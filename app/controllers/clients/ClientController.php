<?php

class ClientController extends Controller
{
  // ✅ FIXED: Declare all properties (PHP 8.2+ requirement)
  private $db;
  private $baseDir = UPLOAD_PATH.'/clients/';
  private $maxFileSize = 5242880; // 5MB
  private $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];

  public function __construct()
  {
    $this->db = new Database();
    if (!is_dir($this->baseDir)) {
      mkdir($this->baseDir, 0777, true);
    }
  }

  public function index()
  {
    $industries = $this->db->selectData('industry_master_t', 'id, industry_name', [], 'industry_name ASC');
    $results = $this->db->selectData('clients_t', 'id, company_name', ['display' => 'Y'], 'company_name ASC');
    
    // Only get locations with ID 17 and 18
    $sql = "SELECT id, transit_point_name AS location_name 
            FROM transit_point_master_t 
            WHERE id IN (17, 18) 
            ORDER BY transit_point_name ASC";
    $locations = $this->db->customQuery($sql);
    
    $refferers = $this->db->selectData('refferer_master_t', 'id, refferer_name', [], 'refferer_name ASC');
    $phases = $this->db->selectData('phase_master_t', 'id, phase_name, phase_code', [], 'phase_code ASC');
    $users = $this->db->selectData('users_t', 'id, full_name', [], 'full_name ASC');
    $group_company = $this->db->selectData('group_company_master_t', 'id, group_company_name', [], 'group_company_name ASC');
    
    // Get done_by options
    $done_by_options = $this->db->selectData('done_by_t', 'id, done_by_name', ['display' => 'Y'], 'done_by_name ASC');
    
    $data = [
      'title' => 'Clients Management',
      'industries' => $industries,
      'results' => $results,
      'locations' => $locations,
      'refferers' => $refferers,
      'phases' => $phases,
      'users' => $users,
      'group_company' => $group_company,
      'done_by_options' => $done_by_options
    ];

    $this->viewWithLayout('clients/clients', $data);
  }

  public function crudData($action = 'insertion')
  {
    header('Content-Type: application/json');

    try {
      switch ($action) {
        case 'insertion':
          $this->insertClient();
          break;
        case 'update':
          $this->updateClient();
          break;
        case 'deletion':
          $this->deleteClient();
          break;
        case 'getClient':
          $this->getClient();
          break;
        case 'listing':
          $this->listClients();
          break;
        case 'checkShortName':
          $this->checkShortName();
          break;
        case 'exportClient':
          $this->exportClient();
          break;
        case 'exportAll':
          $this->exportAllClients();
          break;
        default:
          echo json_encode(['success' => false, 'message' => 'Invalid action']);
      }
    } catch (Exception $e) {
      error_log("Controller Error: " . $e->getMessage());
      echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    }
    exit;
  }

  private function insertClient()
  {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      echo json_encode(['success' => false, 'message' => 'Invalid request method']);
      return;
    }

    $validation = $this->validateClientData($_POST);
    if (!$validation['success']) {
      echo json_encode($validation);
      return;
    }

    $data = $this->prepareClientData($_POST);

    $existing = $this->db->selectData('clients_t', 'id', ['company_name' => $data['company_name']]);
    if (!empty($existing)) {
      echo json_encode(['success' => false, 'message' => 'A client with this company name already exists']);
      return;
    }

    $fileUploadResult = $this->handleFileUploads();
    if (!$fileUploadResult['success']) {
      echo json_encode(['success' => false, 'message' => implode(', ', $fileUploadResult['errors'])]);
      return;
    }

    $data = array_merge($data, $fileUploadResult['files']);
    $data['display'] = 'Y';
    $data['created_by'] = $_SESSION['user_id'] ?? 1;
    $data['updated_by'] = $_SESSION['user_id'] ?? 1;

    $insertId = $this->db->insertData('clients_t', $data);

    if ($insertId) {
      echo json_encode(['success' => true, 'message' => 'Client created successfully!', 'id' => $insertId]);
    } else {
      // ✅ FIXED: Cleanup uploaded files on database failure
      $this->cleanupFiles($fileUploadResult['files']);
      echo json_encode(['success' => false, 'message' => 'Failed to save client. Please try again.']);
    }
  }

  private function updateClient()
  {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      echo json_encode(['success' => false, 'message' => 'Invalid request method']);
      return;
    }

    $clientId = (int) ($_POST['client_id'] ?? 0);
    if ($clientId <= 0) {
      echo json_encode(['success' => false, 'message' => 'Invalid client ID']);
      return;
    }

    $existing = $this->db->selectData('clients_t', '*', ['id' => $clientId]);
    if (empty($existing)) {
      echo json_encode(['success' => false, 'message' => 'Client not found']);
      return;
    }

    $validation = $this->validateClientData($_POST, $clientId);
    if (!$validation['success']) {
      echo json_encode($validation);
      return;
    }

    $data = $this->prepareClientData($_POST);

    // ✅ FIXED: Handle file uploads FIRST and check for errors
    $fileUploadResult = $this->handleFileUploads(true);
    
    // If there are upload errors, stop and don't delete old files
    if (!$fileUploadResult['success'] && !empty($fileUploadResult['errors'])) {
      echo json_encode(['success' => false, 'message' => implode(', ', $fileUploadResult['errors'])]);
      return;
    }

    // ✅ FIXED: Only collect old files for deletion if NEW files were successfully uploaded
    $oldFilesToDelete = [];
    $hasNewFiles = false;
    
    foreach ($fileUploadResult['files'] as $key => $newFilePath) {
      // Check if a new file was actually uploaded for this field
      if (!empty($newFilePath)) {
        $hasNewFiles = true;
        $oldFile = $existing[0][$key] ?? '';
        
        // Only mark old file for deletion if it exists and is different from new file
        if (!empty($oldFile) && $oldFile !== $newFilePath) {
          $oldFilesToDelete[] = $oldFile;
        }
        
        // Update data with new file path
        $data[$key] = $newFilePath;
      }
      // If no new file uploaded, keep existing file (don't update the field)
    }

    $data['updated_by'] = $_SESSION['user_id'] ?? 1;
    $data['updated_at'] = date('Y-m-d H:i:s');

    // ✅ Update database
    $success = $this->db->updateData('clients_t', $data, ['id' => $clientId]);

    if ($success) {
      // ✅ FIXED: Only delete old files AFTER successful database update
      if (!empty($oldFilesToDelete)) {
        $this->cleanupFiles($oldFilesToDelete);
        error_log("Update successful - Deleted " . count($oldFilesToDelete) . " old file(s)");
      }
      
      $message = 'Client updated successfully!';
      if ($hasNewFiles) {
        $message .= ' Files uploaded successfully.';
      }
      
      echo json_encode(['success' => true, 'message' => $message]);
    } else {
      // ✅ FIXED: Delete newly uploaded files if database update fails (rollback)
      $newFiles = array_filter($fileUploadResult['files']);
      if (!empty($newFiles)) {
        $this->cleanupFiles($newFiles);
        error_log("Update failed - Rolled back " . count($newFiles) . " new file(s)");
      }
      echo json_encode(['success' => false, 'message' => 'Failed to update client. Please try again.']);
    }
  }

  private function deleteClient()
  {
    $clientId = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);

    if ($clientId <= 0) {
      echo json_encode(['success' => false, 'message' => 'Invalid client ID']);
      return;
    }

    // ✅ Get client data BEFORE deleting to access file paths
    $client = $this->db->selectData('clients_t', '*', ['id' => $clientId]);
    if (empty($client)) {
      echo json_encode(['success' => false, 'message' => 'Client not found']);
      return;
    }

    // ✅ Soft delete - update display flag
    $success = $this->db->updateData('clients_t', ['display' => 'N'], ['id' => $clientId]);

    if ($success) {
      // ✅ FIXED: Delete all associated files when client is deleted
      $fileFields = ['id_nat_file', 'rccm_file', 'import_export_file', 'attestation_file'];
      $filesToDelete = [];
      
      foreach ($fileFields as $field) {
        if (!empty($client[0][$field])) {
          $filesToDelete[] = $client[0][$field];
        }
      }
      
      // Delete physical files
      if (!empty($filesToDelete)) {
        $this->cleanupFiles($filesToDelete);
        
        // ✅ OPTIONAL: Clear file references in database after deletion
        $updateData = [
          'id_nat_file' => null,
          'rccm_file' => null,
          'import_export_file' => null,
          'attestation_file' => null
        ];
        $this->db->updateData('clients_t', $updateData, ['id' => $clientId]);
      }
      
      echo json_encode(['success' => true, 'message' => 'Client and associated files deleted successfully']);
    } else {
      echo json_encode(['success' => false, 'message' => 'Failed to delete client']);
    }
  }

  private function getClient()
  {
    $clientId = (int) ($_GET['id'] ?? 0);

    if ($clientId <= 0) {
      echo json_encode(['success' => false, 'message' => 'Invalid client ID']);
      return;
    }

    $client = $this->db->selectData('clients_t', '*', ['id' => $clientId]);

    if (!empty($client)) {
      echo json_encode(['success' => true, 'data' => $client[0]]);
    } else {
      echo json_encode(['success' => false, 'message' => 'Client not found']);
    }
  }

  private function listClients()
  {
    $sql = "SELECT 
              c.*,
              i.industry_name,
              loc.transit_point_name as location_name
            FROM clients_t c
            LEFT JOIN industry_master_t i ON c.industry_type_id = i.id
            LEFT JOIN transit_point_master_t loc ON c.office_location_id = loc.id
            WHERE c.display = 'Y'
            ORDER BY c.id DESC";

    $clients = $this->db->customQuery($sql);
    echo json_encode(['success' => true, 'data' => $clients ?: []]);
  }

  private function checkShortName()
  {
    $shortName = trim($_GET['short_name'] ?? '');
    $clientId = (int) ($_GET['client_id'] ?? 0);

    if ($shortName === '') {
      echo json_encode(['success' => false, 'message' => 'Short name required']);
      return;
    }

    $sql = "SELECT id FROM clients_t WHERE short_name = :short_name";
    $params = [':short_name' => $shortName];

    if ($clientId > 0) {
      $sql .= " AND id != :client_id";
      $params[':client_id'] = $clientId;
    }
    
    $exists = $this->db->customQuery($sql, $params);
    echo json_encode(['success' => true, 'exists' => !empty($exists)]);
  }

  /**
   * Export single client to Excel - ALL BUSINESS DETAILS (Excluding: id, created_at, updated_at, created_by, updated_by, display)
   */
  private function exportClient()
  {
    $clientId = (int) ($_GET['id'] ?? 0);

    if ($clientId <= 0) {
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'message' => 'Invalid client ID']);
      return;
    }

    try {
      $vendorPath = __DIR__ . '/../../../vendor/autoload.php';
      
      if (!file_exists($vendorPath)) {
        throw new Exception('PhpSpreadsheet not found. Please run: composer require phpoffice/phpspreadsheet');
      }
      
      require_once $vendorPath;

      $sql = "SELECT 
                c.*,
                i.industry_name,
                loc.transit_point_name as location_name,
                r.refferer_name,
                p.phase_name,
                gc.group_company_name,
                u1.full_name as verified_by_name,
                u2.full_name as approved_by_name,
                db1.done_by_name as liquidation_paid_by_name,
                db2.done_by_name as license_cleared_by_name,
                db3.done_by_name as license_submit_to_bank_name
              FROM clients_t c
              LEFT JOIN industry_master_t i ON c.industry_type_id = i.id
              LEFT JOIN transit_point_master_t loc ON c.office_location_id = loc.id
              LEFT JOIN refferer_master_t r ON c.referred_by_id = r.id
              LEFT JOIN phase_master_t p ON c.phase_id = p.id
              LEFT JOIN group_company_master_t gc ON c.group_company_id = gc.id
              LEFT JOIN users_t u1 ON c.verified_by_id = u1.id
              LEFT JOIN users_t u2 ON c.approved_by_id = u2.id
              LEFT JOIN done_by_t db1 ON c.liquidation_paid_by = db1.id
              LEFT JOIN done_by_t db2 ON c.license_cleared_by = db2.id
              LEFT JOIN done_by_t db3 ON c.license_submit_to_bank = db3.id
              WHERE c.id = :id AND c.display = 'Y'";

      $result = $this->db->customQuery($sql, [':id' => $clientId]);
      
      if (empty($result)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Client not found or inactive']);
        return;
      }

      $data = $result[0];
      
      $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->setTitle('Client Details');

      // ✅ COMPREHENSIVE HEADERS - ALL BUSINESS DETAILS (NO TECHNICAL FIELDS)
      $headers = [
        'Company Name',
        'Client Code', 
        'Client Type', 
        'Group Company', 
        'Industry', 
        'Location',
        'Address',
        'Contact Person',
        'Email',
        'Secondary Email',
        'Phone',
        'Secondary Phone',
        'Referred By',
        'Phase',
        'Phase Start Date',
        'Phase End Date',
        'ID/NAT Number',
        'ID/NAT File Path',
        'RCCM Number',
        'RCCM File Path',
        'Import/Export Number',
        'Import/Export Validity',
        'Import/Export File Path',
        'Attestation Number',
        'Attestation Validity',
        'Attestation File Path',
        'NIF Number',
        'Payment Term',
        'Credit Term (days)',
        'Liquidation Paid By',
        'License Cleared By',
        'License Submit To Bank',
        'Contract Start Date',
        'Contract Validity',
        'Payment Contact Email',
        'Payment Contact Phone',
        'Approval Code',
        'Invoice Template',
        'Verified By',
        'Verification Date',
        'Approved By',
        'Approved Date',
        'Remarks'
      ];
      
      // ✅ COMPREHENSIVE VALUES - ALL BUSINESS DETAILS
      $values = [
        $data['company_name'] ?? '',
        $data['short_name'] ?? '',
        $this->formatClientType($data['client_type'] ?? ''),
        $data['group_company_name'] ?? '',
        $data['industry_name'] ?? '',
        $data['location_name'] ?? '',
        $data['address'] ?? '',
        $data['contact_person'] ?? '',
        $data['email'] ?? '',
        $data['email_secondary'] ?? '',
        $data['phone'] ?? '',
        $data['phone_secondary'] ?? '',
        $data['refferer_name'] ?? '',
        $data['phase_name'] ?? '',
        $data['phase_start_date'] ?? '',
        $data['phase_end_date'] ?? '',
        $data['id_nat_number'] ?? '',
        $data['id_nat_file'] ?? '',
        $data['rccm_number'] ?? '',
        $data['rccm_file'] ?? '',
        $data['import_export_number'] ?? '',
        $data['import_export_validity'] ?? '',
        $data['import_export_file'] ?? '',
        $data['attestation_number'] ?? '',
        $data['attestation_validity'] ?? '',
        $data['attestation_file'] ?? '',
        $data['nif_number'] ?? '',
        $data['payment_term'] ?? '',
        $data['credit_term'] ?? '',
        $data['liquidation_paid_by_name'] ?? '',
        $data['license_cleared_by_name'] ?? '',
        $data['license_submit_to_bank_name'] ?? '',
        $data['contract_start_date'] ?? '',
        $data['contract_validity'] ?? '',
        $data['payment_contact_email'] ?? '',
        $data['payment_contact_phone'] ?? '',
        $data['approval_code'] ?? '',
        $data['invoice_template'] ?? '',
        $data['verified_by_name'] ?? '',
        $data['verified_by_date'] ?? '',
        $data['approved_by_name'] ?? '',
        $data['approved_by_date'] ?? '',
        $data['remarks'] ?? ''
      ];

      // Populate sheet with headers and values
      $excelData = [$headers, $values];
      $sheet->fromArray($excelData, null, 'A1');

      // Style header row
      $headerStyle = [
        'font' => [
          'bold' => true,
          'color' => ['rgb' => 'FFFFFF'],
          'size' => 11
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
      
      $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
      $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray($headerStyle);

      // Style value row
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

      // Set column widths
      foreach (range(1, count($headers)) as $colIndex) {
        $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
        $sheet->getColumnDimension($column)->setWidth(20);
      }

      // Set row heights
      $sheet->getRowDimension(1)->setRowHeight(25);
      $sheet->getRowDimension(2)->setRowHeight(20);

      $filename = 'Client_' . ($data['short_name'] ?? 'Export') .'.xlsx';
      // $filepath = __DIR__ . '/../../../uploads/' . $filename;
      $filepath = UPLOAD_PATH .'/'. $filename;

      $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
      $writer->save($filepath);

      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment;filename="' . $filename . '"');
      header('Content-Length: ' . filesize($filepath));
      header('Cache-Control: max-age=0');

      readfile($filepath);
      
      unlink($filepath);
      $spreadsheet->disconnectWorksheets();
      unset($spreadsheet);
      exit;
      
    } catch (Exception $e) {
      error_log("Export Error: " . $e->getMessage());
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'message' => 'Export failed: ' . $e->getMessage()]);
      exit;
    }
  }

  /**
   * Export ALL clients to Excel - ALL BUSINESS DETAILS (Excluding: id, created_at, updated_at, created_by, updated_by, display)
   */
  private function exportAllClients()
  {
    try {
      $vendorPath = __DIR__ . '/../../../vendor/autoload.php';
      
      if (!file_exists($vendorPath)) {
        throw new Exception('PhpSpreadsheet not found. Please run: composer require phpoffice/phpspreadsheet');
      }
      
      require_once $vendorPath;

      $sql = "SELECT 
                c.*,
                i.industry_name,
                loc.transit_point_name as location_name,
                r.refferer_name,
                p.phase_name,
                gc.group_company_name,
                u1.full_name as verified_by_name,
                u2.full_name as approved_by_name,
                db1.done_by_name as liquidation_paid_by_name,
                db2.done_by_name as license_cleared_by_name,
                db3.done_by_name as license_submit_to_bank_name
              FROM clients_t c
              LEFT JOIN industry_master_t i ON c.industry_type_id = i.id
              LEFT JOIN transit_point_master_t loc ON c.office_location_id = loc.id
              LEFT JOIN refferer_master_t r ON c.referred_by_id = r.id
              LEFT JOIN phase_master_t p ON c.phase_id = p.id
              LEFT JOIN group_company_master_t gc ON c.group_company_id = gc.id
              LEFT JOIN users_t u1 ON c.verified_by_id = u1.id
              LEFT JOIN users_t u2 ON c.approved_by_id = u2.id
              LEFT JOIN done_by_t db1 ON c.liquidation_paid_by = db1.id
              LEFT JOIN done_by_t db2 ON c.license_cleared_by = db2.id
              LEFT JOIN done_by_t db3 ON c.license_submit_to_bank = db3.id
              WHERE c.display = 'Y'
              ORDER BY c.id DESC";

      $clients = $this->db->customQuery($sql);

      if (empty($clients)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No clients found to export']);
        return;
      }

      $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->setTitle('All Clients');

      // ✅ COMPREHENSIVE HEADERS - ALL BUSINESS DETAILS (NO TECHNICAL FIELDS)
      $headers = [
        'Company Name',
        'Client Code', 
        'Client Type', 
        'Group Company', 
        'Industry', 
        'Location',
        'Address',
        'Contact Person',
        'Email',
        'Secondary Email',
        'Phone',
        'Secondary Phone',
        'Referred By',
        'Phase',
        'Phase Start Date',
        'Phase End Date',
        'ID/NAT Number',
        'ID/NAT File Path',
        'RCCM Number',
        'RCCM File Path',
        'Import/Export Number',
        'Import/Export Validity',
        'Import/Export File Path',
        'Attestation Number',
        'Attestation Validity',
        'Attestation File Path',
        'NIF Number',
        'Payment Term',
        'Credit Term (days)',
        'Liquidation Paid By',
        'License Cleared By',
        'License Submit To Bank',
        'Contract Start Date',
        'Contract Validity',
        'Payment Contact Email',
        'Payment Contact Phone',
        'Approval Code',
        'Invoice Template',
        'Verified By',
        'Verification Date',
        'Approved By',
        'Approved Date',
        'Remarks'
      ];

      $sheet->fromArray([$headers], null, 'A1');

      // ✅ Add data rows with ALL BUSINESS DETAILS
      $rowIndex = 2;
      foreach ($clients as $client) {
        $rowData = [
          $client['company_name'] ?? '',
          $client['short_name'] ?? '',
          $this->formatClientType($client['client_type'] ?? ''),
          $client['group_company_name'] ?? '',
          $client['industry_name'] ?? '',
          $client['location_name'] ?? '',
          $client['address'] ?? '',
          $client['contact_person'] ?? '',
          $client['email'] ?? '',
          $client['email_secondary'] ?? '',
          $client['phone'] ?? '',
          $client['phone_secondary'] ?? '',
          $client['refferer_name'] ?? '',
          $client['phase_name'] ?? '',
          $client['phase_start_date'] ?? '',
          $client['phase_end_date'] ?? '',
          $client['id_nat_number'] ?? '',
          $client['id_nat_file'] ?? '',
          $client['rccm_number'] ?? '',
          $client['rccm_file'] ?? '',
          $client['import_export_number'] ?? '',
          $client['import_export_validity'] ?? '',
          $client['import_export_file'] ?? '',
          $client['attestation_number'] ?? '',
          $client['attestation_validity'] ?? '',
          $client['attestation_file'] ?? '',
          $client['nif_number'] ?? '',
          $client['payment_term'] ?? '',
          $client['credit_term'] ?? '',
          $client['liquidation_paid_by_name'] ?? '',
          $client['license_cleared_by_name'] ?? '',
          $client['license_submit_to_bank_name'] ?? '',
          $client['contract_start_date'] ?? '',
          $client['contract_validity'] ?? '',
          $client['payment_contact_email'] ?? '',
          $client['payment_contact_phone'] ?? '',
          $client['approval_code'] ?? '',
          $client['invoice_template'] ?? '',
          $client['verified_by_name'] ?? '',
          $client['verified_by_date'] ?? '',
          $client['approved_by_name'] ?? '',
          $client['approved_by_date'] ?? '',
          $client['remarks'] ?? ''
        ];
        
        $sheet->fromArray([$rowData], null, 'A' . $rowIndex);
        $rowIndex++;
      }

      // Style header row
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

      // Style data rows
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

      // Set column widths
      foreach (range(1, count($headers)) as $colIndex) {
        $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
        $sheet->getColumnDimension($column)->setWidth(20);
      }

      $sheet->getRowDimension(1)->setRowHeight(25);
      $sheet->setAutoFilter('A1:' . $lastColumn . '1');

      $filename = 'All_Clients'. '.xlsx';
      $filepath = UPLOAD_PATH.'/'.$filename ;

      $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
      $writer->save($filepath);

      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment;filename="' . $filename . '"');
      header('Content-Length: ' . filesize($filepath));
      header('Cache-Control: max-age=0');

      readfile($filepath);
      
      unlink($filepath);
      $spreadsheet->disconnectWorksheets();
      unset($spreadsheet);
      exit;
      
    } catch (Exception $e) {
      error_log("Export All Error: " . $e->getMessage());
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'message' => 'Export failed: ' . $e->getMessage()]);
      exit;
    }
  }

  private function validateClientData($data, $clientId = null)
  {
    $errors = [];

    if (empty(trim($data['company_name'] ?? ''))) {
      $errors[] = 'Company name is required';
    } elseif (strlen($data['company_name']) < 2 || strlen($data['company_name']) > 200) {
      $errors[] = 'Company name must be between 2 and 200 characters';
    }

    if (empty($data['short_name']) || strlen($data['short_name']) !== 3) {
      $errors[] = 'Client code must be exactly 3 characters';
    }

    if (empty($data['client_type']) || !in_array($data['client_type'], ['I','E','L','IE','EI','IL','LI','EL','LE','IEL','ILE','EIL','ELI','LIE','LEI'])) {
      $errors[] = 'Valid client type is required';
    }

    if (empty($data['payment_term'])) {
      $errors[] = 'Payment term is required';
    }

    if (empty($data['liquidation_paid_by']) || !in_array($data['liquidation_paid_by'], ['1', '2', 1, 2])) {
      $errors[] = 'Liquidation paid by is required';
    }

    if (empty($data['license_submit_to_bank']) || !in_array($data['license_submit_to_bank'], ['1', '2', 1, 2])) {
      $errors[] = 'License submit to bank is required';
    }

    $emailFields = ['email', 'email_secondary', 'payment_contact_email'];
    foreach ($emailFields as $field) {
      if (!empty($data[$field]) && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
        $errors[] = ucwords(str_replace('_', ' ', $field)) . ' is invalid';
      }
    }

    $phoneFields = ['phone', 'phone_secondary', 'payment_contact_phone'];
    foreach ($phoneFields as $field) {
      if (!empty($data[$field]) && !preg_match('/^[0-9+\s()\-]{7,20}$/', $data[$field])) {
        $errors[] = ucwords(str_replace('_', ' ', $field)) . ' is invalid';
      }
    }

    if (!empty($data['credit_term'])) {
      $creditTerm = (int) $data['credit_term'];
      if ($creditTerm < 0 || $creditTerm > 365) {
        $errors[] = 'Credit term must be between 0 and 365 days';
      }
    }

    if (!empty($data['company_name'])) {
      $where = ['company_name' => trim($data['company_name'])];
      if ($clientId) {
        $existing = $this->db->selectData('clients_t', 'id', $where);
        if (!empty($existing) && $existing[0]['id'] != $clientId) {
          $errors[] = 'A client with this company name already exists';
        }
      }
    }

    if (!empty($errors)) {
      return ['success' => false, 'message' => '<ul><li>' . implode('</li><li>', $errors) . '</li></ul>'];
    }

    return ['success' => true];
  }

  private function prepareClientData($post)
  {
    return [
      'company_name' => $this->clean($post['company_name'] ?? ''),
      'short_name' => $this->clean($post['short_name'] ?? ''),
      'client_type' => $this->clean($post['client_type'] ?? 'I'),
      'group_company_id' => $this->toInt($post['group_company_id'] ?? null),
      'industry_type_id' => $this->toInt($post['industry_type_id'] ?? null),
      'referred_by_id' => $this->toInt($post['referred_by_id'] ?? null),
      'office_location_id' => $this->toInt($post['office_location_id'] ?? null),
      'address' => $this->clean($post['address'] ?? ''),
      'phase_id' => $this->toInt($post['phase_id'] ?? null),
      'phase_start_date' => !empty($post['phase_start_date']) ? date('Y-m-d', strtotime($post['phase_start_date'])) : null,
      'phase_end_date' => !empty($post['phase_end_date']) ? date('Y-m-d', strtotime($post['phase_end_date'])) : null,
      'contact_person' => $this->clean($post['contact_person'] ?? ''),
      'email' => $this->clean($post['email'] ?? ''),
      'email_secondary' => $this->clean($post['email_secondary'] ?? ''),
      'phone' => $this->clean($post['phone'] ?? ''),
      'phone_secondary' => $this->clean($post['phone_secondary'] ?? ''),
      'id_nat_number' => $this->clean($post['id_nat_number'] ?? ''),
      'rccm_number' => $this->clean($post['rccm_number'] ?? ''),
      'import_export_number' => $this->clean($post['import_export_number'] ?? ''),
      'import_export_validity' => !empty($post['import_export_validity']) ? date('Y-m-d', strtotime($post['import_export_validity'])) : null,
      'attestation_number' => $this->clean($post['attestation_number'] ?? ''),
      'attestation_validity' => !empty($post['attestation_validity']) ? date('Y-m-d', strtotime($post['attestation_validity'])) : null,
      'nif_number' => $this->clean($post['nif_number'] ?? ''),
      'payment_contact_email' => $this->clean($post['payment_contact_email'] ?? ''),
      'payment_contact_phone' => $this->clean($post['payment_contact_phone'] ?? ''),
      'payment_term' => $this->clean($post['payment_term'] ?? ''),
      'credit_term' => $this->toInt($post['credit_term'] ?? null),
      'liquidation_paid_by' => $this->toInt($post['liquidation_paid_by'] ?? null),
      'license_cleared_by' => $this->toInt($post['license_cleared_by'] ?? null),
      'license_submit_to_bank' => $this->toInt($post['license_submit_to_bank'] ?? null),
      'contract_start_date' => !empty($post['contract_start_date']) ? date('Y-m-d', strtotime($post['contract_start_date'])) : null,
      'contract_validity' => !empty($post['contract_validity']) ? date('Y-m-d', strtotime($post['contract_validity'])) : null,
      'approval_code' => $this->clean($post['approval_code'] ?? ''),
      'invoice_template' => $this->clean($post['invoice_template'] ?? ''),
      'remarks' => $this->clean($post['remarks'] ?? ''),
      'verified_by_id' => $this->toInt($post['verified_by_id'] ?? null),
      'verified_by_date' => !empty($post['verified_by_date']) ? date('Y-m-d', strtotime($post['verified_by_date'])) : null,
      'approved_by_id' => $this->toInt($post['approved_by_id'] ?? null),
      'approved_by_date' => !empty($post['approved_by_date']) ? date('Y-m-d', strtotime($post['approved_by_date'])) : null
    ];
  }

  /**
   * ✅ FIXED: Handle file uploads with proper relative path storage
   * @param bool $isUpdate Whether this is an update operation
   * @return array Result with success status, uploaded files, and errors
   */
  private function handleFileUploads($isUpdate = false)
  {
    $fileFields = ['id_nat_file', 'rccm_file', 'import_export_file', 'attestation_file'];
    $uploadedFiles = [];
    $errors = [];
    
    $client_short_name = preg_replace('/[^A-Za-z0-9_-]/', '', $_POST['short_name'] ?? 'CLIENT');

    foreach ($fileFields as $field) {
      // Check if a file was uploaded for this field
      if (!empty($_FILES[$field]['name'])) {
        $file = $_FILES[$field];
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
          $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
          ];
          $errorMsg = $uploadErrors[$file['error']] ?? 'Unknown upload error';
          $errors[] = ucwords(str_replace('_', ' ', $field)) . ': ' . $errorMsg;
          error_log("Upload error for {$field}: " . $errorMsg);
          continue;
        }

        // Validate file size
        if ($file['size'] > $this->maxFileSize) {
          $errors[] = ucwords(str_replace('_', ' ', $field)) . ' must be less than 5MB';
          error_log("File size error for {$field}: {$file['size']} bytes (max: {$this->maxFileSize})");
          continue;
        }

        // Validate file extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $this->allowedExtensions)) {
          $errors[] = ucwords(str_replace('_', ' ', $field)) . ' must be PDF, JPG, or PNG';
          error_log("Extension error for {$field}: {$ext}");
          continue;
        }

        // Validate MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedMimes = ['application/pdf', 'image/jpeg', 'image/png'];
        if (!in_array($mimeType, $allowedMimes)) {
          $errors[] = ucwords(str_replace('_', ' ', $field)) . ' has invalid file type';
          error_log("MIME type error for {$field}: {$mimeType}");
          continue;
        }

        // Determine subfolder based on field
        switch ($field) {
          case 'id_nat_file': $subFolder = 'NAT'; break;
          case 'rccm_file': $subFolder = 'RCCM'; break;
          case 'import_export_file': $subFolder = 'IMPORT_AND_EXPORT'; break;
          case 'attestation_file': $subFolder = 'ATTESTATION'; break;
          default: $subFolder = 'OTHERS';
        }

        // Create directory if it doesn't exist
        $targetDir = $this->baseDir . $subFolder . '/';
        if (!is_dir($targetDir)) {
          if (!mkdir($targetDir, 0777, true)) {
            $errors[] = 'Failed to create directory for ' . ucwords(str_replace('_', ' ', $field));
            error_log("Failed to create directory: {$targetDir}");
            continue;
          }
        }

        // Create unique filename with field prefix
        $fieldPrefix = str_replace('_file', '', $field);
        $fileName = $client_short_name . '_' . $fieldPrefix . '.' . $ext;
        $targetPath = $targetDir . $fileName;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
          // ✅ CRITICAL: Store RELATIVE path (not full path) for database
          // This allows cleanupFiles() to work correctly
          $uploadedFiles[$field] = $subFolder . '/' . $fileName;
          error_log("✅ Successfully uploaded {$field}: {$targetPath}");
        } else {
          $errors[] = 'Failed to upload ' . ucwords(str_replace('_', ' ', $field));
          error_log("❌ Failed to move uploaded file for {$field} from {$file['tmp_name']} to {$targetPath}");
        }
      } else {
        // No file uploaded for this field
        $uploadedFiles[$field] = null;
      }
    }

    return [
      'success' => empty($errors), 
      'files' => $uploadedFiles, 
      'errors' => $errors
    ];
  }

  /**
   * ✅ FIXED: Cleanup files - properly handles relative paths from database
   * This function now correctly constructs full file paths and deletes them
   * 
   * @param array $files Array of file paths relative to /clients/ directory
   *                     Example: ['NAT/ACME_id_nat.pdf', 'RCCM/ACME_rccm.pdf']
   */
  private function cleanupFiles($files)
  {
    $deletedCount = 0;
    $failedCount = 0;
    
    foreach ($files as $fileName) {
      if (!empty($fileName)) {
        // ✅ FIXED: Construct full path by prepending base directory
        // Database stores: "NAT/ACME_id_nat.pdf"
        // This creates: "/path/to/uploads/clients/NAT/ACME_id_nat.pdf"
        $filePath = $this->baseDir . $fileName;
        
        // Check if file exists before attempting deletion
        if (file_exists($filePath)) {
          // Attempt to delete the file
          if (@unlink($filePath)) {
            $deletedCount++;
            error_log("✅ Successfully deleted file: {$filePath}");
          } else {
            $failedCount++;
            error_log("❌ Failed to delete file: {$filePath}");
          }
        } else {
          error_log("⚠️ File not found for deletion: {$filePath}");
        }
      }
    }
    
    // Log summary
    if ($deletedCount > 0 || $failedCount > 0) {
      error_log("File cleanup summary - Deleted: {$deletedCount}, Failed: {$failedCount}");
    }
  }

  /**
   * ✅ BONUS: Permanent deletion method (for admin use)
   * This permanently deletes a client and all associated files
   */
  public function permanentDeleteClient()
  {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      echo json_encode(['success' => false, 'message' => 'Invalid request method']);
      return;
    }

    $clientId = (int) ($_POST['id'] ?? 0);

    if ($clientId <= 0) {
      echo json_encode(['success' => false, 'message' => 'Invalid client ID']);
      return;
    }

    // Get client data before deletion
    $client = $this->db->selectData('clients_t', '*', ['id' => $clientId]);
    if (empty($client)) {
      echo json_encode(['success' => false, 'message' => 'Client not found']);
      return;
    }

    // Collect all file paths
    $fileFields = ['id_nat_file', 'rccm_file', 'import_export_file', 'attestation_file'];
    $filesToDelete = [];
    
    foreach ($fileFields as $field) {
      if (!empty($client[0][$field])) {
        $filesToDelete[] = $client[0][$field];
      }
    }

    // Delete physical files first
    if (!empty($filesToDelete)) {
      $this->cleanupFiles($filesToDelete);
    }

    // Delete database record permanently
    $sql = "DELETE FROM clients_t WHERE id = :id";
    $success = $this->db->customQuery($sql, [':id' => $clientId]);

    if ($success !== false) {
      echo json_encode([
        'success' => true, 
        'message' => 'Client and all files permanently deleted',
        'files_deleted' => count($filesToDelete)
      ]);
    } else {
      echo json_encode(['success' => false, 'message' => 'Failed to delete client from database']);
    }
  }

  private function clean($value)
  {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
  }

  private function toInt($value)
  {
    if ($value === '' || $value === null) {
      return null;
    }
    
    $intValue = (int) $value;
    
    if ($intValue === 0 && $value !== '0' && $value !== 0) {
      return null;
    }
    
    return $intValue;
  }

  private function formatClientType($type)
  {
    if (empty($type)) return 'N/A';
    $types = [];
    if (strpos($type, 'I') !== false) $types[] = 'Import';
    if (strpos($type, 'E') !== false) $types[] = 'Export';
    if (strpos($type, 'L') !== false) $types[] = 'Local';
    return implode(', ', $types);
  }
  public function viewFile($field, $clientId)
  {
      // 🔐 Allow only specific file fields
      $allowedFields = [
          'id_nat_file',
          'rccm_file',
          'import_export_file',
          'attestation_file'
      ];

      if (!in_array($field, $allowedFields)) {
          http_response_code(404);
          exit;
      }

      // 🔐 Optional login check (recommended)
      if (!isset($_SESSION['user_id'])) {
          http_response_code(404);
          exit;
      }

      $clientId = (int)$clientId;
      if ($clientId <= 0) {
          http_response_code(404);
          exit;
      }

      // Fetch client
      $client = $this->db->selectData('clients_t', '*', ['id' => $clientId]);
      if (empty($client) || empty($client[0][$field])) {
          http_response_code(404);
          exit;
      }

      // Build FULL FILE PATH
      // DB stores: NAT/INF_id_nat.pdf
      $filePath = $this->baseDir . $client[0][$field];
      if (!file_exists($filePath)) {
          http_response_code(404);
          exit;
      }

      // 🔐 Stream PDF (URL MASKED)
      header('Content-Type: application/pdf');
      header('Content-Disposition: inline; filename="document.pdf"');
      header('Content-Length: ' . filesize($filePath));
      header('Cache-Control: no-store, no-cache, must-revalidate');
      header('Pragma: no-cache');

      readfile($filePath);
      exit;
  }

}