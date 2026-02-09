<?php

class EmcfInvoiceService
{
    /**
     * STEP-2: Submit invoice for validation & calculation
     * (Does NOT finalize invoice)
     *
     * @param array $payload  Normalized e-MCF payload
     * @return array          UID + calculated totals
     * @throws EmcfException
     */
    public static function submitInvoice(array $payload): array
    {
        // ------------------------------
        // 1. Basic mandatory fields
        // ------------------------------
        foreach (['nif', 'mode', 'isf', 'type', 'items', 'operator'] as $field) {
            if (empty($payload[$field])) {
                throw new EmcfException("Missing required field: {$field}");
            }
        }

        // ------------------------------
        // 2. Normalize mode
        // ------------------------------
        /*$payload['mode'] = strtoupper($payload['mode']);
        if (!in_array($payload['mode'], ['HT', 'TTC'], true)) {
            throw new EmcfException('Invalid mode. Allowed values: HT or TTC');
        }*/

        // ------------------------------
        // 3. Invoice type validation
        // ------------------------------
        if (!EmcfInvoiceTypes::isValid($payload['type'])) {
            throw new EmcfException(
                'Invalid invoice type. Allowed: ' .
                implode(', ', EmcfInvoiceTypes::ALL)
            );
        }

        // ------------------------------
        // 4. Credit note rule (FA / EA)
        // ------------------------------
        /*if (EmcfInvoiceTypes::isCreditNote($payload['type'])) {
            if (empty($payload['reference'])) {
                throw new EmcfException(
                    'Reference is required for credit notes (FA, EA)',
                    400,
                    [],
                    EmcfErrorCodes::MISSING_REFERENCE
                );
            }
            if (strlen($payload['reference']) !== 24) {
                throw new EmcfException(
                    'Reference must be exactly 24 characters',
                    400,
                    [],
                    EmcfErrorCodes::INVALID_REFERENCE_LENGTH
                );
            }
        }*/

        // ------------------------------
        // 5. Item-level validation
        // ------------------------------
        if (!is_array($payload['items']) || count($payload['items']) === 0) {
            throw new EmcfException(
                'Invoice must contain at least one item',
                400,
                [],
                EmcfErrorCodes::NO_ITEMS
            );
        }

        foreach ($payload['items'] as &$item) {
            foreach (['type', 'name', 'price', 'quantity', 'taxGroup'] as $f) {
                if (!isset($item[$f])) {
                    throw new EmcfException("Missing item field: {$f}");
                }
            }

            // Force correct numeric format
            $item['price']    = round((float)$item['price'], 2);
            $item['quantity'] = (float)$item['quantity'];

            if (!preg_match('/^[A-I]$/', $item['taxGroup'])) {
                throw new EmcfException(
                    'Invalid tax group',
                    400,
                    [],
                    EmcfErrorCodes::INVALID_TAX_GROUP
                );
            }
        }
        unset($item);

        // ------------------------------
        // 6. Payment normalization
        // ------------------------------
        if (empty($payload['payment'])) {
            $payload['payment'] = [
                'name'   => 'ESPECES',
                'amount' => self::calculateItemsTotal($payload)
            ];
        }

        // ------------------------------
        // 7. Submit to e-MCF
        // ------------------------------
        $response = EmcfClient::request('POST', '/', $payload);

        // ------------------------------
        // 8. Normalize Step-2 response
        // ------------------------------
        $data = $response['data'];

        // If API reports validation error
        if (!empty($data['errorCode'])) {
            throw new EmcfException(
                $data['errorDesc'] ?? 'Invoice validation failed',
                400,
                $data,
                (int)$data['errorCode']
            );
        }

        return [
            // Required for Step-3
            'uid' => $data['uid'],

            // Tax rates A–I
            'tax_rates' => self::extractRange($data, 'ta', 'ti'),

            // Totals per tax group (gross)
            'tax_group_totals' => self::extractRange($data, 'taa', 'tai'),

            // Amounts excluding VAT
            'amounts_excl_vat' => self::extractRange($data, 'haa', 'hai'),

            // VAT amounts
            'vat_amounts' => self::extractRange($data, 'vaa', 'vai'),

            // Specific tax
            'specific_tax_total' => (float)($data['ts'] ?? 0),

            // Invoice totals
            'total'  => (float)$data['total'],   // TTC
            'vtotal' => (float)$data['vtotal'],  // Total VAT

            // Keep full raw response for audit
            'raw' => $data
        ];
    }

    public static function finalizeInvoice(string $uid, $type, $total, $vtotal): array
    {
        if($type == 'confirm'){
            $endpoint = '/confirm';
        } else {
            $endpoint = '/cancel';
        }

        $response = EmcfClient::request('PUT', $uid . $endpoint, ['total' => $total,'vtotal' => $vtotal]);
        $data = $response['data'];

        // If API reports validation error
        if (!empty($data['errorCode'])) {
            throw new EmcfException(
                $data['errorDesc'] ?? 'Invoice finalization failed',
                400,
                $data,
                (int)$data['errorCode']
            );
        }

        if($type == 'confirm'){
            $confirmData = [
                'success' => true,
                'uid' => $uid,
                'qrCode' => $data['qrCode'] ?? '',
                'codeDEFDGI' => $data['codeDEFDGI'] ?? '',
                'dateTime' => $data['dateTime'] ?? date('Y-m-d H:i:s'),
                'counters' => $data['counters'] ?? '',
                'nim' => $data['nim'] ?? '',
                'raw' => $data
            ];
        } else {
            $confirmData = [
                'success' => true,
                'uid' => $uid
            ];
        }
        return $confirmData;
    }

    /**
     * Calculate item total (HT)
     */
    private static function calculateItemsTotal(array $payload): float
    {
        $total = 0;
        foreach ($payload['items'] as $item) {
            $total += ((float)$item['price']) * ((float)$item['quantity']);
        }
        return round($total, 2);
    }

    private static function extractRange(array $data, string $from, string $to): array
    {
        $result = [];
        foreach (range(ord($from), ord($to)) as $char) {
            $key = chr($char);
            if (isset($data[$key])) {
                $result[$key] = (float)$data[$key];
            }
        }
        return $result;
    }

}
