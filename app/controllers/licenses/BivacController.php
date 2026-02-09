<?php
// ✅ CRITICAL: Include Composer autoloader for PhpSpreadsheet
$vendorPath = __DIR__ . '/../../../vendor/autoload.php';
if (!file_exists($vendorPath)) {
    die('Error: Composer autoload not found. Please run: composer require phpoffice/phpspreadsheet');
}
require_once $vendorPath;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class BivacController extends Controller
{
    /*
     * INDEX PAGE
     */
    public function index()
    {
        $db = new Database();

        $this->ensurePartialTableExists($db);

        $sql = "
            SELECT 
                l.id,
                l.license_number,
                l.ref_cod,
                c.short_name as client_name,
                cur.currency_short_name as currency_name,
                tog.goods_type as type_of_goods_name,
                l.weight,
                l.fob_declared,
                l.insurance,
                l.freight,
                l.other_costs
            FROM licenses_t l
            LEFT JOIN clients_t c ON l.client_id = c.id
            LEFT JOIN currency_master_t cur ON l.currency_id = cur.id
            LEFT JOIN type_of_goods_master_t tog ON l.type_of_goods_id = tog.id
            WHERE l.display = 'Y' AND l.kind_id IN (1, 2)
            ORDER BY l.license_number ASC
        ";
        $licenses = $db->customQuery($sql);
        
        if ($licenses === false) {
            $licenses = [];
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $data = [
            'title' => 'PARTIELLE Management',
            'licenses' => $licenses,
            'csrf_token' => $_SESSION['csrf_token']
        ];

        $this->viewWithLayout('licenses/bivac', $data);
    }

    /*
     * EXPORT LICENSES TO EXCEL (Server-Side)
     */
    public function exportLicenses()
    {
        $db = new Database();
        
        // Get filter parameter
        $clientFilter = intval($_GET['client_filter'] ?? 0);
        
        // Build query
        $whereClause = " WHERE l.display = 'Y' AND l.kind_id IN (1, 2) ";
        $params = [];
        
        if ($clientFilter > 0) {
            $whereClause .= " AND l.client_id = ? ";
            $params[] = $clientFilter;
        }
        
        $query = "
            SELECT 
                l.id,
                l.license_number,
                l.ref_cod,
                c.short_name as client_name,
                cur.currency_short_name as currency_name,
                tog.goods_type as type_of_goods_name,
                l.weight,
                l.fob_declared,
                l.insurance,
                l.freight,
                l.other_costs,
                (SELECT COUNT(*) FROM partial_t p WHERE p.license_id = l.id AND p.display = 'Y') as partielle_count,
                (SELECT COALESCE(SUM(partial_weight), 0) FROM partial_t p WHERE p.license_id = l.id AND p.display = 'Y') as total_partial_weight,
                (SELECT COALESCE(SUM(partial_fob), 0) FROM partial_t p WHERE p.license_id = l.id AND p.display = 'Y') as total_partial_fob,
                (
                    SELECT COALESCE(SUM(i.weight), 0) 
                    FROM imports_t i 
                    INNER JOIN partial_t p ON i.inspection_reports = p.partial_name 
                    WHERE p.license_id = l.id AND p.display = 'Y' AND i.display = 'Y'
                ) as total_used_weight,
                (
                    SELECT COALESCE(SUM(i.fob), 0) 
                    FROM imports_t i 
                    INNER JOIN partial_t p ON i.inspection_reports = p.partial_name 
                    WHERE p.license_id = l.id AND p.display = 'Y' AND i.display = 'Y'
                ) as total_used_fob,
                (
                    SELECT COALESCE(SUM(i.fret), 0) 
                    FROM imports_t i 
                    INNER JOIN partial_t p ON i.inspection_reports = p.partial_name 
                    WHERE p.license_id = l.id AND p.display = 'Y' AND i.display = 'Y'
                ) as total_used_freight,
                (
                    SELECT COALESCE(SUM(i.insurance_amount), 0) 
                    FROM imports_t i 
                    INNER JOIN partial_t p ON i.inspection_reports = p.partial_name 
                    WHERE p.license_id = l.id AND p.display = 'Y' AND i.display = 'Y'
                ) as total_used_insurance,
                (
                    SELECT COALESCE(SUM(i.other_charges), 0) 
                    FROM imports_t i 
                    INNER JOIN partial_t p ON i.inspection_reports = p.partial_name 
                    WHERE p.license_id = l.id AND p.display = 'Y' AND i.display = 'Y'
                ) as total_used_other
            FROM licenses_t l
            LEFT JOIN clients_t c ON l.client_id = c.id
            LEFT JOIN currency_master_t cur ON l.currency_id = cur.id
            LEFT JOIN type_of_goods_master_t tog ON l.type_of_goods_id = tog.id
            " . $whereClause . "
            ORDER BY l.license_number ASC
        ";
        
        $data = $db->customQuery($query, $params);
        
        if (empty($data)) {
            echo "No data to export";
            exit;
        }

        // Create new Spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Licenses');

        // Set headers - NEW ORDER WITH ADDITIONAL COLUMNS
        $headers = [
            'A1' => 'ID',
            'B1' => 'License Number',
            'C1' => 'CRF',
            'D1' => 'PARTIELLE Count',
            'E1' => 'Client',
            'F1' => 'Currency',
            'G1' => 'Type of Goods',
            'H1' => 'License FOB',
            'I1' => 'License Freight',
            'J1' => 'License Insurance',
            'K1' => 'License Other Costs',
            'L1' => 'License Weight (KG)',
            'M1' => 'License Wt - Used Wt',
            'N1' => 'License FOB - Used FOB',
            'O1' => 'License Frt - Used Frt',
            'P1' => 'License Ins - Used Ins',
            'Q1' => 'License Other - Used Other'
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Style headers
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 10
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2c3e50']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ];
        $sheet->getStyle('A1:Q1')->applyFromArray($headerStyle);

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(13);
        $sheet->getColumnDimension('D')->setWidth(13);
        $sheet->getColumnDimension('E')->setWidth(23);
        $sheet->getColumnDimension('F')->setWidth(11);
        $sheet->getColumnDimension('G')->setWidth(23);
        $sheet->getColumnDimension('H')->setWidth(14);
        $sheet->getColumnDimension('I')->setWidth(14);
        $sheet->getColumnDimension('J')->setWidth(15);
        $sheet->getColumnDimension('K')->setWidth(16);
        $sheet->getColumnDimension('L')->setWidth(16);
        $sheet->getColumnDimension('M')->setWidth(16);
        $sheet->getColumnDimension('N')->setWidth(17);
        $sheet->getColumnDimension('O')->setWidth(17);
        $sheet->getColumnDimension('P')->setWidth(17);
        $sheet->getColumnDimension('Q')->setWidth(19);

        // Fill data - NEW ORDER WITH ADDITIONAL CALCULATIONS
        $row = 2;
        foreach ($data as $record) {
            $licWeight = floatval($record['weight'] ?? 0);
            $licFob = floatval($record['fob_declared'] ?? 0);
            $licFreight = floatval($record['freight'] ?? 0);
            $licInsurance = floatval($record['insurance'] ?? 0);
            $licOther = floatval($record['other_costs'] ?? 0);
            
            $totalUsedWeight = floatval($record['total_used_weight'] ?? 0);
            $totalUsedFob = floatval($record['total_used_fob'] ?? 0);
            $totalUsedFreight = floatval($record['total_used_freight'] ?? 0);
            $totalUsedInsurance = floatval($record['total_used_insurance'] ?? 0);
            $totalUsedOther = floatval($record['total_used_other'] ?? 0);
            
            $sheet->setCellValue('A' . $row, $record['id']);
            $sheet->setCellValue('B' . $row, $record['license_number'] ?? '');
            $sheet->setCellValue('C' . $row, $record['ref_cod'] ?? '');
            $sheet->setCellValue('D' . $row, $record['partielle_count'] ?? 0);
            $sheet->setCellValue('E' . $row, $record['client_name'] ?? 'N/A');
            $sheet->setCellValue('F' . $row, $record['currency_name'] ?? 'N/A');
            $sheet->setCellValue('G' . $row, $record['type_of_goods_name'] ?? 'N/A');
            $sheet->setCellValue('H' . $row, number_format($licFob, 2, '.', ''));
            $sheet->setCellValue('I' . $row, number_format($licFreight, 2, '.', ''));
            $sheet->setCellValue('J' . $row, number_format($licInsurance, 2, '.', ''));
            $sheet->setCellValue('K' . $row, number_format($licOther, 2, '.', ''));
            $sheet->setCellValue('L' . $row, number_format($licWeight, 2, '.', ''));
            $sheet->setCellValue('M' . $row, number_format($licWeight - $totalUsedWeight, 2, '.', ''));
            $sheet->setCellValue('N' . $row, number_format($licFob - $totalUsedFob, 2, '.', ''));
            $sheet->setCellValue('O' . $row, number_format($licFreight - $totalUsedFreight, 2, '.', ''));
            $sheet->setCellValue('P' . $row, number_format($licInsurance - $totalUsedInsurance, 2, '.', ''));
            $sheet->setCellValue('Q' . $row, number_format($licOther - $totalUsedOther, 2, '.', ''));
            $row++;
        }

        // Style data rows
        $dataStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC']
                ]
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ];
        $sheet->getStyle('A2:Q' . ($row - 1))->applyFromArray($dataStyle);

        // Center align numeric columns
        $sheet->getStyle('A2:A' . ($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D2:D' . ($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('H2:Q' . ($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Freeze header row
        $sheet->freezePane('A2');

        // Generate filename
        $filename = 'Licenses_Export_' . date('Y-m-d') . '.xlsx';

        // Output file
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /*
     * EXPORT PARTIELLE TO EXCEL (Server-Side)
     */
    public function exportPartielle()
    {
        $db = new Database();
        
        $licenseId = intval($_GET['license_id'] ?? 0);
        
        if ($licenseId <= 0) {
            echo "Invalid License ID";
            exit;
        }

        $query = "
            SELECT 
                p.*,
                l.license_number,
                l.ref_cod,
                c.company_name as client_name,
                l.weight as license_weight,
                l.fob_declared as license_fob
            FROM partial_t p
            LEFT JOIN licenses_t l ON p.license_id = l.id
            LEFT JOIN clients_t c ON l.client_id = c.id
            WHERE p.license_id = ? AND p.display = 'Y'
            ORDER BY p.id DESC
        ";
        
        $data = $db->customQuery($query, [$licenseId]);
        
        if (empty($data)) {
            echo "No PARTIELLE data to export";
            exit;
        }

        // Calculate used values for each PARTIELLE
        foreach ($data as &$p) {
            $usedQuery = "
                SELECT 
                    COALESCE(SUM(weight), 0) as used_weight,
                    COALESCE(SUM(fob), 0) as used_fob
                FROM imports_t 
                WHERE inspection_reports = ? AND display = 'Y'
            ";
            $usedResult = $db->customQuery($usedQuery, [$p['partial_name']]);
            $p['used_weight'] = (!empty($usedResult)) ? $usedResult[0]['used_weight'] : 0;
            $p['used_fob'] = (!empty($usedResult)) ? $usedResult[0]['used_fob'] : 0;

            $importCountQuery = "SELECT COUNT(*) as cnt FROM imports_t WHERE inspection_reports = ? AND display = 'Y'";
            $importCountResult = $db->customQuery($importCountQuery, [$p['partial_name']]);
            $p['import_count'] = (!empty($importCountResult)) ? $importCountResult[0]['cnt'] : 0;
            
            $p['partial_weight_usedweight'] = round(($p['partial_weight'] ?? 0) - $p['used_weight'], 2);
            $p['partial_fob_usedfob'] = round(($p['partial_fob'] ?? 0) - $p['used_fob'], 2);
        }

        // Create new Spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('PARTIELLE');

        // Set headers
        $headers = [
            'A1' => 'ID',
            'B1' => 'PARTIELLE Name',
            'C1' => 'License Number',
            'D1' => 'CRF',
            'E1' => 'Client',
            'F1' => 'License Weight (KG)',
            'G1' => 'License FOB',
            'H1' => 'License Insurance',
            'I1' => 'License Freight',
            'J1' => 'License Other',
            'K1' => 'AV Weight (KG)',
            'L1' => 'AV FOB',
            'M1' => 'AV Insurance',
            'N1' => 'AV Freight',
            'O1' => 'AV Other',
            'P1' => 'Used Weight (KG)',
            'Q1' => 'Used FOB',
            'R1' => 'Remaining Weight (KG)',
            'S1' => 'Remaining FOB',
            'T1' => 'Import Files Count'
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Style headers
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 9
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2c3e50']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ];
        $sheet->getStyle('A1:T1')->applyFromArray($headerStyle);

        // Set column widths
        $columns = ['A' => 7, 'B' => 18, 'C' => 16, 'D' => 13, 'E' => 23, 'F' => 15, 'G' => 13, 'H' => 13, 'I' => 13, 'J' => 13, 'K' => 15, 'L' => 13, 'M' => 13, 'N' => 13, 'O' => 13, 'P' => 15, 'Q' => 13, 'R' => 16, 'S' => 13, 'T' => 15];
        foreach ($columns as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // Fill data
        $row = 2;
        foreach ($data as $record) {
            $sheet->setCellValue('A' . $row, $record['id']);
            $sheet->setCellValue('B' . $row, $record['partial_name'] ?? '');
            $sheet->setCellValue('C' . $row, $record['license_number'] ?? '');
            $sheet->setCellValue('D' . $row, $record['ref_cod'] ?? '');
            $sheet->setCellValue('E' . $row, $record['client_name'] ?? '');
            $sheet->setCellValue('F' . $row, number_format($record['license_weight'] ?? 0, 2, '.', ''));
            $sheet->setCellValue('G' . $row, number_format($record['license_fob'] ?? 0, 2, '.', ''));
            $sheet->setCellValue('H' . $row, number_format($record['license_insurance'] ?? 0, 2, '.', ''));
            $sheet->setCellValue('I' . $row, number_format($record['license_freight'] ?? 0, 2, '.', ''));
            $sheet->setCellValue('J' . $row, number_format($record['license_other_costs'] ?? 0, 2, '.', ''));
            $sheet->setCellValue('K' . $row, number_format($record['partial_weight'] ?? 0, 2, '.', ''));
            $sheet->setCellValue('L' . $row, number_format($record['partial_fob'] ?? 0, 2, '.', ''));
            $sheet->setCellValue('M' . $row, number_format($record['partial_insurance'] ?? 0, 2, '.', ''));
            $sheet->setCellValue('N' . $row, number_format($record['partial_freight'] ?? 0, 2, '.', ''));
            $sheet->setCellValue('O' . $row, number_format($record['partial_other_costs'] ?? 0, 2, '.', ''));
            $sheet->setCellValue('P' . $row, number_format($record['used_weight'] ?? 0, 2, '.', ''));
            $sheet->setCellValue('Q' . $row, number_format($record['used_fob'] ?? 0, 2, '.', ''));
            $sheet->setCellValue('R' . $row, number_format($record['partial_weight_usedweight'] ?? 0, 2, '.', ''));
            $sheet->setCellValue('S' . $row, number_format($record['partial_fob_usedfob'] ?? 0, 2, '.', ''));
            $sheet->setCellValue('T' . $row, $record['import_count'] ?? 0);
            $row++;
        }

        // Style data rows
        $dataStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC']
                ]
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ];
        $sheet->getStyle('A2:T' . ($row - 1))->applyFromArray($dataStyle);

        // Right align numeric columns
        $sheet->getStyle('A2:A' . ($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F2:T' . ($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Freeze header row
        $sheet->freezePane('A2');

        // Generate filename
        $licenseNumber = $data[0]['license_number'] ?? 'Unknown';
        $filename = 'PARTIELLE_' . $licenseNumber . '_Export_' . date('Y-m-d') . '.xlsx';

        // Output file
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /*
     * EXPORT FILES TO EXCEL (Server-Side)
     */
    public function exportFiles()
    {
        $db = new Database();
        
        $partialName = trim($_GET['partial_name'] ?? '');
        
        if (empty($partialName)) {
            echo "Invalid PARTIELLE name";
            exit;
        }

        $query = "
            SELECT 
                id,
                mca_ref,
                inspection_reports,
                declaration_reference,
                dgda_in_date,
                liquidation_reference,
                liquidation_date,
                quittance_reference,
                quittance_date,
                weight,
                fob
            FROM imports_t
            WHERE inspection_reports = ? 
            AND display = 'Y'
            ORDER BY id ASC
        ";
        
        $data = $db->customQuery($query, [$partialName]);
        
        if (empty($data)) {
            echo "No files data to export";
            exit;
        }

        // Create new Spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Files');

        // Set headers
        $headers = [
            'A1' => '#',
            'B1' => 'PARTIELLE Name',
            'C1' => 'MCA Reference',
            'D1' => 'Inspection Reports',
            'E1' => 'Declaration Reference',
            'F1' => 'DGDA In Date',
            'G1' => 'Liquidation Reference',
            'H1' => 'Liquidation Date',
            'I1' => 'Quittance Reference',
            'J1' => 'Quittance Date',
            'K1' => 'Weight (KG)',
            'L1' => 'FOB'
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Style headers
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 10
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2c3e50']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ];
        $sheet->getStyle('A1:L1')->applyFromArray($headerStyle);

        // Set column widths
        $columns = ['A' => 7, 'B' => 18, 'C' => 18, 'D' => 18, 'E' => 18, 'F' => 14, 'G' => 18, 'H' => 14, 'I' => 18, 'J' => 14, 'K' => 14, 'L' => 14];
        foreach ($columns as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // Fill data
        $row = 2;
        $index = 1;
        foreach ($data as $record) {
            $sheet->setCellValue('A' . $row, $index);
            $sheet->setCellValue('B' . $row, $partialName);
            $sheet->setCellValue('C' . $row, $record['mca_ref'] ?? '');
            $sheet->setCellValue('D' . $row, $record['inspection_reports'] ?? '');
            $sheet->setCellValue('E' . $row, $record['declaration_reference'] ?? '');
            $sheet->setCellValue('F' . $row, !empty($record['dgda_in_date']) ? date('d-m-Y', strtotime($record['dgda_in_date'])) : '-');
            $sheet->setCellValue('G' . $row, $record['liquidation_reference'] ?? '');
            $sheet->setCellValue('H' . $row, !empty($record['liquidation_date']) ? date('d-m-Y', strtotime($record['liquidation_date'])) : '-');
            $sheet->setCellValue('I' . $row, $record['quittance_reference'] ?? '');
            $sheet->setCellValue('J' . $row, !empty($record['quittance_date']) ? date('d-m-Y', strtotime($record['quittance_date'])) : '-');
            $sheet->setCellValue('K' . $row, number_format($record['weight'] ?? 0, 2, '.', ''));
            $sheet->setCellValue('L' . $row, number_format($record['fob'] ?? 0, 2, '.', ''));
            $row++;
            $index++;
        }

        // Style data rows
        $dataStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC']
                ]
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ];
        $sheet->getStyle('A2:L' . ($row - 1))->applyFromArray($dataStyle);

        // Center/Right align columns
        $sheet->getStyle('A2:A' . ($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('K2:L' . ($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Freeze header row
        $sheet->freezePane('A2');

        // Generate filename
        $filename = 'Files_' . $partialName . '_Export_' . date('Y-m-d') . '.xlsx';

        // Output file
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /*
     * CRUD - UPDATE, DELETE, LISTING
     */
    public function crudData($action = 'listing')
    {
        $db = new Database();
        $table = 'partial_t';

        function s($v) {
            return htmlspecialchars(trim($v), ENT_QUOTES, 'UTF-8');
        }

        $this->ensurePartialTableExists($db);

        /*
         * GET CLIENTS LIST FOR FILTER
         */
        if ($action === 'getClients' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            header('Content-Type: application/json');

            try {
                $query = "
                    SELECT DISTINCT 
                        c.id,
                        c.short_name,
                        c.company_name
                    FROM clients_t c
                    INNER JOIN licenses_t l ON c.id = l.client_id
                    WHERE c.display = 'Y' 
                    AND l.display = 'Y' 
                    AND l.kind_id IN (1, 2)
                    ORDER BY c.short_name ASC
                ";
                $clients = $db->customQuery($query);
                
                if ($clients === false || !is_array($clients)) {
                    $clients = [];
                }

                echo json_encode([
                    'success' => true,
                    'data' => $clients
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to load clients: ' . $e->getMessage()
                ]);
            }
            exit;
        }

        /*
         * LICENSES LISTING FOR DATATABLE - UPDATED WITH NEW COLUMNS
         */
        if ($action === 'licensesListing' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            header('Content-Type: application/json');

            try {
                $draw = intval($_GET['draw'] ?? 1);
                $start = intval($_GET['start'] ?? 0);
                $length = intval($_GET['length'] ?? 15);
                $searchValue = trim($_GET['search']['value'] ?? '');
                $orderColumnIndex = intval($_GET['order'][0]['column'] ?? 0);
                $orderDir = strtolower($_GET['order'][0]['dir'] ?? 'desc');
                
                $orderDir = in_array($orderDir, ['asc', 'desc']) ? $orderDir : 'desc';

                $columns = [
                    'l.id', 
                    'l.license_number', 
                    'l.ref_cod', 
                    'c.short_name', 
                    'cur.currency_short_name', 
                    'tog.goods_type', 
                    'l.fob_declared',
                    'l.freight',
                    'l.insurance',
                    'l.other_costs',
                    'l.weight'
                ];
                $orderColumn = $columns[$orderColumnIndex] ?? 'l.id';

                $baseQuery = "
                    FROM licenses_t l
                    LEFT JOIN clients_t c ON l.client_id = c.id
                    LEFT JOIN currency_master_t cur ON l.currency_id = cur.id
                    LEFT JOIN type_of_goods_master_t tog ON l.type_of_goods_id = tog.id
                ";

                $whereClause = " WHERE l.display = 'Y' AND l.kind_id IN (1, 2) ";
                $params = [];

                // Client filter
                if (!empty($_GET['client_filter']) && $_GET['client_filter'] != '0') {
                    $clientFilter = intval($_GET['client_filter']);
                    $whereClause .= " AND l.client_id = ? ";
                    $params[] = $clientFilter;
                }

                if (!empty($searchValue)) {
                    $whereClause .= " AND (
                        l.license_number LIKE ? OR
                        l.ref_cod LIKE ? OR
                        c.short_name LIKE ? OR
                        cur.currency_short_name LIKE ? OR
                        tog.goods_type LIKE ?
                    )";
                    $searchParam = "%{$searchValue}%";
                    $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam]);
                }

                $totalRecordsQuery = "SELECT COUNT(*) as total " . $baseQuery . $whereClause;
                $totalRecordsResult = $db->customQuery($totalRecordsQuery, $params);
                $totalRecords = (!empty($totalRecordsResult) && isset($totalRecordsResult[0]['total'])) ? $totalRecordsResult[0]['total'] : 0;

                $dataQuery = "
                    SELECT 
                        l.id,
                        l.license_number,
                        l.ref_cod,
                        c.short_name as client_name,
                        cur.currency_short_name as currency_name,
                        tog.goods_type as type_of_goods_name,
                        l.weight,
                        l.fob_declared,
                        l.insurance,
                        l.freight,
                        l.other_costs,
                        (SELECT COUNT(*) FROM partial_t p WHERE p.license_id = l.id AND p.display = 'Y') as partielle_count,
                        (SELECT COALESCE(SUM(partial_weight), 0) FROM partial_t p WHERE p.license_id = l.id AND p.display = 'Y') as total_partial_weight,
                        (SELECT COALESCE(SUM(partial_fob), 0) FROM partial_t p WHERE p.license_id = l.id AND p.display = 'Y') as total_partial_fob,
                        (
                            SELECT COALESCE(SUM(i.weight), 0) 
                            FROM imports_t i 
                            INNER JOIN partial_t p ON i.inspection_reports = p.partial_name 
                            WHERE p.license_id = l.id AND p.display = 'Y' AND i.display = 'Y'
                        ) as total_used_weight,
                        (
                            SELECT COALESCE(SUM(i.fob), 0) 
                            FROM imports_t i 
                            INNER JOIN partial_t p ON i.inspection_reports = p.partial_name 
                            WHERE p.license_id = l.id AND p.display = 'Y' AND i.display = 'Y'
                        ) as total_used_fob,
                        (
                            SELECT COALESCE(SUM(i.fret), 0) 
                            FROM imports_t i 
                            INNER JOIN partial_t p ON i.inspection_reports = p.partial_name 
                            WHERE p.license_id = l.id AND p.display = 'Y' AND i.display = 'Y'
                        ) as total_used_freight,
                        (
                            SELECT COALESCE(SUM(i.insurance_amount), 0) 
                            FROM imports_t i 
                            INNER JOIN partial_t p ON i.inspection_reports = p.partial_name 
                            WHERE p.license_id = l.id AND p.display = 'Y' AND i.display = 'Y'
                        ) as total_used_insurance,
                        (
                            SELECT COALESCE(SUM(i.other_charges), 0) 
                            FROM imports_t i 
                            INNER JOIN partial_t p ON i.inspection_reports = p.partial_name 
                            WHERE p.license_id = l.id AND p.display = 'Y' AND i.display = 'Y'
                        ) as total_used_other
                    " . $baseQuery . $whereClause . "
                    ORDER BY $orderColumn $orderDir
                    LIMIT ?, ?
                ";
                
                $params[] = $start;
                $params[] = $length;

                $records = $db->customQuery($dataQuery, $params);
                
                if ($records === false || !is_array($records)) {
                    $records = [];
                }

                $data = [];
                foreach ($records as $row) {
                    $data[] = [
                        'id' => $row['id'],
                        'license_number' => $row['license_number'] ?? '',
                        'ref_cod' => $row['ref_cod'] ?? '',
                        'client_name' => $row['client_name'] ?? 'N/A',
                        'currency_name' => $row['currency_name'] ?? 'N/A',
                        'type_of_goods_name' => $row['type_of_goods_name'] ?? 'N/A',
                        'weight' => $row['weight'] ?? 0,
                        'fob_declared' => $row['fob_declared'] ?? 0,
                        'insurance' => $row['insurance'] ?? 0,
                        'freight' => $row['freight'] ?? 0,
                        'other_costs' => $row['other_costs'] ?? 0,
                        'partielle_count' => $row['partielle_count'] ?? 0,
                        'total_partial_weight' => $row['total_partial_weight'] ?? 0,
                        'total_partial_fob' => $row['total_partial_fob'] ?? 0,
                        'total_used_weight' => $row['total_used_weight'] ?? 0,
                        'total_used_fob' => $row['total_used_fob'] ?? 0,
                        'total_used_freight' => $row['total_used_freight'] ?? 0,
                        'total_used_insurance' => $row['total_used_insurance'] ?? 0,
                        'total_used_other' => $row['total_used_other'] ?? 0
                    ];
                }

                echo json_encode([
                    'draw' => $draw,
                    'recordsTotal' => $totalRecords,
                    'recordsFiltered' => $totalRecords,
                    'data' => $data
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'draw' => intval($_GET['draw'] ?? 1),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'error' => $e->getMessage()
                ]);
            }
            exit;
        }

        /*
         * GET PARTIELLE BY LICENSE ID
         */
        if ($action === 'getPartielleByLicense' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            header('Content-Type: application/json');

            $licenseId = intval($_GET['license_id'] ?? 0);
            if ($licenseId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid License ID']);
                exit;
            }

            try {
                $query = "
                    SELECT 
                        p.*,
                        l.license_number,
                        l.ref_cod,
                        c.company_name as client_name
                    FROM partial_t p
                    LEFT JOIN licenses_t l ON p.license_id = l.id
                    LEFT JOIN clients_t c ON l.client_id = c.id
                    WHERE p.license_id = ? AND p.display = 'Y'
                    ORDER BY p.id DESC
                ";
                $partielle = $db->customQuery($query, [$licenseId]);
                
                if ($partielle === false || !is_array($partielle)) {
                    $partielle = [];
                }

                // Calculate used values and import count for each PARTIELLE
                foreach ($partielle as &$p) {
                    // Calculate used weight and FOB from imports_t
                    $usedQuery = "
                        SELECT 
                            COALESCE(SUM(weight), 0) as used_weight,
                            COALESCE(SUM(fob), 0) as used_fob
                        FROM imports_t 
                        WHERE inspection_reports = ? AND display = 'Y'
                    ";
                    $usedResult = $db->customQuery($usedQuery, [$p['partial_name']]);
                    $p['used_weight'] = (!empty($usedResult)) ? $usedResult[0]['used_weight'] : 0;
                    $p['used_fob'] = (!empty($usedResult)) ? $usedResult[0]['used_fob'] : 0;

                    // Count imports
                    $importCountQuery = "SELECT COUNT(*) as cnt FROM imports_t WHERE inspection_reports = ? AND display = 'Y'";
                    $importCountResult = $db->customQuery($importCountQuery, [$p['partial_name']]);
                    $p['import_count'] = (!empty($importCountResult) && isset($importCountResult[0]['cnt'])) ? $importCountResult[0]['cnt'] : 0;
                    
                    // Calculate Partial - Used
                    $p['partial_weight_usedweight'] = round(($p['partial_weight'] ?? 0) - $p['used_weight'], 2);
                    $p['partial_fob_usedfob'] = round(($p['partial_fob'] ?? 0) - $p['used_fob'], 2);
                }

                echo json_encode([
                    'success' => true,
                    'data' => $partielle,
                    'count' => count($partielle)
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to load PARTIELLE: ' . $e->getMessage()
                ]);
            }
            exit;
        }

        /*
         * GET SINGLE PARTIELLE
         */
        if ($action === 'getPartielle' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            header('Content-Type: application/json');

            $id = intval($_GET['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid ID']);
                exit;
            }

            $query = "
                SELECT 
                    p.*,
                    l.license_number,
                    l.ref_cod,
                    c.company_name as client_name
                FROM partial_t p
                LEFT JOIN licenses_t l ON p.license_id = l.id
                LEFT JOIN clients_t c ON l.client_id = c.id
                WHERE p.id = ? AND p.display = 'Y'
            ";
            $result = $db->customQuery($query, [$id]);

            if (!empty($result) && is_array($result)) {
                $data = $result[0];
                
                // Calculate used values from imports
                $usedQuery = "
                    SELECT 
                        COALESCE(SUM(weight), 0) as used_weight,
                        COALESCE(SUM(fob), 0) as used_fob
                    FROM imports_t 
                    WHERE inspection_reports = ? AND display = 'Y'
                ";
                $usedResult = $db->customQuery($usedQuery, [$data['partial_name']]);
                $data['used_weight'] = (!empty($usedResult)) ? $usedResult[0]['used_weight'] : 0;
                $data['used_fob'] = (!empty($usedResult)) ? $usedResult[0]['used_fob'] : 0;

                // Recalculate Partial - Used
                $data['partial_weight_usedweight'] = round($data['partial_weight'] - $data['used_weight'], 2);
                $data['partial_fob_usedfob'] = round($data['partial_fob'] - $data['used_fob'], 2);

                echo json_encode(['success' => true, 'data' => $data]);
            } else {
                echo json_encode(['success' => false, 'message' => 'PARTIELLE not found']);
            }
            exit;
        }

        /*
         * GET IMPORT FILES FOR PARTIELLE
         */
        if ($action === 'getImportFiles' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            header('Content-Type: application/json');

            $partialName = trim($_GET['partial_name'] ?? '');
            
            if (empty($partialName)) {
                echo json_encode(['success' => false, 'message' => 'Invalid PARTIELLE name']);
                exit;
            }

            try {
                $query = "
                    SELECT 
                        id,
                        mca_ref,
                        inspection_reports,
                        declaration_reference,
                        dgda_in_date,
                        liquidation_reference,
                        liquidation_date,
                        quittance_reference,
                        quittance_date,
                        weight,
                        fob
                    FROM imports_t
                    WHERE inspection_reports = ? 
                    AND display = 'Y'
                    ORDER BY id ASC
                ";
                
                $files = $db->customQuery($query, [$partialName]);
                
                if ($files === false || !is_array($files)) {
                    $files = [];
                }

                echo json_encode([
                    'success' => true,
                    'data' => $files,
                    'count' => count($files)
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to load files: ' . $e->getMessage()
                ]);
            }
            exit;
        }

        /*
         * UPDATE - SIMPLIFIED VALIDATION (ONLY CHECK LICENSE LIMITS)
         */
        if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');

            if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                echo json_encode(['success' => false, 'message' => 'Invalid security token']);
                exit;
            }

            $id = (int)($_POST['partielle_id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid ID']);
                exit;
            }

            // Get existing PARTIELLE record
            $oldRow = $db->customQuery("SELECT * FROM partial_t WHERE id = ? AND display = 'Y'", [$id]);
            if (empty($oldRow) || !is_array($oldRow)) {
                echo json_encode(['success' => false, 'message' => 'PARTIELLE not found']);
                exit;
            }
            $existing = $oldRow[0];

            // Get input values
            $partial_weight = round((float)($_POST['partial_weight'] ?? 0), 2);
            $partial_fob = round((float)($_POST['partial_fob'] ?? 0), 2);
            $partial_insurance = round((float)($_POST['partial_insurance'] ?? 0), 2);
            $partial_freight = round((float)($_POST['partial_freight'] ?? 0), 2);
            $partial_other_costs = round((float)($_POST['partial_other_costs'] ?? 0), 2);

            // Get fresh license data
            $licenseQuery = "SELECT weight, fob_declared, insurance, freight, other_costs FROM licenses_t WHERE id = ? AND display = 'Y'";
            $licenseData = $db->customQuery($licenseQuery, [$existing['license_id']]);
            if (empty($licenseData)) {
                echo json_encode(['success' => false, 'message' => 'License not found']);
                exit;
            }
            
            $license_weight = round((float)$licenseData[0]['weight'], 2);
            $license_fob = round((float)$licenseData[0]['fob_declared'], 2);
            $license_insurance = round((float)$licenseData[0]['insurance'], 2);
            $license_freight = round((float)$licenseData[0]['freight'], 2);
            $license_other_costs = round((float)$licenseData[0]['other_costs'], 2);

            // ✅ ONLY VALIDATION: Check total allocation doesn't exceed license capacity
            // Get sum of OTHER PARTIELLE for this license (excluding current one)
            $capacityQuery = "
                SELECT 
                    COALESCE(SUM(partial_weight), 0) as total_allocated_weight,
                    COALESCE(SUM(partial_fob), 0) as total_allocated_fob,
                    COALESCE(SUM(partial_insurance), 0) as total_allocated_insurance,
                    COALESCE(SUM(partial_freight), 0) as total_allocated_freight,
                    COALESCE(SUM(partial_other_costs), 0) as total_allocated_other
                FROM partial_t 
                WHERE license_id = ? AND id != ? AND display = 'Y'
            ";
            $capacityResult = $db->customQuery($capacityQuery, [$existing['license_id'], $id]);
            
            $other_allocated_weight = (!empty($capacityResult)) ? $capacityResult[0]['total_allocated_weight'] : 0;
            $other_allocated_fob = (!empty($capacityResult)) ? $capacityResult[0]['total_allocated_fob'] : 0;
            $other_allocated_insurance = (!empty($capacityResult)) ? $capacityResult[0]['total_allocated_insurance'] : 0;
            $other_allocated_freight = (!empty($capacityResult)) ? $capacityResult[0]['total_allocated_freight'] : 0;
            $other_allocated_other = (!empty($capacityResult)) ? $capacityResult[0]['total_allocated_other'] : 0;

            // Calculate new totals
            $new_total_weight = $other_allocated_weight + $partial_weight;
            $new_total_fob = $other_allocated_fob + $partial_fob;
            $new_total_insurance = $other_allocated_insurance + $partial_insurance;
            $new_total_freight = $other_allocated_freight + $partial_freight;
            $new_total_other = $other_allocated_other + $partial_other_costs;

            // Check capacity limits
            if ($new_total_weight > $license_weight) {
                $available = round($license_weight - $other_allocated_weight, 2);
                echo json_encode([
                    'success' => false,
                    'message' => "Total allocated weight ({$new_total_weight} KG) exceeds license capacity ({$license_weight} KG). Available: {$available} KG"
                ]);
                exit;
            }

            if ($new_total_fob > $license_fob) {
                $available = round($license_fob - $other_allocated_fob, 2);
                echo json_encode([
                    'success' => false,
                    'message' => "Total allocated FOB ({$new_total_fob}) exceeds license FOB ({$license_fob}). Available: {$available}"
                ]);
                exit;
            }

            if ($new_total_insurance > $license_insurance) {
                $available = round($license_insurance - $other_allocated_insurance, 2);
                echo json_encode([
                    'success' => false,
                    'message' => "Total allocated Insurance ({$new_total_insurance}) exceeds license Insurance ({$license_insurance}). Available: {$available}"
                ]);
                exit;
            }

            if ($new_total_freight > $license_freight) {
                $available = round($license_freight - $other_allocated_freight, 2);
                echo json_encode([
                    'success' => false,
                    'message' => "Total allocated Freight ({$new_total_freight}) exceeds license Freight ({$license_freight}). Available: {$available}"
                ]);
                exit;
            }

            if ($new_total_other > $license_other_costs) {
                $available = round($license_other_costs - $other_allocated_other, 2);
                echo json_encode([
                    'success' => false,
                    'message' => "Total allocated Other Costs ({$new_total_other}) exceeds license Other Costs ({$license_other_costs}). Available: {$available}"
                ]);
                exit;
            }

            // ✅ VALIDATION PASSED - Calculate used values for display
            $usedQuery = "
                SELECT 
                    COALESCE(SUM(weight), 0) as used_weight,
                    COALESCE(SUM(fob), 0) as used_fob
                FROM imports_t 
                WHERE inspection_reports = ? AND display = 'Y'
            ";
            $usedResult = $db->customQuery($usedQuery, [$existing['partial_name']]);
            $used_weight = (!empty($usedResult)) ? round($usedResult[0]['used_weight'], 2) : 0;
            $used_fob = (!empty($usedResult)) ? round($usedResult[0]['used_fob'], 2) : 0;

            // Calculate derived fields
            $licenseweight_partial_weight = round($license_weight - $partial_weight, 2);
            $licensefob_partial_fob = round($license_fob - $partial_fob, 2);
            $partial_weight_usedweight = round($partial_weight - $used_weight, 2);
            $partial_fob_usedfob = round($partial_fob - $used_fob, 2);

            // Update data
            $data = [
                'license_weight' => $license_weight,
                'license_fob' => $license_fob,
                'license_insurance' => $license_insurance,
                'license_freight' => $license_freight,
                'license_other_costs' => $license_other_costs,
                'partial_weight' => $partial_weight,
                'partial_fob' => $partial_fob,
                'partial_insurance' => $partial_insurance,
                'partial_freight' => $partial_freight,
                'partial_other_costs' => $partial_other_costs,
                'licenseweight_partial_weight' => $licenseweight_partial_weight,
                'licensefob_partial_fob' => $licensefob_partial_fob,
                'partial_weight_usedweight' => $partial_weight_usedweight,
                'partial_fob_usedfob' => $partial_fob_usedfob,
                'updated_by' => $_SESSION['user_id'] ?? 1,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $update = $db->updateData('partial_t', $data, ['id' => $id]);

            echo json_encode([
                'success' => $update ? true : false,
                'message' => $update ? 'PARTIELLE updated successfully!' : 'Update failed.'
            ]);
            exit;
        }

        echo json_encode(['success' => false, 'message' => 'Invalid request']);
        exit;
    }

    /*
     * ENSURE PARTIAL_T TABLE EXISTS - MATCHES DATABASE SCHEMA
     */
    private function ensurePartialTableExists($db)
    {
        try {
            $checkTableSql = "SHOW TABLES LIKE 'partial_t'";
            $exists = $db->customQuery($checkTableSql);

            if (empty($exists) || !is_array($exists)) {
                $createTableSql = "
                    CREATE TABLE `partial_t` (
                      `id` INT(11) NOT NULL AUTO_INCREMENT,
                      `partial_name` VARCHAR(255) NOT NULL COMMENT 'e.g., CRF123/PART-001',
                      `license_id` INT(11) NOT NULL COMMENT 'Foreign key to licenses_t',
                      `client_id` INT(11) DEFAULT NULL COMMENT 'Foreign key to clients_t',
                      
                      `license_weight` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Original license weight',
                      `license_fob` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Original license FOB declared',
                      `license_insurance` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Original license insurance',
                      `license_freight` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Original license freight',
                      `license_other_costs` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Original license other costs',
                      
                      `partial_weight` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Allocated weight for this partial',
                      `partial_fob` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Allocated FOB for this partial',
                      `partial_insurance` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Allocated insurance',
                      `partial_freight` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Allocated freight',
                      `partial_other_costs` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Allocated other costs',
                      
                      `licenseweight_partial_weight` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'License weight - Partial weight',
                      `licensefob_partial_fob` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'License FOB - Partial FOB',
                      `partial_weight_usedweight` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Partial weight - Used weight',
                      `partial_fob_usedfob` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Partial FOB - Used FOB',
                      
                      `created_by` INT(11) NOT NULL,
                      `updated_by` INT(11) DEFAULT NULL,
                      `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                      `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                      `display` ENUM('Y', 'N') NOT NULL DEFAULT 'Y',
                      
                      PRIMARY KEY (`id`),
                      UNIQUE KEY `unique_partial_name` (`partial_name`),
                      KEY `idx_license_id` (`license_id`),
                      KEY `idx_client_id` (`client_id`),
                      KEY `idx_display` (`display`),
                      KEY `idx_license_display` (`license_id`, `display`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
                    COMMENT='Tracks partial shipments (PARTIELLE) with client_id foreign key';
                ";

                $db->customQuery($createTableSql);
            }
        } catch (Exception $e) {
            error_log("Error ensuring partial_t table: " . $e->getMessage());
        }
    }
}