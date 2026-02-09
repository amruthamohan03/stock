<?php
class BankExchangeRateController extends Controller
{
    /**
     * Display the main exchange rate management page
     */
    public function index()
    {
        $db = new Database();
        
        // Get banks where for_exchange = 'Y'
        $banks = $db->selectData('banklist_master_t', '*', ['for_exchange' => 'Y', 'display' => 'Y']);
        
        // Get currencies from currency_master_t
        $currencies = $db->selectData('currency_master_t', 'id, currency_name, currency_short_name', ['display' => 'Y']);
        
        // Get today's date as default
        $selectedDate = $_GET['date'] ?? date('Y-m-d');
        
        // Find CDF currency ID or default to first currency
        $currencyId = 1; // Default
        if (!empty($currencies)) {
            foreach ($currencies as $curr) {
                if ($curr['currency_short_name'] == 'CDF') {
                    $currencyId = $curr['id'];
                    break;
                }
            }
        }
        
        // Override with GET parameter if provided
        $currencyId = $_GET['currency_id'] ?? $currencyId;
        $bccRate = $_GET['bcc_rate'] ?? '';
        
        // Get exchange rates for selected date
        $exchangeRates = [];
        if (!empty($banks)) {
            $query = "
                SELECT 
                    er.*, 
                    b.bank_name 
                FROM bank_exchange_rate_t er 
                JOIN banklist_master_t b ON er.bank_id = b.id 
                WHERE er.exchange_date = ? 
                    AND er.currency_id = ?
                    AND b.for_exchange = 'Y' 
                    AND b.display = 'Y'
                ORDER BY b.bank_name
            ";
            $result = $db->customQuery($query, [$selectedDate, $currencyId]);
            
            if ($result) {
                foreach ($result as $row) {
                    $exchangeRates[$row['bank_id']] = $row;
                    // Get BCC rate from first result if exists
                    if (empty($bccRate) && !empty($row['bcc_rate'])) {
                        $bccRate = $row['bcc_rate'];
                    }
                }
            }
        }
        
        $data = [
            'title' => 'Bank Exchange Rates',
            'banks' => $banks,
            'currencies' => $currencies,
            'exchangeRates' => $exchangeRates,
            'selectedDate' => $selectedDate,
            'bccRate' => $bccRate,
            'currencyId' => $currencyId
        ];
        
        $this->viewWithLayout('masters/bankexchangerate', $data);
    }
    
    /**
     * CREATE/UPDATE - Save exchange rates
     * Creates new records or updates existing ones
     */
    public function saveRates()
    {
        header('Content-Type: application/json');
        $db = new Database();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }
        
        $exchange_date = $_POST['exchange_date'] ?? '';
        $bcc_rate = $_POST['bcc_rate'] ?? '';
        $rates = $_POST['rates'] ?? [];
        $currency_id = (int)($_POST['currency_id'] ?? 1);
        
        // Validation
        if (empty($exchange_date)) {
            echo json_encode(['success' => false, 'message' => 'Date is required']);
            exit;
        }
        
        // Validate date format
        $dateObj = DateTime::createFromFormat('Y-m-d', $exchange_date);
        if (!$dateObj || $dateObj->format('Y-m-d') !== $exchange_date) {
            echo json_encode(['success' => false, 'message' => 'Invalid date format']);
            exit;
        }
        
        // Check if date is not in future
        if (strtotime($exchange_date) > strtotime(date('Y-m-d'))) {
            echo json_encode(['success' => false, 'message' => 'Cannot set rates for future dates']);
            exit;
        }
        
        if (empty($currency_id)) {
            echo json_encode(['success' => false, 'message' => 'Currency is required']);
            exit;
        }
        
        if (empty($bcc_rate) || (float)$bcc_rate <= 0) {
            echo json_encode(['success' => false, 'message' => 'BCC Rate is required and must be greater than 0']);
            exit;
        }
        
        if (empty($rates)) {
            echo json_encode(['success' => false, 'message' => 'At least one bank rate is required']);
            exit;
        }
        
        $success = true;
        $message = '';
        $inserted = 0;
        $updated = 0;
        
        // Start transaction
        $db->beginTransaction();
        
