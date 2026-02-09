<?php
class PerdiemController extends Controller
{
    public function index()
    {
        $db = new Database();
        
        // Get all perdiem records with joined data
        $result = $db->selectQuery("
            SELECT 
                p.id,
                p.client_id,
                c.short_name as client_code,
                p.transport_mode_id,
                t.transport_mode_name,
                p.goods_type_id,
                g.goods_type,
                p.location_id,
                l.main_location_name,
                p.perdiem_amount,
                p.im_ex_lo_id,
                p.display,
                p.created_at,
                p.updated_at
            FROM perdiem_master_t p
            LEFT JOIN clients_t c ON p.client_id = c.id
            LEFT JOIN transport_mode_master_t t ON p.transport_mode_id = t.id
            LEFT JOIN type_of_goods_master_t g ON p.goods_type_id = g.id
            LEFT JOIN main_office_master_t l ON p.location_id = l.id
            ORDER BY p.id DESC
        ");

        // Get clients for dropdown
        $clients = $db->selectData('clients_t', 'id, short_name, company_name', ['display' => 'Y'], 'short_name ASC');

        // Get transport modes for dropdown
        $transportModes = $db->selectData('transport_mode_master_t', 'id, transport_mode_name', ['display' => 'Y'], 'transport_mode_name ASC');

        // Get goods types for dropdown
        $goodsTypes = $db->selectData('type_of_goods_master_t', 'id, goods_type', ['display' => 'Y'], 'goods_type ASC');

        // Get locations for dropdown
        $locations = $db->selectData('main_office_master_t', 'id, main_location_name', ['display' => 'Y'], 'main_location_name ASC');

        $data = [
            'title' => 'Per Diem Master',
            'result' => $result ?: [],
            'clients' => $clients ?: [],
            'transportModes' => $transportModes ?: [],
            'goodsTypes' => $goodsTypes ?: [],
            'locations' => $locations ?: []
        ];

        $this->viewWithLayout('masters/perdiem', $data);
    }

    public function crudData($action = 'insertion')
    {
        $db = new Database();
        $table = 'perdiem_master_t';

        function sanitize($value)
        {
            return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
        }

        // INSERT (BULK OR SINGLE)
        if ($action === 'insertion' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            
            // Get multiple client IDs (for bulk) or single (for copy)
            $client_ids = isset($_POST['client_id']) ? $_POST['client_id'] : [];
            
            // If single value, convert to array
            if (!is_array($client_ids)) {
                $client_ids = [$client_ids];
            }
            
            // Filter out empty values
            $client_ids = array_filter($client_ids, function($id) {
                return !empty($id) && $id > 0;
            });
            
            $transport_mode_id = (int)($_POST['transport_mode_id'] ?? 0);
            $goods_type_id = (int)($_POST['goods_type_id'] ?? 0);
            $im_ex_lo_id = (int)($_POST['im_ex_lo_id'] ?? 0);
            $location_id = (int)($_POST['location_id'] ?? 0);
            $perdiem_amount = floatval($_POST['perdiem_amount'] ?? 0);
            $display = isset($_POST['display']) && in_array($_POST['display'], ['Y', 'N']) ? $_POST['display'] : 'Y';

            // Validation
            if (empty($client_ids) || $transport_mode_id <= 0 || $im_ex_lo_id <= 0 || $goods_type_id <= 0 || $location_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'All fields are required.']);
                exit;
            }

            if ($perdiem_amount <= 0) {
                echo json_encode(['success' => false, 'message' => 'Per diem amount must be greater than zero.']);
                exit;
            }

            $successCount = 0;
            $skipCount = 0;
            $failCount = 0;
            $skippedClients = [];
            
            // Loop through each selected client
            foreach ($client_ids as $client_id) {
                $client_id = (int)$client_id;
                
                if ($client_id <= 0) {
                    $failCount++;
                    continue;
                }

                // Check if combination already exists
                $existCheck = $db->selectData($table, 'id', [
                    'client_id' => $client_id,
                    'transport_mode_id' => $transport_mode_id,
                    'goods_type_id' => $goods_type_id,
                    'location_id' => $location_id,
                    'im_ex_lo_id' => $im_ex_lo_id
                ]);

                if (!empty($existCheck)) {
                    $skipCount++;
                    // Get client name for skipped message
                    $clientInfo = $db->selectData('clients_t', 'short_name', ['id' => $client_id]);
                    if (!empty($clientInfo)) {
                        $skippedClients[] = $clientInfo[0]['short_name'];
                    }
                    continue;
                }

                // Insert per diem record for this client
                $data = [
                    'client_id' => $client_id,
                    'transport_mode_id' => $transport_mode_id,
                    'goods_type_id' => $goods_type_id,
                    'location_id' => $location_id,
                    'perdiem_amount' => $perdiem_amount,
                    'im_ex_lo_id' => $im_ex_lo_id,
                    'display' => $display,
                    'created_by' => 1,
                    'updated_by' => 1,
                ];

                $insertId = $db->insertData($table, $data);
                
                if ($insertId) {
                    $successCount++;
                } else {
                    $failCount++;
                }
            }

            // Build response message
            $message = [];
            if ($successCount > 0) {
                $message[] = "✅ {$successCount} per diem record(s) added successfully";
            }
            if ($skipCount > 0) {
                $message[] = "⚠️ {$skipCount} duplicate(s) skipped: " . implode(', ', $skippedClients);
            }
            if ($failCount > 0) {
                $message[] = "❌ {$failCount} record(s) failed";
            }

            echo json_encode([
                'success' => $successCount > 0,
                'message' => implode('. ', $message),
                'details' => [
                    'success' => $successCount,
                    'skipped' => $skipCount,
                    'failed' => $failCount
                ]
            ]);
            exit;
        }

