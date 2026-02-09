<?php

class EmcfInvoiceNormalizer
{
    public static function normalize(array $input): array
    {
        $normalized = [
            'nif'   => self::normalizeNif($input['nif']),
            'rn'    => $input['rn'],
            'mode'  => $input['mode'],
            'isf'   => $input['isf'],
            'type'  => $input['type'],

            'items' => self::normalizeItems($input['items']),
            'client'=> self::normalizeClient($input['client'] ?? null),
            'operator' => self::normalizeOperator($input['operator']),
            'payment'  => self::normalizePayments($input['payment'] ?? []),
        ];

        if (isset($input['reference'])) {
            $normalized['reference'] = $input['reference'];
            $normalized['referenceType'] = $input['referenceType'] ?? 'RAM';
        }
        return $normalized;
    }

    private static function normalizeItems(array $items): array
    {
        if (count($items) === 0) {
            throw new EmcfException('At least one item is required');
        }

        return array_map(function ($item) {
            return [
                'code'     => $item['code'] ?? null,
                'type'     => $item['type'],
                'name'     => $item['name'],
                'price'    => round($item['price'], 2),
                'quantity' => (float)$item['quantity'],
                'taxGroup' => $item['taxGroup'],
            ];
        }, $items);
    }

    private static function normalizeClient(?array $client): ?array
    {
        if (!$client) return null;

        return [
            'nif'     => self::normalizeNif($client['nif'] ?? null),
            'name'    => $client['name'] ?? null,
            'type'    => $client['type'] ?? null,
            'contact' => $client['contact'] ?? '',
            'address' => $client['address'] ?? '',
        ];
    }

    private static function normalizeOperator(array $op): array
    {
        return [
            'id'   => (string)$op['id'],
            'name' => $op['name'],
        ];
    }

    private static function normalizePayments(array $payments): array
    {
        if (!$payments || count($payments) === 0) {
            return [[
                'name'   => 'ESPECES',
                'amount' => 0
            ]];
        }
        return array_map(function ($pay) {
            return [
                'name'   => $pay['name'],
                'amount' => round($pay['amount'], 2),
            ];
        }, $payments);
    }

    private static function normalizeNif(?string $nif): ?string
    {
        if (!$nif) return null;

        // ⚠️ Replace with real mapping logic
        /*if (!preg_match('/^\d{13}$/', $nif)) {
            return mapInternalNifToEmcf($nif);
        }*/

        return $nif;
    }
}
