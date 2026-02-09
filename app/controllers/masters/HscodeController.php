<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class HscodeController extends Controller
{
    private $table = 'hscode_master_t';
    private $maxFileSize = 5242880; // 5MB
    private $maxImportRows = 5000;

    /**
     * Clean and sanitize input - renamed to avoid parent class conflict
     */
    private function cleanInput($value)
    {
        if ($value === null) return '';
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate numeric value with range
     */
    private function validateNumeric($value, $default = 0.00)
    {
        $value = floatval($value);
        if ($value < 0 || $value > 999999.99) {
            return $default;
        }
        return round($value, 2);
    }

    /**
     * Send JSON response
     */
    private function jsonResponse($success, $message, $extra = [])
    {
        header('Content-Type: application/json');
        echo json_encode(array_merge([
            'success' => $success,
            'message' => $message
        ], $extra));
        exit;
    }

    /**
     * Get current user ID
     */
    private function getUserId()
    {
        return $_SESSION['user_id'] ?? 1;
    }

    /**
     * Index - Display HS Code list
     */
    public function index()
    {
        $db = new Database();
        $result = $db->selectData($this->table, '*', [], 'hscode_number ASC');
        
        $data = [
            'title'  => 'HS Code Master',
            'result' => $result
        ];
        $this->viewWithLayout('masters/hscode_master', $data);
    }

    /**
     * Download Excel Template
     */
    public function downloadTemplate()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('HS Code Data');

            // Headers
            $headers = ['HS Code Number', 'DDI', 'ICA', 'DCI', 'DCL', 'TPI', 'Display (Y/N)'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '1', $header);
                $col++;
            }

            // Header styling
            $headerStyle = [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ];
            $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);
            $sheet->getRowDimension(1)->setRowHeight(25);

            // Column widths
            $widths = ['A' => 22, 'B' => 12, 'C' => 12, 'D' => 12, 'E' => 12, 'F' => 12, 'G' => 15];
            foreach ($widths as $c => $w) {
                $sheet->getColumnDimension($c)->setWidth($w);
            }

            // Sample data
            $samples = [
                ['8471.30.00', 10.00, 0.00, 0.00, 0.00, 0.00, 'Y'],
                ['8517.12.00', 5.00, 2.50, 0.00, 0.00, 0.00, 'Y'],
            ];

            $row = 2;
            foreach ($samples as $sample) {
                $sheet->fromArray($sample, null, 'A' . $row);
                $row++;
            }

            // Data styling
            $sheet->getStyle('A2:G3')->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC']
                    ]
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]);

            // Instructions sheet
            $instSheet = $spreadsheet->createSheet();
            $instSheet->setTitle('Instructions');
            
            $instructions = [
                ['HS CODE IMPORT TEMPLATE'],
                [''],
                ['COLUMNS:'],
                ['HS Code Number - Required (4-20 chars, alphanumeric with dots/hyphens)'],
                ['DDI - Droit de Douane Import (Required)'],
                ['ICA - Default: 0'],
                ['DCI - Default: 0'],
                ['DCL - Default: 0'],
                ['TPI - Default: 0'],
                ['Display - Y or N (Default: Y)'],
                [''],
                ['NOTES:'],
                ['• Do NOT modify header row'],
                ['• Duplicate HS Codes will be skipped'],
                ['• Delete sample data before importing'],
                ['• Max ' . number_format($this->maxImportRows) . ' rows per import'],
            ];

            $row = 1;
            foreach ($instructions as $line) {
                $instSheet->setCellValue('A' . $row, $line[0] ?? '');
                $row++;
            }
            
            $instSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $instSheet->getStyle('A3')->getFont()->setBold(true);
            $instSheet->getStyle('A12')->getFont()->setBold(true);
            $instSheet->getColumnDimension('A')->setWidth(55);

            $spreadsheet->setActiveSheetIndex(0);

            // Output
            $filename = 'HSCode_Template_' . date('Ymd') . '.xlsx';
            
            if (ob_get_level()) ob_end_clean();

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;

        } catch (Exception $e) {
            error_log("HSCode Template Error: " . $e->getMessage());
            die('Error generating template. Please try again.');
        }
    }

    /**
     * Import HS Code Data from Excel
     */
    public function importData()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Invalid request method');
        }

        // Validate file upload
        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            $errorMsg = $this->getUploadErrorMessage($_FILES['import_file']['error'] ?? UPLOAD_ERR_NO_FILE);
            $this->jsonResponse(false, $errorMsg);
        }

        $file = $_FILES['import_file'];

        // Validate file size
        if ($file['size'] > $this->maxFileSize) {
            $this->jsonResponse(false, 'File too large. Maximum size: ' . ($this->maxFileSize / 1024 / 1024) . 'MB');
        }

        // Validate extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'xlsx') {
            $this->jsonResponse(false, 'Only .xlsx files are allowed');
        }

        try {
            $spreadsheet = IOFactory::load($file['tmp_name']);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // Remove header
            array_shift($rows);

            // Check row limit
            if (count($rows) > $this->maxImportRows) {
                $this->jsonResponse(false, 'Too many rows. Maximum: ' . number_format($this->maxImportRows));
            }

            $db = new Database();
            $userId = $this->getUserId();

            $inserted = 0;
            $skipped = 0;
            $errors = [];
            $total = 0;

            // Get existing HS codes for faster duplicate check
            $existingCodes = [];
            $db->query("SELECT hscode_number FROM {$this->table}");
            $existing = $db->resultSet();
            foreach ($existing as $row) {
                $existingCodes[$row['hscode_number']] = true;
            }

            foreach ($rows as $index => $row) {
                $rowNum = $index + 2;

                // Skip empty rows
                $hscode = trim($row[0] ?? '');
                if (empty($hscode)) {
                    continue;
                }

                $total++;

                // Validate HS Code format
                if (strlen($hscode) < 4 || strlen($hscode) > 20) {
                    $errors[] = "Row $rowNum: Invalid HS Code length";
                    $skipped++;
                    continue;
                }

                if (!preg_match('/^[A-Za-z0-9.\-]+$/', $hscode)) {
                    $errors[] = "Row $rowNum: Invalid HS Code format";
                    $skipped++;
                    continue;
                }

                // Check duplicate
                if (isset($existingCodes[$hscode])) {
                    $skipped++;
                    continue;
                }

                // Validate display
                $display = strtoupper(trim($row[6] ?? 'Y'));
                if (!in_array($display, ['Y', 'N'])) {
                    $display = 'Y';
                }

                // Prepare data
                $insertData = [
                    'hscode_number' => $this->cleanInput($hscode),
                    'hscode_ddi'    => $this->validateNumeric($row[1] ?? 0),
                    'hscode_ica'    => $this->validateNumeric($row[2] ?? 0),
                    'hscode_dci'    => $this->validateNumeric($row[3] ?? 0),
                    'hscode_dcl'    => $this->validateNumeric($row[4] ?? 0),
                    'hscode_tpi'    => $this->validateNumeric($row[5] ?? 0),
                    'display'       => $display,
                    'created_by'    => $userId,
                    'updated_by'    => $userId
                ];

                $insertId = $db->insertData($this->table, $insertData);
                
                if ($insertId) {
                    $inserted++;
                    $existingCodes[$hscode] = true;
                } else {
                    $errors[] = "Row $rowNum: Database insert failed";
                    $skipped++;
                }

                // Limit errors shown
                if (count($errors) >= 10) {
                    $errors[] = "... and more errors (showing first 10)";
                    break;
                }
            }

            $this->jsonResponse(true, 'Import completed', [
                'total'    => $total,
                'inserted' => $inserted,
                'skipped'  => $skipped,
                'errors'   => array_slice($errors, 0, 10)
            ]);

        } catch (Exception $e) {
            error_log("HSCode Import Error: " . $e->getMessage());
            $this->jsonResponse(false, 'Error processing file. Please check the format and try again.');
        }
    }

    /**
     * Get upload error message
     */
    private function getUploadErrorMessage($errorCode)
    {
        $errors = [
            UPLOAD_ERR_INI_SIZE   => 'File exceeds server limit',
            UPLOAD_ERR_FORM_SIZE  => 'File exceeds form limit',
            UPLOAD_ERR_PARTIAL    => 'File partially uploaded',
            UPLOAD_ERR_NO_FILE    => 'No file selected',
            UPLOAD_ERR_NO_TMP_DIR => 'Server configuration error',
            UPLOAD_ERR_CANT_WRITE => 'Server write error',
            UPLOAD_ERR_EXTENSION  => 'File type blocked',
        ];
        return $errors[$errorCode] ?? 'Unknown upload error';
    }

    /**
     * CRUD Operations
     */
    public function crudData($action = 'insertion')
    {
        $db = new Database();
        $userId = $this->getUserId();

        // INSERT
        if ($action === 'insertion' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $hscode = trim($_POST['hscode_number'] ?? '');
            $ddi = $_POST['hscode_ddi'] ?? '';

            if (empty($hscode)) {
                $this->jsonResponse(false, 'HS Code Number is required');
            }

            if ($ddi === '' || !is_numeric($ddi)) {
                $this->jsonResponse(false, 'DDI is required');
            }

            // Validate format
            if (strlen($hscode) < 4 || strlen($hscode) > 20 || !preg_match('/^[A-Za-z0-9.\-]+$/', $hscode)) {
                $this->jsonResponse(false, 'Invalid HS Code format (4-20 chars, alphanumeric with dots/hyphens)');
            }

            // Check duplicate
            $db->query("SELECT id FROM {$this->table} WHERE hscode_number = :code");
            $db->bind(":code", $hscode);
            if ($db->single()) {
                $this->jsonResponse(false, 'HS Code already exists');
            }

            $display = $_POST['display'] ?? 'Y';
            $data = [
                'hscode_number' => $this->cleanInput($hscode),
                'hscode_ddi'    => $this->validateNumeric($ddi),
                'hscode_ica'    => $this->validateNumeric($_POST['hscode_ica'] ?? 0),
                'hscode_dci'    => $this->validateNumeric($_POST['hscode_dci'] ?? 0),
                'hscode_dcl'    => $this->validateNumeric($_POST['hscode_dcl'] ?? 0),
                'hscode_tpi'    => $this->validateNumeric($_POST['hscode_tpi'] ?? 0),
                'display'       => in_array($display, ['Y', 'N']) ? $display : 'Y',
                'created_by'    => $userId,
                'updated_by'    => $userId
            ];

            $insertId = $db->insertData($this->table, $data);
            
            if ($insertId) {
                $this->jsonResponse(true, 'HS Code added successfully', ['id' => $insertId]);
            } else {
                $this->jsonResponse(false, 'Failed to add HS Code');
            }
        }

        // UPDATE
        if ($action === 'updation' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_GET['id'] ?? 0);
            
            if ($id <= 0) {
                $this->jsonResponse(false, 'Invalid ID');
            }

            $hscode = trim($_POST['hscode_number'] ?? '');
            
            if (empty($hscode)) {
                $this->jsonResponse(false, 'HS Code Number is required');
            }

            // Check if new code conflicts with another record
            $db->query("SELECT id FROM {$this->table} WHERE hscode_number = :code AND id != :id");
            $db->bind(":code", $hscode);
            $db->bind(":id", $id);
            if ($db->single()) {
                $this->jsonResponse(false, 'This HS Code already exists in another record');
            }

            $display = $_POST['display'] ?? 'Y';
            $data = [
                'hscode_number' => $this->cleanInput($hscode),
                'hscode_ddi'    => $this->validateNumeric($_POST['hscode_ddi'] ?? 0),
                'hscode_ica'    => $this->validateNumeric($_POST['hscode_ica'] ?? 0),
                'hscode_dci'    => $this->validateNumeric($_POST['hscode_dci'] ?? 0),
                'hscode_dcl'    => $this->validateNumeric($_POST['hscode_dcl'] ?? 0),
                'hscode_tpi'    => $this->validateNumeric($_POST['hscode_tpi'] ?? 0),
                'display'       => in_array($display, ['Y', 'N']) ? $display : 'Y',
                'updated_by'    => $userId
            ];

            $update = $db->updateData($this->table, $data, ['id' => $id]);
            
            if ($update) {
                $this->jsonResponse(true, 'HS Code updated successfully');
            } else {
                $this->jsonResponse(false, 'Failed to update HS Code');
            }
        }

        // DELETE
        if ($action === 'deletion') {
            $id = (int)($_GET['id'] ?? 0);
            
            if ($id <= 0) {
                $this->jsonResponse(false, 'Invalid ID');
            }

            $delete = $db->deleteData($this->table, ['id' => $id]);
            
            if ($delete) {
                $this->jsonResponse(true, 'HS Code deleted successfully');
            } else {
                $this->jsonResponse(false, 'Failed to delete HS Code');
            }
        }
    }

    /**
     * Get HS Code by ID
     */
    public function getHscodeById()
    {
        header('Content-Type: application/json');
        
        $id = (int)($_GET['id'] ?? 0);

        if ($id <= 0) {
            $this->jsonResponse(false, 'Invalid ID');
        }

        $db = new Database();
        $data = $db->selectData($this->table, '*', ['id' => $id]);

        if (!empty($data)) {
            $this->jsonResponse(true, 'Data found', ['data' => $data[0]]);
        } else {
            $this->jsonResponse(false, 'Record not found');
        }
    }
}
?>