        // UPDATE (BULK OR SINGLE)
        if ($action === 'updation' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid per diem ID.']);
                exit;
            }

            // Get multiple client IDs for bulk update
            $client_ids = isset($_POST['client_id']) ? $_POST['client_id'] : [];
            
            // If single value, convert to array
            if (!is_array($client_ids)) {
                $client_ids = [$client_ids];
            }
            
            // Filter out empty values
            $client_ids = array_filter($client_ids, function($cid) {
                return !empty($cid) && $cid > 0;
            });

            $transport_mode_id = (int)($_POST['transport_mode_id'] ?? 0);
            $goods_type_id = (int)($_POST['goods_type_id'] ?? 0);
            $location_id = (int)($_POST['location_id'] ?? 0);
            $im_ex_lo_id = (int)($_POST['im_ex_lo_id'] ?? 0);
            $perdiem_amount = floatval($_POST['perdiem_amount'] ?? 0);
            $display = isset($_POST['display']) && in_array($_POST['display'], ['Y', 'N']) ? $_POST['display'] : 'Y';

            if (empty($client_ids) || $transport_mode_id <= 0 || $im_ex_lo_id <= 0 || $goods_type_id <= 0 || $location_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'All fields are required.']);
                exit;
            }

            if ($perdiem_amount <= 0) {
                echo json_encode(['success' => false, 'message' => 'Per diem amount must be greater than zero.']);
                exit;
            }

            // Get the original record to check if we're updating the same client
            $originalRecord = $db->selectData($table, '*', ['id' => $id]);
            if (empty($originalRecord)) {
                echo json_encode(['success' => false, 'message' => 'Record not found.']);
                exit;
            }

            $original_client_id = $originalRecord[0]['client_id'];

            $successCount = 0;
            $skipCount = 0;
            $failCount = 0;
            $skippedClients = [];
            $isFirstClient = true;

            foreach ($client_ids as $client_id) {
                $client_id = (int)$client_id;
                
                if ($client_id <= 0) {
                    $failCount++;
                    continue;
                }

                // If this is the first client and it's the same as original, UPDATE the existing record
                if ($isFirstClient && $client_id == $original_client_id) {
                    $updateData = [
                        'client_id' => $client_id,
                        'transport_mode_id' => $transport_mode_id,
                        'goods_type_id' => $goods_type_id,
                        'location_id' => $location_id,
                        'im_ex_lo_id' => $im_ex_lo_id,
                        'perdiem_amount' => $perdiem_amount,
                        'display' => $display,
                        'updated_by' => 1,
                    ];

                    $update = $db->updateData($table, $updateData, ['id' => $id]);
                    if ($update) {
                        $successCount++;
                    } else {
                        $failCount++;
                    }
                    $isFirstClient = false;
                    continue;
                }

                // For different clients or additional clients, check for duplicates
                $sql = "
                    SELECT id FROM perdiem_master_t
                    WHERE client_id = $client_id
                    AND transport_mode_id = $transport_mode_id
                    AND goods_type_id = $goods_type_id
                    AND location_id = $location_id
                    AND im_ex_lo_id = $im_ex_lo_id
                ";

                $existCheck = $db->selectQuery($sql);

                if (!empty($existCheck)) {
                    $skipCount++;
                    $clientInfo = $db->selectData('clients_t', 'short_name', ['id' => $client_id]);
                    if (!empty($clientInfo)) {
                        $skippedClients[] = $clientInfo[0]['short_name'];
                    }
                    continue;
                }

                // If first client is different from original, UPDATE the existing record
                if ($isFirstClient) {
                    $updateData = [
                        'client_id' => $client_id,
                        'transport_mode_id' => $transport_mode_id,
                        'goods_type_id' => $goods_type_id,
                        'location_id' => $location_id,
                        'im_ex_lo_id' => $im_ex_lo_id,
                        'perdiem_amount' => $perdiem_amount,
                        'display' => $display,
                        'updated_by' => 1,
                    ];

                    $update = $db->updateData($table, $updateData, ['id' => $id]);
                    if ($update) {
                        $successCount++;
                    } else {
                        $failCount++;
                    }
                    $isFirstClient = false;
                } else {
                    // For additional clients, INSERT new records
                    $insertData = [
                        'client_id' => $client_id,
                        'transport_mode_id' => $transport_mode_id,
                        'goods_type_id' => $goods_type_id,
                        'location_id' => $location_id,
                        'perdiem_amount' => $perdiem_amount,
                        'im_ex_lo_id' => $im_ex_lo_id,
                        'display' => $display,
                        'created_by' => 1,
                        'updated_by' => 1,
                    ];

                    $insertId = $db->insertData($table, $insertData);
                    if ($insertId) {
                        $successCount++;
                    } else {
                        $failCount++;
                    }
                }
            }

            // Build response message
            $message = [];
            if ($successCount > 0) {
                $message[] = "✅ {$successCount} per diem record(s) updated successfully";
            }
            if ($skipCount > 0) {
                $message[] = "⚠️ {$skipCount} duplicate(s) skipped: " . implode(', ', $skippedClients);
            }
            if ($failCount > 0) {
                $message[] = "❌ {$failCount} record(s) failed";
            }

            echo json_encode([
                'success' => $successCount > 0,
                'message' => implode('. ', $message),
                'details' => [
                    'success' => $successCount,
                    'skipped' => $skipCount,
                    'failed' => $failCount
                ]
            ]);
            exit;
        }

        // DELETE
        if ($action === 'deletion') {
            header('Content-Type: application/json');
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid ID for deletion.']);
                exit;
            }

            $delete = $db->deleteData($table, ['id' => $id]);
            echo json_encode([
                'success' => $delete ? true : false,
                'message' => $delete ? 'Per diem deleted successfully!' : 'Delete failed.'
            ]);
            exit;
        }

        echo json_encode(['success' => false, 'message' => 'Invalid request.']);
        exit;
    }

    public function getPerdiemById()
    {
        header('Content-Type: application/json');
        $db = new Database();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
            exit;
        }

        $perdiem = $db->selectData('perdiem_master_t', '*', ['id' => $id]);

        echo json_encode(!empty($perdiem)
            ? ['success' => true, 'data' => $perdiem[0]]
            : ['success' => false, 'message' => 'Per diem not found.']
        );
        exit;
    }

   public function exportToExcel()
{
    $db = new Database();
    
    // Build WHERE clause based on filters
    $whereConditions = [];
    $params = [];
    
    if (!empty($_GET['filter_client_id'])) {
        $whereConditions[] = "p.client_id = ?";
        $params[] = (int)$_GET['filter_client_id'];
    }
    
    if (!empty($_GET['filter_transport_mode_id'])) {
        $whereConditions[] = "p.transport_mode_id = ?";
        $params[] = (int)$_GET['filter_transport_mode_id'];
    }
    
    if (!empty($_GET['filter_goods_type_id'])) {
        $whereConditions[] = "p.goods_type_id = ?";
        $params[] = (int)$_GET['filter_goods_type_id'];
    }
    
    if (!empty($_GET['filter_location_id'])) {
        $whereConditions[] = "p.location_id = ?";
        $params[] = (int)$_GET['filter_location_id'];
    }
    
    if (!empty($_GET['filter_im_ex_lo_id'])) {
        $whereConditions[] = "p.im_ex_lo_id = ?";
        $params[] = (int)$_GET['filter_im_ex_lo_id'];
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Get filtered data
    $sql = "
        SELECT 
            p.id,
            c.short_name as client_code,
            t.transport_mode_name,
            g.goods_type,
            l.main_location_name,
            p.im_ex_lo_id,
            p.perdiem_amount,
            p.display,
            p.created_at,
            p.updated_at
        FROM perdiem_master_t p
        LEFT JOIN clients_t c ON p.client_id = c.id
        LEFT JOIN transport_mode_master_t t ON p.transport_mode_id = t.id
        LEFT JOIN type_of_goods_master_t g ON p.goods_type_id = g.id
        LEFT JOIN main_office_master_t l ON p.location_id = l.id
        $whereClause
        ORDER BY p.id DESC
    ";
    
    if (!empty($params)) {
        $result = $db->selectQuery($sql, $params);
    } else {
        $result = $db->selectQuery($sql);
    }
    
    // Create new Spreadsheet - Fix the autoload path
    require_once __DIR__ . '/../../../vendor/autoload.php';
    
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Set document properties
    $spreadsheet->getProperties()
        ->setCreator("Malabar Group")
        ->setTitle("Per Diem Master Report")
        ->setSubject("Per Diem Master Export")
        ->setDescription("Exported Per Diem Master data");
    
    // Header styling
    $headerStyle = [
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF'],
            'size' => 12
        ],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => ['rgb' => '4472C4']
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => ['rgb' => '000000']
            ]
        ]
    ];
    
    // Set headers
    $headers = ['ID', 'Client', 'Transport Mode', 'Goods Type', 'Location', 'Mode', 'Amount (USD)', 'Status', 'Created At', 'Updated At'];
    $column = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($column . '1', $header);
        $column++;
    }
    
    // Apply header style
    $sheet->getStyle('A1:J1')->applyFromArray($headerStyle);
    
    // Set auto width for columns
    foreach (range('A', 'J') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    // Fill data
    $row = 2;
    if (!empty($result)) {
        foreach ($result as $data) {
            $modeMap = [
                1 => 'Import',
                2 => 'Export',
                3 => 'Local'
            ];
            
            $sheet->setCellValue('A' . $row, $data['id'] ?? '');
            $sheet->setCellValue('B' . $row, $data['client_code'] ?? '');
            $sheet->setCellValue('C' . $row, $data['transport_mode_name'] ?? '');
            $sheet->setCellValue('D' . $row, $data['goods_type'] ?? '');
            $sheet->setCellValue('E' . $row, $data['main_location_name'] ?? '');
            $sheet->setCellValue('F' . $row, $modeMap[$data['im_ex_lo_id']] ?? '');
            $sheet->setCellValue('G' . $row, number_format($data['perdiem_amount'] ?? 0, 2));
            $sheet->setCellValue('H' . $row, ($data['display'] == 'Y') ? 'Active' : 'Inactive');
            $sheet->setCellValue('I' . $row, $data['created_at'] ?? '');
            $sheet->setCellValue('J' . $row, $data['updated_at'] ?? '');
            
            // Apply borders to data rows
            $sheet->getStyle('A' . $row . ':J' . $row)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC']
                    ]
                ]
            ]);
            
            // Right align amount
            $sheet->getStyle('G' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            
            $row++;
        }
    }
    
    // Set row height for header
    $sheet->getRowDimension(1)->setRowHeight(25);
    
    // Freeze first row
    $sheet->freezePane('A2');
    
    // Create Excel file
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    
    // Set headers for download
    $filename = 'Perdiem_Master_' . date('Y-m-d_H-i-s') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $writer->save('php://output');
    exit;
}
}
?>