<?php 

class ImportsydoniaController extends Controller
{
    private function getDbConnection()
    {
        try {
            $host = 'localhost';
            $port = 3307;
            $dbname = 'malabar_db';
            $username = 'root';
            $password = '';
            
            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
            
            return $pdo;
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            return null;
        }
    }

    public function index()
    {
        $data = [
            'title' => 'Import Sydonia'
        ];

        $this->viewWithLayout('sydonia/importsydonia', $data);
    }

    public function cleanDate($date)
    {
        if (empty($date) || trim($date) === "") {
            return null;
        }

        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return null;
        }

        return date("Y-m-d", $timestamp);
    }

    public function validate_mca_refs()
    {
        header("Content-Type: application/json");

        try {
            $input = file_get_contents("php://input");
            $data = json_decode($input, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                echo json_encode([
                    "success" => false, 
                    "msg" => "JSON decode error: " . json_last_error_msg()
                ]);
                return;
            }

            if (!isset($data["records"]) || !is_array($data["records"])) {
                echo json_encode([
                    "success" => false, 
                    "msg" => "Invalid data format"
                ]);
                return;
            }

            $records = $data["records"];
            
            if (count($records) === 0) {
                echo json_encode([
                    "success" => false, 
                    "msg" => "No records to validate"
                ]);
                return;
            }

            $conn = $this->getDbConnection();
            
            if (!$conn) {
                echo json_encode([
                    "success" => false,
                    "msg" => "Database connection failed"
                ]);
                return;
            }

            $valid = [];
            $invalid = [];

            foreach ($records as $row) {
                $mcaRef = trim($row["mca_ref"] ?? "");
                
                if (empty($mcaRef)) {
                    continue;
                }

                try {
                    $sql = "SELECT COUNT(*) as count FROM imports_t WHERE TRIM(UPPER(mca_ref)) = TRIM(UPPER(:mca_ref))";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':mca_ref', $mcaRef, PDO::PARAM_STR);
                    $stmt->execute();
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($result && $result['count'] > 0) {
                        $valid[] = $mcaRef;
                    } else {
                        $invalid[] = $mcaRef;
                    }

                } catch (Exception $e) {
                    error_log("Database error checking MCA ref {$mcaRef}: " . $e->getMessage());
                    $invalid[] = $mcaRef;
                }
            }

            echo json_encode([
                "success" => true,
                "valid" => $valid,
                "invalid" => $invalid,
                "total" => count($records),
                "valid_count" => count($valid),
                "invalid_count" => count($invalid)
            ]);

        } catch (Exception $e) {
            error_log("Exception in validate_mca_refs: " . $e->getMessage());
            echo json_encode([
                "success" => false,
                "msg" => "Server error: " . $e->getMessage()
            ]);
        }
    }

    public function update_mca_refs()
    {
        header("Content-Type: application/json");

        try {
            $input = file_get_contents("php://input");
            $data = json_decode($input, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                echo json_encode([
                    "success" => false, 
                    "msg" => "JSON decode error: " . json_last_error_msg()
                ]);
                return;
            }

            if (!isset($data["records"]) || !is_array($data["records"])) {
                echo json_encode([
                    "success" => false, 
                    "msg" => "No data received"
                ]);
                return;
            }

            $rows = $data["records"];
            $updated = 0;
            $failed = 0;
            $errors = [];

            $conn = $this->getDbConnection();
            
            if (!$conn) {
                echo json_encode([
                    "success" => false,
                    "msg" => "Database connection failed"
                ]);
                return;
            }

            foreach ($rows as $row) {
                $mcaRef = trim($row["mca_ref"] ?? "");

                if (empty($mcaRef)) {
                    continue;
                }

                try {
                    // BUILD UPDATE QUERY - ONLY FOR NON-EMPTY FIELDS
                    $updateFields = [];
                    $params = [];
                    
                    if (!empty(trim($row["declaration_reference"] ?? ""))) {
                        $updateFields[] = "declaration_reference = :declaration_reference";
                        $params[':declaration_reference'] = trim($row["declaration_reference"]);
                    }
                    
                    $dgdaInDate = $this->cleanDate($row["declaration_date"] ?? "");
                    if ($dgdaInDate !== null) {
                        $updateFields[] = "dgda_in_date = :dgda_in_date";
                        $params[':dgda_in_date'] = $dgdaInDate;
                    }
                    
                    if (!empty(trim($row["liquidation_reference"] ?? ""))) {
                        $updateFields[] = "liquidation_reference = :liquidation_reference";
                        $params[':liquidation_reference'] = trim($row["liquidation_reference"]);
                    }
                    
                    $liquidationDate = $this->cleanDate($row["liquidation_date"] ?? "");
                    if ($liquidationDate !== null) {
                        $updateFields[] = "liquidation_date = :liquidation_date";
                        $params[':liquidation_date'] = $liquidationDate;
                    }
                    
                    if (!empty(trim($row["quittance_reference"] ?? ""))) {
                        $updateFields[] = "quittance_reference = :quittance_reference";
                        $params[':quittance_reference'] = trim($row["quittance_reference"]);
                    }
                    
                    $quittanceDate = $this->cleanDate($row["quittance_date"] ?? "");
                    if ($quittanceDate !== null) {
                        $updateFields[] = "quittance_date = :quittance_date";
                        $params[':quittance_date'] = $quittanceDate;
                    }
                    
                    if (!empty(trim($row["liquidation_amount"] ?? ""))) {
                        $updateFields[] = "liquidation_amount = :liquidation_amount";
                        $params[':liquidation_amount'] = trim($row["liquidation_amount"]);
                    }
                    
                    // Always update timestamp
                    $updateFields[] = "updated_at = :updated_at";
                    $params[':updated_at'] = date("Y-m-d H:i:s");
                    $params[':mca_ref'] = $mcaRef;

                    if (count($updateFields) <= 1) {
                        $failed++;
                        $errors[] = "MCA: {$mcaRef} - No data to update";
                        continue;
                    }

                    $sql = "UPDATE imports_t SET " . implode(", ", $updateFields) . " 
                            WHERE TRIM(UPPER(mca_ref)) = TRIM(UPPER(:mca_ref))";

                    $stmt = $conn->prepare($sql);
                    $result = $stmt->execute($params);
                    $rowsAffected = $stmt->rowCount();

                    if ($result && $rowsAffected > 0) {
                        $updated++;
                    } else {
                        $failed++;
                        $errors[] = "MCA: {$mcaRef} - No changes made";
                    }

                } catch (Exception $e) {
                    $failed++;
                    $errorMsg = "MCA: {$mcaRef} - " . $e->getMessage();
                    error_log($errorMsg);
                    $errors[] = $errorMsg;
                }
            }

            echo json_encode([
                "success" => true,
                "updated" => $updated,
                "failed" => $failed,
                "total" => count($rows),
                "errors" => $errors
            ]);

        } catch (Exception $e) {
            error_log("Exception in update_mca_refs: " . $e->getMessage());
            echo json_encode([
                "success" => false,
                "msg" => "Server error: " . $e->getMessage()
            ]);
        }
    }
}

?>