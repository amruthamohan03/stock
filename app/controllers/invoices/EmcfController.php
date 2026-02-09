<?php

class EmcfController extends Controller
{
  private $db;
  private $logFile;
  private $logoPath;

  public function __construct()
  {
    $this->db = new Database();
    $this->logFile = __DIR__ . '/../../logs/emcf_invoice.log';
    $this->logoPath = __DIR__ . '/../../../public/images/logo.jpg';
    
    $logDir = dirname($this->logFile);
    if (!is_dir($logDir)) {
      @mkdir($logDir, 0755, true);
    }
  }

  private function logError($message)
  {
    @file_put_contents($this->logFile, "[" . date('Y-m-d H:i:s') . "] {$message}\n", FILE_APPEND);
  }

  public function checkEmcfStatus(){
    try {
        $status = EmcfStatusService::checkStatus();

        if (!$status['status']) {
            $this->logError('e-MCF API is not operational');
        }

        print_r($status);

    } catch (EmcfException $e) {
        $this->logError('e-MCF Error: ' . $e->getMessage());
    } catch (Exception $e) {
        $this->logError('Error: ' . $e->getMessage());
    }
  }

  public function sendInvoiceToEmcf(int $invoiceId, string $type, $invoiceData)
  {
      try {
        $payload = EmcfInvoiceNormalizer::normalize($invoiceData);
        $response = EmcfInvoiceService::submitInvoice($payload);

        if(empty($response) || !isset($response['uid'])) {
            return json_encode(['success' => false, 'message' => 'Invalid response from e-MCF service']);
        }

        $sql = "
          INSERT INTO emcf_invoice (
              inv_type,
              invoice_id,
              emcf_submit,
              uid,
              total,
              vtotal,
              submit_req,
              created_by
          ) VALUES (
              :inv_type,
              :invoice_id,
              :emcf_submit,
              :uid,
              :total,
              :vtotal,
              :submit_req,
              :created_by
          )
          ON DUPLICATE KEY UPDATE
              emcf_submit = VALUES(emcf_submit),
              uid         = VALUES(uid),
              total       = VALUES(total),
              vtotal      = VALUES(vtotal),
              submit_req  = VALUES(submit_req),
              updated_at  = NOW()
          ";

          $emcf_db_resp = $this->db->customQuery($sql,[
              ':inv_type'     => $type,
              ':invoice_id'   => $invoiceId,
              ':emcf_submit'  => 'Y',
              ':uid'          => $response['uid'] ?? null,
              ':total'        => $response['total'] ?? null,
              ':vtotal'       => $response['vtotal'] ?? null,
              ':submit_req'   => json_encode($response['raw'] ?? null),
              ':created_by'   => (int)($_SESSION['user_id'] ?? 1)
          ]);

          if($type == 'EXP_CREDIT' || $type == 'IMP_CREDIT'){
            $msg = "e-MCF Credit Note submitted for DGI verification!";
          }else{
            $msg = "e-MCF Invoice submitted for DGI verification!";
          }
        
        return json_encode([
          'success' => true,
          'data' => $response,
          'message' => $msg
        ]);

      } catch (EmcfException $e) {
        $this->logError('e-MCF Invoice Error: ' . $e->getMessage());
        return json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
      } catch (Exception $e) {
        $this->logError("Mark DGI Verified Exception: " . $e->getMessage());
        return json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
      }
      return json_encode(['success' => false, 'message' => 'Unknown error occurred']);
  }

  public function sendFinalizeEMCF(string $uid, string $type)
  {
      try {
          $sql = "SELECT total,vtotal FROM emcf_invoice WHERE uid = ? LIMIT 1";
          $existing = $this->db->customQuery($sql, [$uid]);
          if (empty($existing)) {
              return ['success' => false, 'message' => 'e-MCF Invoice not found'];
          }
          $response = EmcfInvoiceService::finalizeInvoice($uid, $type, $existing[0]['total'], $existing[0]['vtotal']);
          $msg = "e-MCF Invoice cancelled successfully!";
          if($type === 'confirm') {
              $sql = "
              UPDATE emcf_invoice SET
                  emcf_final = 'Y', emcf_confirm='Y',qrcode = ?, codedefdgi = ?, counters = ?,
                  nim = ?, final_req = ?
              WHERE uid = ?
              ";
              $this->db->customQuery($sql, [$response['qrCode'], $response['codeDEFDGI'], $response['counters'], $response['nim'], json_encode($response['raw']), $uid]);
          }
          return [
              'success' => true,
              'data' => $response,
              'message' => $msg
          ];
      } catch (EmcfException $e) {
          $this->logError('e-MCF Finalize Invoice Error: ' . $e->getMessage());
          return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
      } catch (Exception $e) {
          $this->logError("Finalize e-MCF Invoice Exception: " . $e->getMessage());
          return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
      }
  }

  public function createEmcfPayload($itemsArr, $header, $reference = ''): array
  {
      $items = [];
      $total = 0;
      foreach ($itemsArr as $row) {
          $price = round((float)$row['price'], 2);
          $items[] = [
              'code'     => 'DEB' . $row['item_code'],
              'type'     => 'SER',
              'name'     => $row['item_name'],
              'price'    => $price,
              'quantity' => round((float)$row['quantity'], 2),
              'taxGroup' => $row['taxGroup'],
          ];
          $total += $price * round((float)$row['quantity'], 2);
      }

      if (empty($items)) {
          throw new Exception('Invoice has no items');
      }

      $response = [
          'nif'   => EmcfConfig::getNifNumber(),
          'rn'    => $header['invoice_ref'],
          'mode'  => 'ht',
          'isf'   => EmcfConfig::getIsfCode(),
          'type'  => $reference ? 'FA' : 'FV',
          'items' => $items,
          'client' => [
              'nif'     => $header['client_nif'],
              'name'    => $header['client_name'],
              'contact' => $header['client_contact'] ?? '',
              'address' => $header['client_address'],
              'type'    => 'PM'
          ],
          'operator' => [
              'id'   => 'OP'.$header['operator_id'],
              'name' => $header['operator_name']
          ],
          'payment' => [[
              'name' => 'CREDIT',
              'amount' => round($total,2)
          ]]
      ];
      if($reference) {
          $response['reference'] = $reference;
          $response['referenceType'] = 'RAM';
      }
    return $response;
  }
}

