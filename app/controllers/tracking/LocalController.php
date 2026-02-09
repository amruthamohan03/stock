<?php

class LocalController extends Controller
{

  private $db;
  private $logFile;

  public function __construct()
  {
    $this->db = new Database();
    $this->logFile = __DIR__ . '/../../logs/local_operations.log';
    
    $logDir = dirname($this->logFile);
    if (!is_dir($logDir)) {
      mkdir($logDir, 0755, true);
    }
  }

  /**
   * Index page - Display local form and list
   */
  public function index()
  {
    if (empty($_SESSION['csrf_token'])) {
      $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    // Only fetch clients with client_type containing 'L'
    $sql = "SELECT id, short_name 
            FROM clients_t 
            WHERE display = 'Y' 
              AND client_type LIKE '%L%' 
            ORDER BY short_name ASC";
    $clients = $this->db->customQuery($sql);
    
    // Get locations with IDs 1, 2, and 4
    $locationSql = "SELECT id, main_location_name 
                    FROM main_office_master_t 
                    WHERE display = 'Y' AND id IN (1, 2, 4) 
                    ORDER BY id ASC";
    $locations = $this->db->customQuery($locationSql);

    $data = [
      'title' => 'Locals Management',
      'clients' => $clients,
      'locations' => $locations,
      'csrf_token' => $_SESSION['csrf_token']
    ];

    $this->viewWithLayout('tracking/locals', $data);
  }

  /**
   * CRUD Data Router
   */
  public function crudData($action = 'insertion')
  {
    header('Content-Type: application/json');

    try {
      switch ($action) {
        case 'insert':
        case 'insertion':
          $this->insertLocal();
          break;
        case 'update':
          $this->updateLocal();
          break;
        case 'deletion':
          $this->deleteLocal();
          break;
        case 'getLocal':
          $this->getLocal();
          break;
        case 'listing':
          $this->listLocals();
          break;
        case 'statistics':
          $this->getStatistics();
          break;
        case 'getNextLTSequence':
          $this->getNextLTSequence();
          break;
        case 'exportLocal':
          $this->exportLocal();
          break;
        case 'exportAll':
          $this->exportAllLocals();
          break;
        default:
          $this->logError('Invalid action attempted', ['action' => $action]);
          echo json_encode(['success' => false, 'message' => 'Invalid action']);
      }
    } catch (Exception $e) {
      $this->logError('Server error in crudData', [
        'action' => $action,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);
      echo json_encode(['success' => false, 'message' => 'Server error occurred. Please try again.']);
    }
    exit;
  }

  /**
   * Export single local to Excel using PhpSpreadsheet
   */
  private function exportLocal()
  {
    $localId = (int) ($_GET['id'] ?? 0);

    if ($localId <= 0) {
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'message' => 'Invalid local ID']);
      return;
    }

    try {
      $vendorPath = __DIR__ . '/../../../vendor/autoload.php';
      
      if (!file_exists($vendorPath)) {
        throw new Exception('PhpSpreadsheet not found. Please run: composer require phpoffice/phpspreadsheet');
      }
      
      require_once $vendorPath;

      $sql = "SELECT 
                l.*,
                l.location as location_id,
                c.short_name as client_name,
                m.main_location_name as location_name
              FROM locals_t l
              LEFT JOIN clients_t c ON l.client_id = c.id
              LEFT JOIN main_office_master_t m ON l.location = m.id
              WHERE l.id = :id AND l.display = 'Y'";

      $result = $this->db->customQuery($sql, [':id' => $localId]);
      
      if (empty($result)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Local not found']);
        return;
      }

      $data = $result[0];
      
      $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->setTitle('Local Details');

      $headers = [
        'ID', 'Client', 'Location', 'MCA LT Reference', 'Lot Num', 'Horse', 'Trailer 1', 'Trailer 2',
        'Transporter', 'Nbr of Bags', 'Weight (T)', 'Arrival Date', 'Loading Date',
        'BP Details Received Date', 'PV Div Mines Date', 'Demande Attestation Date',
        'CEEC In', 'CEEC Out', 'CGEA', 'Gov Docs Complete Date', 'Disp Date',
        'End of Formalities', 'Remarks'
      ];
      
      $values = [
  $data['id'] ?? '',
  $data['client_name'] ?? 'N/A',
  $data['location_name'] ?? 'N/A',
  $data['mca_lt_reference'] ?? 'N/A',
  $data['lot_num'] ?? 'N/A',
  $data['horse'] ?? 'N/A',
  $data['trailer_1'] ?? 'N/A',
  $data['trailer_2'] ?? 'N/A',
  $data['transporter'] ?? 'N/A',
  $data['nbr_of_bags'] ?? 'N/A',
  $data['weight'] ? number_format($data['weight'], 2) : 'N/A',
  $data['arrival_date'] ? date('d-m-Y', strtotime($data['arrival_date'])) : 'N/A',
  $data['loading_date'] ? date('d-m-Y', strtotime($data['loading_date'])) : 'N/A',
  $data['bp_details_received_date'] ? date('d-m-Y', strtotime($data['bp_details_received_date'])) : 'N/A',
  $data['pv_div_mines_date'] ? date('d-m-Y', strtotime($data['pv_div_mines_date'])) : 'N/A',
  $data['demande_attestation_date'] ? date('d-m-Y', strtotime($data['demande_attestation_date'])) : 'N/A',
  $data['ceec_in'] ? date('d-m-Y', strtotime($data['ceec_in'])) : 'N/A',
  $data['ceec_out'] ? date('d-m-Y', strtotime($data['ceec_out'])) : 'N/A',
  $data['cgea'] ?? 'N/A',  // CHANGED: No date formatting
  $data['gov_docs_complete_date'] ? date('d-m-Y', strtotime($data['gov_docs_complete_date'])) : 'N/A',
  $data['disp_date'] ? date('d-m-Y', strtotime($data['disp_date'])) : 'N/A',
  $data['end_of_formalities'] ? date('d-m-Y', strtotime($data['end_of_formalities'])) : 'N/A',
  $data['remarks'] ?? 'N/A'
];

      $excelData = [$headers, $values];
      $sheet->fromArray($excelData, null, 'A1');

      $headerStyle = [
        'font' => [
          'bold' => true,
          'color' => ['rgb' => 'FFFFFF'],
          'size' => 11
        ],
        'fill' => [
          'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
          'startColor' => ['rgb' => '667eea']
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
      
      $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
      $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray($headerStyle);

      $valueStyle = [
        'borders' => [
          'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            'color' => ['rgb' => 'CCCCCC']
          ]
        ],
        'alignment' => [
          'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
          'wrapText' => true
        ]
      ];
      $sheet->getStyle('A2:' . $lastColumn . '2')->applyFromArray($valueStyle);

      foreach (range(1, count($headers)) as $colIndex) {
        $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
        $sheet->getColumnDimension($column)->setWidth(18);
      }

      $sheet->getRowDimension(1)->setRowHeight(25);
      $sheet->getRowDimension(2)->setRowHeight(20);

      $today = date('d-m-Y');
      $mcaLtRef = $data['mca_lt_reference'] ?? 'Export';
      $filename = 'Local_' . str_replace(['/', '\\', '-'], '_', $mcaLtRef) . '_' . $today . '.xlsx';
      
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment;filename="' . $filename . '"');
      header('Cache-Control: max-age=0');

      $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
      $writer->save('php://output');

      $spreadsheet->disconnectWorksheets();
      unset($spreadsheet);
      exit;
      
    } catch (Exception $e) {
      $this->logError('Export Local Error', ['error' => $e->getMessage()]);
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'message' => 'Export failed: ' . $e->getMessage()]);
      exit;
    }
  }

  /**
   * Export all locals to Excel
   */
  private function exportAllLocals()
  {
    try {
      $vendorPath = __DIR__ . '/../../../vendor/autoload.php';
      
      if (!file_exists($vendorPath)) {
        throw new Exception('PhpSpreadsheet not found. Please run: composer require phpoffice/phpspreadsheet');
      }
      
      require_once $vendorPath;

      $sql = "SELECT 
                l.*,
                l.location as location_id,
                c.short_name as client_name,
                m.main_location_name as location_name
              FROM locals_t l
              LEFT JOIN clients_t c ON l.client_id = c.id
              LEFT JOIN main_office_master_t m ON l.location = m.id
              WHERE l.display = 'Y'
              ORDER BY l.id DESC";

      $locals = $this->db->customQuery($sql);

      if (empty($locals)) {
        $this->logError('No locals found for export', [
          'query' => $sql,
          'result' => 'empty'
        ]);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No data found to export. Please add some locals first.']);
        return;
      }

      $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->setTitle('All Locals');

      $headers = [
        'ID', 'Client', 'Location', 'MCA LT Reference', 'Lot Num', 'Horse', 'Trailer 1', 'Trailer 2',
        'Transporter', 'Nbr of Bags', 'Weight (T)', 'Arrival Date', 'Loading Date',
        'BP Details Received Date', 'PV Div Mines Date', 'Demande Attestation Date',
        'CEEC In', 'CEEC Out', 'CGEA', 'Gov Docs Complete Date', 'Disp Date',
        'End of Formalities', 'Remarks'
      ];

      $sheet->fromArray([$headers], null, 'A1');

      foreach ($locals as $local) {
  $rowData = [
    $local['id'] ?? '',
    $local['client_name'] ?? 'N/A',
    $local['location_name'] ?? 'N/A',
    $local['mca_lt_reference'] ?? 'N/A',
    $local['lot_num'] ?? 'N/A',
    $local['horse'] ?? 'N/A',
    $local['trailer_1'] ?? 'N/A',
    $local['trailer_2'] ?? 'N/A',
    $local['transporter'] ?? 'N/A',
    $local['nbr_of_bags'] ?? 'N/A',
    $local['weight'] ? number_format($local['weight'], 2) : 'N/A',
    $local['arrival_date'] ? date('d-m-Y', strtotime($local['arrival_date'])) : 'N/A',
    $local['loading_date'] ? date('d-m-Y', strtotime($local['loading_date'])) : 'N/A',
    $local['bp_details_received_date'] ? date('d-m-Y', strtotime($local['bp_details_received_date'])) : 'N/A',
    $local['pv_div_mines_date'] ? date('d-m-Y', strtotime($local['pv_div_mines_date'])) : 'N/A',
    $local['demande_attestation_date'] ? date('d-m-Y', strtotime($local['demande_attestation_date'])) : 'N/A',
    $local['ceec_in'] ? date('d-m-Y', strtotime($local['ceec_in'])) : 'N/A',
    $local['ceec_out'] ? date('d-m-Y', strtotime($local['ceec_out'])) : 'N/A',
    $local['cgea'] ?? 'N/A',  // CHANGED: No date formatting
    $local['gov_docs_complete_date'] ? date('d-m-Y', strtotime($local['gov_docs_complete_date'])) : 'N/A',
    $local['disp_date'] ? date('d-m-Y', strtotime($local['disp_date'])) : 'N/A',
    $local['end_of_formalities'] ? date('d-m-Y', strtotime($local['end_of_formalities'])) : 'N/A',
    $local['remarks'] ?? 'N/A'
  ];
  
  $sheet->fromArray([$rowData], null, 'A' . $rowIndex);
  $rowIndex++;
}

      $headerStyle = [
        'font' => [
          'bold' => true,
          'color' => ['rgb' => 'FFFFFF'],
          'size' => 11
        ],
        'fill' => [
          'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
          'startColor' => ['rgb' => '28a745']
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
      
      $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
      $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray($headerStyle);

      $dataStyle = [
        'borders' => [
          'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            'color' => ['rgb' => 'CCCCCC']
          ]
        ],
        'alignment' => [
          'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
        ]
      ];
      $sheet->getStyle('A2:' . $lastColumn . ($rowIndex - 1))->applyFromArray($dataStyle);

      foreach (range(1, count($headers)) as $colIndex) {
        $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
        $sheet->getColumnDimension($column)->setWidth(15);
      }

      $sheet->getRowDimension(1)->setRowHeight(25);
      $sheet->setAutoFilter('A1:' . $lastColumn . '1');

      $today = date('d-m-Y');
      $filename = 'Local_tracking_' . $today . '.xlsx';
      
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment;filename="' . $filename . '"');
      header('Cache-Control: max-age=0');

      $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
      $writer->save('php://output');

      $spreadsheet->disconnectWorksheets();
      unset($spreadsheet);
      exit;
      
    } catch (Exception $e) {
      $this->logError('Export All Error', ['error' => $e->getMessage()]);
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'message' => 'Export failed: ' . $e->getMessage()]);
      exit;
    }
  }

  /**
   * Get next LT Reference sequence number
   */
  private function getNextLTSequence()
  {
    $this->validateCsrfToken();

    try {
      $clientId = (int)($_POST['client_id'] ?? 0);
      $locationId = (int)($_POST['location_id'] ?? 0);
      $year = $_POST['year'] ?? date('y');

      if ($clientId <= 0 || $locationId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        return;
      }

      $client = $this->db->selectData('clients_t', 'short_name', ['id' => $clientId]);
      if (empty($client)) {
        echo json_encode(['success' => false, 'message' => 'Client not found']);
        return;
      }
      $clientName = $client[0]['short_name'];
      $clientAbbrev = substr(preg_replace('/[^A-Z]/', '', strtoupper($clientName)), 0, 3);

      $location = $this->db->selectData('main_office_master_t', 'main_location_name', ['id' => $locationId]);
      if (empty($location)) {
        echo json_encode(['success' => false, 'message' => 'Location not found']);
        return;
      }
      $locationName = $location[0]['main_location_name'];
      $locationPrefix = substr(preg_replace('/[^A-Z]/', '', strtoupper($locationName)), 0, 2);

      $prefix = "{$clientAbbrev}-LT{$locationPrefix}{$year}-";

      $sql = "SELECT mca_lt_reference 
              FROM locals_t 
              WHERE mca_lt_reference LIKE :prefix 
              AND display = 'Y'
              ORDER BY mca_lt_reference DESC 
              LIMIT 1";
      
      $result = $this->db->customQuery($sql, [':prefix' => $prefix . '%']);

      $nextSequence = 1;

      if (!empty($result)) {
        $lastRef = $result[0]['mca_lt_reference'];
        if (preg_match('/-(\d{4})$/', $lastRef, $matches)) {
          $lastSequence = (int)$matches[1];
          $nextSequence = $lastSequence + 1;
        }
      }

      echo json_encode([
        'success' => true,
        'sequence' => $nextSequence,
        'prefix' => $prefix
      ]);

    } catch (Exception $e) {
      $this->logError('Failed to generate LT sequence', [
        'error' => $e->getMessage()
      ]);
      echo json_encode(['success' => false, 'message' => 'Failed to generate sequence']);
    }
  }

  /**
   * Validate CSRF Token
   */
  private function validateCsrfToken()
  {
    $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
    
    if (empty($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
      $this->logError('CSRF token validation failed', [
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
      ]);
      http_response_code(403);
      echo json_encode(['success' => false, 'message' => 'Invalid security token. Please refresh the page.']);
      exit;
    }
  }

  /**
   * Log errors
   */
  private function logError($message, $context = [])
  {
    $logEntry = [
      'timestamp' => date('Y-m-d H:i:s'),
      'level' => 'ERROR',
      'message' => $message,
      'user_id' => $_SESSION['user_id'] ?? 'guest',
      'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
      'context' => $context
    ];
    
    $logLine = json_encode($logEntry) . PHP_EOL;
    file_put_contents($this->logFile, $logLine, FILE_APPEND | LOCK_EX);
  }

  /**
   * Log info
   */
  private function logInfo($message, $context = [])
  {
    $logEntry = [
      'timestamp' => date('Y-m-d H:i:s'),
      'level' => 'INFO',
      'message' => $message,
      'user_id' => $_SESSION['user_id'] ?? 'guest',
      'context' => $context
    ];
    
    $logLine = json_encode($logEntry) . PHP_EOL;
    file_put_contents($this->logFile, $logLine, FILE_APPEND | LOCK_EX);
  }

  /**
   * Get statistics
   */
  private function getStatistics()
  {
    try {
      $totalSql = "SELECT COUNT(*) as total_tracking
                   FROM locals_t l
                   LEFT JOIN main_office_master_t m ON l.location = m.id
                   WHERE l.display = 'Y' AND m.id IN (1, 2, 4)";
      $totalResult = $this->db->customQuery($totalSql);
      $totalTracking = $totalResult[0]['total_tracking'] ?? 0;

      $locationSql = "SELECT 
                        m.id,
                        m.main_location_name,
                        COUNT(l.id) as file_count
                      FROM main_office_master_t m
                      LEFT JOIN locals_t l ON m.id = l.location AND l.display = 'Y'
                      WHERE m.display = 'Y' AND m.id IN (1, 2, 4)
                      GROUP BY m.id, m.main_location_name
                      ORDER BY m.id ASC";
      
      $locationCounts = $this->db->customQuery($locationSql);

      echo json_encode([
        'success' => true,
        'data' => [
          'total_tracking' => $totalTracking,
          'location_counts' => $locationCounts
        ]
      ]);
    } catch (Exception $e) {
      $this->logError('Failed to get statistics', ['error' => $e->getMessage()]);
      echo json_encode([
        'success' => false,
        'message' => 'Failed to load statistics'
      ]);
    }
  }

  /**
   * Insert new local
   */
  private function insertLocal()
  {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      echo json_encode(['success' => false, 'message' => 'Invalid request method']);
      return;
    }

    $this->validateCsrfToken();

    try {
      $validation = $this->validateLocalData($_POST);
      if (!$validation['success']) {
        $this->logError('Validation failed on insert', ['errors' => $validation['message']]);
        echo json_encode($validation);
        return;
      }

      $data = $this->prepareLocalData($_POST);

      $data['created_by'] = $_SESSION['user_id'] ?? 1;
      $data['updated_by'] = $_SESSION['user_id'] ?? 1;
      $data['display'] = 'Y';

      $insertId = $this->db->insertData('locals_t', $data);

      if ($insertId) {
        $this->logInfo('Local created successfully', [
          'local_id' => $insertId,
          'client_id' => $data['client_id'],
          'mca_lt_reference' => $data['mca_lt_reference']
        ]);
        
        echo json_encode([
          'success' => true,
          'message' => 'Local created successfully!',
          'id' => $insertId
        ]);
      } else {
        $this->logError('Failed to insert local', ['data' => $data]);
        echo json_encode(['success' => false, 'message' => 'Failed to save local. Please try again.']);
      }
    } catch (Exception $e) {
      $this->logError('Exception during local insert', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);
      echo json_encode(['success' => false, 'message' => 'An error occurred while saving.']);
    }
  }

  /**
   * Update existing local
   */
  private function updateLocal()
  {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      echo json_encode(['success' => false, 'message' => 'Invalid request method']);
      return;
    }

    $this->validateCsrfToken();

    try {
      $localId = (int) ($_POST['local_id'] ?? 0);
      if ($localId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid local ID']);
        return;
      }

      $existing = $this->db->selectData('locals_t', '*', ['id' => $localId, 'display' => 'Y']);
      if (empty($existing)) {
        $this->logError('Attempted to update non-existent local', ['local_id' => $localId]);
        echo json_encode(['success' => false, 'message' => 'Local not found']);
        return;
      }

      $validation = $this->validateLocalData($_POST, $localId);
      if (!$validation['success']) {
        $this->logError('Validation failed on update', [
          'local_id' => $localId,
          'errors' => $validation['message']
        ]);
        echo json_encode($validation);
        return;
      }

      $data = $this->prepareLocalData($_POST);

      $data['updated_by'] = $_SESSION['user_id'] ?? 1;
      $data['updated_at'] = date('Y-m-d H:i:s');

      $success = $this->db->updateData('locals_t', $data, ['id' => $localId]);

      if ($success) {
        $this->logInfo('Local updated successfully', [
          'local_id' => $localId,
          'changes' => $data
        ]);
        
        echo json_encode([
          'success' => true,
          'message' => 'Local updated successfully!'
        ]);
      } else {
        $this->logError('Failed to update local', ['local_id' => $localId, 'data' => $data]);
        echo json_encode(['success' => false, 'message' => 'Failed to update local. Please try again.']);
      }
    } catch (Exception $e) {
      $this->logError('Exception during local update', [
        'local_id' => $localId ?? 0,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);
      echo json_encode(['success' => false, 'message' => 'An error occurred while updating.']);
    }
  }

  /**
   * Delete local
   */
  private function deleteLocal()
  {
    $this->validateCsrfToken();

    try {
      $localId = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);

      if ($localId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid local ID']);
        return;
      }

      $local = $this->db->selectData('locals_t', '*', ['id' => $localId, 'display' => 'Y']);
      if (empty($local)) {
        $this->logError('Attempted to delete non-existent local', ['local_id' => $localId]);
        echo json_encode(['success' => false, 'message' => 'Local not found']);
        return;
      }

      $success = $this->db->updateData('locals_t', [
        'display' => 'N',
        'updated_by' => $_SESSION['user_id'] ?? 1,
        'updated_at' => date('Y-m-d H:i:s')
      ], ['id' => $localId]);

      if ($success) {
        $this->logInfo('Local deleted successfully', [
          'local_id' => $localId,
          'deleted_record' => $local[0]
        ]);
        
        echo json_encode([
          'success' => true,
          'message' => 'Local deleted successfully!'
        ]);
      } else {
        $this->logError('Failed to delete local', ['local_id' => $localId]);
        echo json_encode(['success' => false, 'message' => 'Failed to delete local']);
      }
    } catch (Exception $e) {
      $this->logError('Exception during local delete', [
        'local_id' => $localId ?? 0,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);
      echo json_encode(['success' => false, 'message' => 'An error occurred while deleting.']);
    }
  }

  /**
   * Get single local
   */
  private function getLocal()
  {
    try {
      $localId = (int) ($_GET['id'] ?? 0);

      if ($localId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid local ID']);
        return;
      }

      $sql = "SELECT 
                l.*,
                l.location as location_id,
                c.short_name as client_name,
                m.main_location_name as location_name
              FROM locals_t l
              LEFT JOIN clients_t c ON l.client_id = c.id
              LEFT JOIN main_office_master_t m ON l.location = m.id
              WHERE l.id = :id AND l.display = 'Y'";

      $local = $this->db->customQuery($sql, [':id' => $localId]);

      if (!empty($local)) {
        echo json_encode([
          'success' => true,
          'data' => $local[0]
        ]);
      } else {
        $this->logError('Local not found for viewing', ['local_id' => $localId]);
        echo json_encode(['success' => false, 'message' => 'Local not found']);
      }
    } catch (Exception $e) {
      $this->logError('Exception while fetching local', [
        'local_id' => $localId ?? 0,
        'error' => $e->getMessage()
      ]);
      echo json_encode(['success' => false, 'message' => 'Failed to load local data']);
    }
  }

  /**
   * List all locals
   */
  private function listLocals()
  {
    try {
      $draw = isset($_GET['draw']) ? (int)$_GET['draw'] : 1;
      $start = isset($_GET['start']) ? (int)$_GET['start'] : 0;
      $length = isset($_GET['length']) ? (int)$_GET['length'] : 25;
      $searchValue = isset($_GET['search']['value']) ? trim($_GET['search']['value']) : '';
      
      $locationFilter = isset($_GET['location_filter']) ? (int)$_GET['location_filter'] : 0;
      
      $orderColumnIndex = isset($_GET['order'][0]['column']) ? (int)$_GET['order'][0]['column'] : 0;
      $orderDirection = isset($_GET['order'][0]['dir']) && $_GET['order'][0]['dir'] === 'asc' ? 'ASC' : 'DESC';
      
      $columns = ['l.id', 'c.short_name', 'm.main_location_name', 'l.mca_lt_reference', 'l.lot_num', 
                  'l.horse', 'l.transporter', 'l.arrival_date'];
      $orderColumn = isset($columns[$orderColumnIndex]) ? $columns[$orderColumnIndex] : 'l.id';

      $baseQuery = "FROM locals_t l
                    LEFT JOIN clients_t c ON l.client_id = c.id
                    LEFT JOIN main_office_master_t m ON l.location = m.id
                    WHERE l.display = 'Y' AND m.id IN (1, 2, 4)";

      $searchCondition = "";
      $params = [];
      
      if ($locationFilter > 0) {
        $baseQuery .= " AND l.location = :location_filter";
        $params[':location_filter'] = $locationFilter;
      }
      
      if (!empty($searchValue)) {
        $searchCondition = " AND (
          l.mca_lt_reference LIKE :search OR
          l.lot_num LIKE :search OR
          l.horse LIKE :search OR
          l.trailer_1 LIKE :search OR
          l.trailer_2 LIKE :search OR
          l.transporter LIKE :search OR
          c.short_name LIKE :search OR
          m.main_location_name LIKE :search
        )";
        $params[':search'] = "%{$searchValue}%";
      }

      $totalSql = "SELECT COUNT(*) as total FROM locals_t l 
                   LEFT JOIN main_office_master_t m ON l.location = m.id 
                   WHERE l.display = 'Y' AND m.id IN (1, 2, 4)";
      $totalResult = $this->db->customQuery($totalSql);
      $totalRecords = $totalResult[0]['total'] ?? 0;

      $filteredSql = "SELECT COUNT(*) as total {$baseQuery} {$searchCondition}";
      $filteredResult = $this->db->customQuery($filteredSql, $params);
      $filteredRecords = $filteredResult[0]['total'] ?? 0;

      $dataSql = "SELECT 
                    l.id,
                    l.location as location_id,
                    l.mca_lt_reference,
                    l.lot_num,
                    l.horse,
                    l.trailer_1,
                    l.trailer_2,
                    l.transporter,
                    l.nbr_of_bags,
                    l.weight,
                    l.arrival_date,
                    l.loading_date,
                    l.bp_details_received_date,
                    l.pv_div_mines_date,
                    l.demande_attestation_date,
                    l.ceec_in,
                    l.ceec_out,
                    l.cgea,
                    l.gov_docs_complete_date,
                    l.disp_date,
                    l.end_of_formalities,
                    l.remarks,
                    c.short_name as client_name,
                    m.main_location_name as location_name,
                    l.created_at
                  {$baseQuery}
                  {$searchCondition}
                  ORDER BY {$orderColumn} {$orderDirection}
                  LIMIT :limit OFFSET :offset";

      $params[':limit'] = $length;
      $params[':offset'] = $start;

      $locals = $this->db->customQuery($dataSql, $params);

      echo json_encode([
        'draw' => $draw,
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $filteredRecords,
        'data' => $locals ?: []
      ]);

    } catch (Exception $e) {
      $this->logError('Exception in listLocals', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);
      
      echo json_encode([
        'draw' => $_GET['draw'] ?? 1,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => 'Failed to load data'
      ]);
    }
  }

  /**
   * Validate local data with date logic
   */
  private function validateLocalData($post, $localId = null)
  {
    $errors = [];

    if (empty($post['client_id'])) {
      $errors[] = 'Client selection is required';
    } else {
      if (!is_numeric($post['client_id']) || (int)$post['client_id'] <= 0) {
        $errors[] = 'Invalid client ID format';
      } else {
        $client = $this->db->selectData('clients_t', 'id, client_type', [
          'id' => (int)$post['client_id'],
          'display' => 'Y'
        ]);
        if (empty($client)) {
          $errors[] = 'Selected client does not exist or is inactive';
        } else {
          $clientType = $client[0]['client_type'] ?? '';
          if (strpos($clientType, 'L') === false) {
            $errors[] = 'Selected client is not configured for Local operations';
          }
        }
      }
    }

    if (empty($post['location'])) {
      $errors[] = 'Location selection is required';
    } else {
      if (!is_numeric($post['location']) || (int)$post['location'] <= 0) {
        $errors[] = 'Invalid location ID format';
      } else {
        $locationId = (int)$post['location'];
        if (!in_array($locationId, [1, 2, 4])) {
          $errors[] = 'Selected location is not allowed';
        } else {
          $location = $this->db->selectData('main_office_master_t', 'id', [
            'id' => $locationId,
            'display' => 'Y'
          ]);
          if (empty($location)) {
            $errors[] = 'Selected location does not exist or is inactive';
          }
        }
      }
    }

    if (empty($post['mca_lt_reference'])) {
      $errors[] = 'MCA LT Reference is required';
    } else {
      $mcaLtRef = trim($post['mca_lt_reference']);
      
      if (!preg_match('/^[A-Z]{1,3}-LT[A-Z]{2}\d{2}-\d{4}$/', $mcaLtRef)) {
        $errors[] = 'MCA LT Reference has invalid format (expected: XXX-LTXX##-####)';
      }
      
      $sql = "SELECT id FROM locals_t WHERE mca_lt_reference = :mca_lt_reference AND display = 'Y'";
      $params = [':mca_lt_reference' => $mcaLtRef];
      
      if ($localId) {
        $sql .= " AND id != :local_id";
        $params[':local_id'] = $localId;
      }
      
      $exists = $this->db->customQuery($sql, $params);
      if ($exists) {
        $errors[] = 'MCA LT Reference already exists in the system';
      }
    }

    // Date validation logic
    $ceecIn = !empty($post['ceec_in']) && $this->isValidDate($post['ceec_in']) ? $post['ceec_in'] : null;
    $ceecOut = !empty($post['ceec_out']) && $this->isValidDate($post['ceec_out']) ? $post['ceec_out'] : null;
    $dispDate = !empty($post['disp_date']) && $this->isValidDate($post['disp_date']) ? $post['disp_date'] : null;

    // Validate CEEC Out >= CEEC In
    if ($ceecIn && $ceecOut) {
      if (strtotime($ceecOut) < strtotime($ceecIn)) {
        $errors[] = 'CEEC Out date cannot be before CEEC In date';
      }
    }

    // Validate Disp Date >= CEEC Out
    if ($ceecOut && $dispDate) {
      if (strtotime($dispDate) < strtotime($ceecOut)) {
        $errors[] = 'Disp Date cannot be before CEEC Out date';
      }
    }

    if (!empty($errors)) {
      return [
        'success' => false,
        'message' => '<ul style="text-align:left;"><li>' . implode('</li><li>', $errors) . '</li></ul>'
      ];
    }

    return ['success' => true];
  }

  /**
   * Prepare local data
   */
 /**
 * Prepare local data
 */