        try {
            foreach ($rates as $bank_id => $rate) {
                $bank_id = (int)$bank_id;
                
                // Skip if rate is empty or invalid
                if (empty($rate['bank_rate']) || (float)$rate['bank_rate'] <= 0) {
                    continue;
                }
                
                // Verify bank exists and is active
                $bankCheck = $db->selectData('banklist_master_t', 'id', [
                    'id' => $bank_id,
                    'for_exchange' => 'Y',
                    'display' => 'Y'
                ]);
                
                if (empty($bankCheck)) {
                    continue; // Skip invalid banks
                }
                
                // Check if rate exists
                $checkQuery = "
                    SELECT id 
                    FROM bank_exchange_rate_t 
                    WHERE bank_id = ? 
                        AND exchange_date = ? 
                        AND currency_id = ?
                ";
                $existing = $db->customQuery($checkQuery, [$bank_id, $exchange_date, $currency_id]);
                
                $data = [
                    'bcc_rate' => (float)$bcc_rate,
                    'bank_rate' => (float)$rate['bank_rate'],
                    'updated_by' => $_SESSION['user_id'] ?? 1,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                if (!empty($existing)) {
                    // UPDATE existing record
                    $update = $db->updateData('bank_exchange_rate_t', $data, ['id' => $existing[0]['id']]);
                    if ($update) $updated++;
                } else {
                    // CREATE new record
                    $data['bank_id'] = $bank_id;
                    $data['exchange_date'] = $exchange_date;
                    $data['currency_id'] = $currency_id;
                    $data['created_by'] = $_SESSION['user_id'] ?? 1;
                    $data['created_at'] = date('Y-m-d H:i:s');
                    $insert = $db->insertData('bank_exchange_rate_t', $data);
                    if ($insert) $inserted++;
                }
            }
            
            $db->commit();
            
            if ($inserted > 0 || $updated > 0) {
                $message = "Exchange rates saved successfully!";
                if ($inserted > 0) $message .= " ($inserted new)";
                if ($updated > 0) $message .= " ($updated updated)";
            } else {
                $success = false;
                $message = "No rates were saved. Please check your input.";
            }
            
        } catch (Exception $e) {
            $db->rollBack();
            $success = false;
            $message = 'Error saving rates: ' . $e->getMessage();
        }
        
        echo json_encode(['success' => $success, 'message' => $message]);
        exit;
    }
    
    /**
     * READ - Get exchange rates for a specific date and currency
     */
    public function getRatesForDate()
    {
        header('Content-Type: application/json');
        $db = new Database();
        
        $date = $_GET['date'] ?? '';
        $currency_id = (int)($_GET['currency_id'] ?? 1);
        
        if (empty($date)) {
            echo json_encode(['success' => false, 'message' => 'Date is required']);
            exit;
        }
        
        if (empty($currency_id)) {
            echo json_encode(['success' => false, 'message' => 'Currency is required']);
            exit;
        }
        
        $query = "
            SELECT 
                er.*, 
                b.bank_name 
            FROM bank_exchange_rate_t er 
            JOIN banklist_master_t b ON er.bank_id = b.id 
            WHERE er.exchange_date = ? 
                AND er.currency_id = ?
                AND b.for_exchange = 'Y' 
                AND b.display = 'Y'
            ORDER BY b.bank_name
        ";
        
        $result = $db->customQuery($query, [$date, $currency_id]);
        
        $rates = [];
        $bcc_rate = null;
        
        if ($result) {
            foreach ($result as $row) {
                $rates[$row['bank_id']] = $row;
                // Get BCC rate from first result (same for all)
                if (!$bcc_rate && !empty($row['bcc_rate'])) {
                    $bcc_rate = $row['bcc_rate'];
                }
            }
        }
        
        echo json_encode([
            'success' => true, 
            'rates' => $rates,
            'bcc_rate' => $bcc_rate
        ]);
        exit;
    }
    
    /**
     * READ - Get history in horizontal format
     */
    public function getHistoryHorizontal()
    {
        header('Content-Type: application/json');
        $db = new Database();
        
        try {
            $currency_id = (int)($_GET['currency_id'] ?? 1);
            $limit = (int)($_GET['limit'] ?? 100);
            
            // Get banks for column headers
            $banks = $db->selectData('banklist_master_t', 'id, bank_name', ['for_exchange' => 'Y', 'display' => 'Y']);
            $bankIds = array_column($banks, 'id');
            
            // Get currency info
            $currencyData = $db->selectData('currency_master_t', 'currency_short_name', ['id' => $currency_id]);
            $currencyName = $currencyData[0]['currency_short_name'] ?? 'N/A';
            
            // Get unique dates with rates
            $dateQuery = "
                SELECT DISTINCT 
                    exchange_date,
                    bcc_rate
                FROM bank_exchange_rate_t 
                WHERE currency_id = ?
                ORDER BY exchange_date DESC
                LIMIT ?
            ";
            $dates = $db->customQuery($dateQuery, [$currency_id, $limit]);
            
            $data = [];
            
            if ($dates) {
                foreach ($dates as $dateRow) {
                    $row = [
                        'exchange_date' => date('d-m-Y', strtotime($dateRow['exchange_date'])),
                        'exchange_date_raw' => $dateRow['exchange_date'], // For delete functionality
                        'currency_name' => $currencyName,
                        'bcc_rate' => number_format($dateRow['bcc_rate'], 2),
                        'banks' => [],
                        'updated_at' => ''
                    ];
                    
                    // Initialize banks array with bank IDs as keys
                    foreach ($bankIds as $bankId) {
                        $row['banks'][$bankId] = '';
                    }
                    
                    // Get rates for this date
                    $rateQuery = "
                        SELECT 
                            bank_id,
                            bank_rate,
                            updated_at
                        FROM bank_exchange_rate_t
                        WHERE exchange_date = ?
                            AND currency_id = ?
                    ";
                    $rates = $db->customQuery($rateQuery, [$dateRow['exchange_date'], $currency_id]);
                    
                    if ($rates) {
                        $latestUpdate = '';
                        foreach ($rates as $rate) {
                            $row['banks'][$rate['bank_id']] = number_format($rate['bank_rate'], 2);
                            
                            // Track latest update time
                            if (empty($latestUpdate) || $rate['updated_at'] > $latestUpdate) {
                                $latestUpdate = $rate['updated_at'];
                            }
                        }
                        $row['updated_at'] = $latestUpdate ? date('d-m H:i', strtotime($latestUpdate)) : '-';
                    }
                    
                    $data[] = $row;
                }
            }
            
            echo json_encode([
                'success' => true,
                'data' => $data
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'data' => [],
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }
    
    /**
     * DELETE - Delete single exchange rate record
     */
    public function deleteRate()
    {
        header('Content-Type: application/json');
        $db = new Database();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }
        
        $id = (int)($_POST['id'] ?? 0);
        
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'Invalid rate ID']);
            exit;
        }
        
        try {
            // Check if record exists
            $existing = $db->selectData('bank_exchange_rate_t', 'id', ['id' => $id]);
            
            if (empty($existing)) {
                echo json_encode(['success' => false, 'message' => 'Rate not found']);
                exit;
            }
            
            // Delete the record
            $result = $db->deleteData('bank_exchange_rate_t', ['id' => $id]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Rate deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error deleting rate']);
            }
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }
    
    /**
     * DELETE - Delete all rates for a specific date and currency
     */
    public function deleteRatesForDate()
    {
        header('Content-Type: application/json');
        $db = new Database();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }
        
        $exchange_date = $_POST['exchange_date'] ?? '';
        $currency_id = (int)($_POST['currency_id'] ?? 0);
        
        if (empty($exchange_date)) {
            echo json_encode(['success' => false, 'message' => 'Date is required']);
            exit;
        }
        
        if (empty($currency_id)) {
            echo json_encode(['success' => false, 'message' => 'Currency is required']);
            exit;
        }
        
        try {
            $db->beginTransaction();
            
            // Check if rates exist for this date and currency
            $checkQuery = "
                SELECT COUNT(*) as count 
                FROM bank_exchange_rate_t 
                WHERE exchange_date = ? 
                    AND currency_id = ?
            ";
            $countResult = $db->customQuery($checkQuery, [$exchange_date, $currency_id]);
            
            if (empty($countResult) || $countResult[0]['count'] == 0) {
                echo json_encode(['success' => false, 'message' => 'No rates found for this date']);
                exit;
            }
            
            // Delete all rates for this date and currency
            $deleteQuery = "
                DELETE FROM bank_exchange_rate_t 
                WHERE exchange_date = ? 
                    AND currency_id = ?
            ";
            $db->customQuery($deleteQuery, [$exchange_date, $currency_id]);
            
            $db->commit();
            
            echo json_encode([
                'success' => true, 
                'message' => 'All rates deleted successfully for ' . date('d-m-Y', strtotime($exchange_date))
            ]);
            
        } catch (Exception $e) {
            $db->rollBack();
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }
    