private function prepareLocalData($post)
{
  return [
    'client_id' => !empty($post['client_id']) ? $this->toInt($post['client_id']) : null,
    'location' => !empty($post['location']) ? $this->toInt($post['location']) : null,
    'mca_lt_reference' => !empty($post['mca_lt_reference']) ? $this->clean($post['mca_lt_reference']) : null,
    'lot_num' => !empty($post['lot_num']) ? $this->clean($post['lot_num']) : null,
    'horse' => !empty($post['horse']) ? $this->clean($post['horse']) : null,
    'trailer_1' => !empty($post['trailer_1']) ? $this->clean($post['trailer_1']) : null,
    'trailer_2' => !empty($post['trailer_2']) ? $this->clean($post['trailer_2']) : null,
    'transporter' => !empty($post['transporter']) ? $this->clean($post['transporter']) : null,
    'nbr_of_bags' => !empty($post['nbr_of_bags']) && is_numeric($post['nbr_of_bags']) ? (int)$post['nbr_of_bags'] : null,
    'weight' => !empty($post['weight']) && is_numeric($post['weight']) ? round((float)$post['weight'], 2) : null,
    'arrival_date' => !empty($post['arrival_date']) && $this->isValidDate($post['arrival_date']) ? date('Y-m-d', strtotime($post['arrival_date'])) : null,
    'loading_date' => !empty($post['loading_date']) && $this->isValidDate($post['loading_date']) ? date('Y-m-d', strtotime($post['loading_date'])) : null,
    'bp_details_received_date' => !empty($post['bp_details_received_date']) && $this->isValidDate($post['bp_details_received_date']) ? date('Y-m-d', strtotime($post['bp_details_received_date'])) : null,
    'pv_div_mines_date' => !empty($post['pv_div_mines_date']) && $this->isValidDate($post['pv_div_mines_date']) ? date('Y-m-d', strtotime($post['pv_div_mines_date'])) : null,
    'demande_attestation_date' => !empty($post['demande_attestation_date']) && $this->isValidDate($post['demande_attestation_date']) ? date('Y-m-d', strtotime($post['demande_attestation_date'])) : null,
    'ceec_in' => !empty($post['ceec_in']) && $this->isValidDate($post['ceec_in']) ? date('Y-m-d', strtotime($post['ceec_in'])) : null,
    'ceec_out' => !empty($post['ceec_out']) && $this->isValidDate($post['ceec_out']) ? date('Y-m-d', strtotime($post['ceec_out'])) : null,
    'cgea' => !empty($post['cgea']) ? $this->clean($post['cgea']) : null,  // CHANGED: Text field instead of date
    'gov_docs_complete_date' => !empty($post['gov_docs_complete_date']) && $this->isValidDate($post['gov_docs_complete_date']) ? date('Y-m-d', strtotime($post['gov_docs_complete_date'])) : null,
    'disp_date' => !empty($post['disp_date']) && $this->isValidDate($post['disp_date']) ? date('Y-m-d', strtotime($post['disp_date'])) : null,
    'end_of_formalities' => !empty($post['end_of_formalities']) && $this->isValidDate($post['end_of_formalities']) ? date('Y-m-d', strtotime($post['end_of_formalities'])) : null,
    'remarks' => !empty($post['remarks']) ? $this->clean($post['remarks']) : null
  ];
}

  private function clean($value)
  {
    $value = trim($value);
    $value = str_replace(chr(0), '', $value);
    $value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $value = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $value);
    
    return $value;
  }

  private function toInt($value)
  {
    if (!is_numeric($value)) {
      return null;
    }
    
    $int = (int)$value;
    return $int > 0 ? $int : null;
  }

  private function isValidDate($date)
  {
    if (empty($date)) {
      return false;
    }
    
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
  }
}