    /**
     * Export rates to CSV
     */
    public function exportRatesHorizontal()
    {
        $db = new Database();
        $date = $_GET['date'] ?? date('Y-m-d');
        $currency_id = (int)($_GET['currency_id'] ?? 1);
        
        // Get banks
        $banks = $db->selectData('banklist_master_t', 'id, bank_name', ['for_exchange' => 'Y', 'display' => 'Y']);
        
        // Get currency info
        $currency = $db->selectData('currency_master_t', 'currency_short_name', ['id' => $currency_id]);
        $currencyName = $currency[0]['currency_short_name'] ?? 'USD';
        
        // Get rates
        $query = "
            SELECT 
                er.bank_id,
                er.bank_rate,
                er.bcc_rate
            FROM bank_exchange_rate_t er
            JOIN banklist_master_t b ON er.bank_id = b.id
            WHERE er.exchange_date = ?
                AND er.currency_id = ?
                AND b.for_exchange = 'Y'
                AND b.display = 'Y'
        ";
        $rates = $db->customQuery($query, [$date, $currency_id]);
        
        // Organize rates by bank_id
        $ratesByBank = [];
        $bccRate = '';
        if ($rates) {
            foreach ($rates as $rate) {
                $ratesByBank[$rate['bank_id']] = $rate['bank_rate'];
                if (empty($bccRate) && !empty($rate['bcc_rate'])) {
                    $bccRate = $rate['bcc_rate'];
                }
            }
        }
        
        // Generate CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="exchange_rates_' . $date . '_' . $currencyName . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // UTF-8 BOM for Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Header row
        $headers = ['Date', 'Currency', 'BCC Rate'];
        foreach ($banks as $bank) {
            $headers[] = strtoupper($bank['bank_name']);
        }
        fputcsv($output, $headers);
        
        // Data row for selected date
        $dataRow = [
            date('d-m-Y', strtotime($date)),
            $currencyName,
            $bccRate ? number_format($bccRate, 2, '.', '') : '-'
        ];
        foreach ($banks as $bank) {
            $dataRow[] = isset($ratesByBank[$bank['id']]) ? 
                         number_format($ratesByBank[$bank['id']], 2, '.', '') : 
                         '-';
        }
        fputcsv($output, $dataRow);
        
        // Add blank row
        fputcsv($output, []);
        
        // Header for historical data
        fputcsv($output, ['Historical Data (Last 7 Days)']);
        fputcsv($output, $headers);
        
        // Additional rows for last 7 days
        for ($i = 1; $i <= 7; $i++) {
            $prevDate = date('Y-m-d', strtotime($date . ' -' . $i . ' days'));
            
            $prevRates = $db->customQuery($query, [$prevDate, $currency_id]);
            $prevRatesByBank = [];
            $prevBccRate = '';
            
            if ($prevRates) {
                foreach ($prevRates as $rate) {
                    $prevRatesByBank[$rate['bank_id']] = $rate['bank_rate'];
                    if (empty($prevBccRate) && !empty($rate['bcc_rate'])) {
                        $prevBccRate = $rate['bcc_rate'];
                    }
                }
                
                if (!empty($prevRatesByBank)) {
                    $dataRow = [
                        date('d-m-Y', strtotime($prevDate)),
                        $currencyName,
                        $prevBccRate ? number_format($prevBccRate, 2, '.', '') : '-'
                    ];
                    foreach ($banks as $bank) {
                        $dataRow[] = isset($prevRatesByBank[$bank['id']]) ? 
                                     number_format($prevRatesByBank[$bank['id']], 2, '.', '') : 
                                     '-';
                    }
                    fputcsv($output, $dataRow);
                }
            }
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Legacy method for backward compatibility
     */
    public function getHistory()
    {
        header('Content-Type: application/json');
        $db = new Database();
        
        try {
            $draw = intval($_GET['draw'] ?? 1);
            $limit = intval($_GET['limit'] ?? 100);
            
            $query = "
                SELECT 
                    er.exchange_date,
                    er.bcc_rate,
                    b.bank_name,
                    c.currency_short_name,
                    er.bank_rate,
                    er.updated_at
                FROM bank_exchange_rate_t er
                INNER JOIN banklist_master_t b ON er.bank_id = b.id
                LEFT JOIN currency_master_t c ON er.currency_id = c.id
                WHERE b.for_exchange = 'Y'
                ORDER BY er.exchange_date DESC, b.bank_name
                LIMIT ?
            ";
            
            $records = $db->customQuery($query, [$limit]);
            
            $data = [];
            if ($records) {
                foreach ($records as $row) {
                    $data[] = [
                        'exchange_date' => date('d-m-Y', strtotime($row['exchange_date'])),
                        'bank_name' => $row['bank_name'],
                        'currency_short_name' => $row['currency_short_name'] ?? 'N/A',
                        'bcc_rate' => number_format($row['bcc_rate'], 2),
                        'bank_rate' => number_format($row['bank_rate'], 2),
                        'updated_at' => date('d-m-Y H:i:s', strtotime($row['updated_at']))
                    ];
                }
            }
            
            echo json_encode([
                'draw' => $draw,
                'recordsTotal' => count($data),
                'recordsFiltered' => count($data),
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
}
?>