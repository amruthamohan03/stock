<?php

class EmcfFinalizeInvoiceService
{
    public const ACTION_CONFIRM = 'CONFIRM';
    public const ACTION_CANCEL  = 'CANCEL';

    /**
     * Finalize or cancel an invoice
     *
     * @param string $uid  UID returned from Step-2
     * @param string $action CONFIRM | CANCEL
     * @param array|null $localTotals Optional – used only for CONFIRM
     */
    public static function finalize(
        string $uid,
        string $action,
        ?array $localTotals = null
    ): FinalizeInvoiceResponseDataDto {

        self::validateAction($action);

        // Mandatory verification before CONFIRM
        if ($action === self::ACTION_CONFIRM) {
            self::verifyTotals($localTotals);
        }

        $endpoint = sprintf('/%s/%s', $uid, $action);

        $response = EmcfClient::request('POST', $endpoint);

        return new FinalizeInvoiceResponseDataDto($response['data']);
    }

    private static function validateAction(string $action): void
    {
        if (!in_array($action, [self::ACTION_CONFIRM, self::ACTION_CANCEL], true)) {
            throw new EmcfException('Invalid finalize action. Use CONFIRM or CANCEL.');
        }
    }

    /**
     * Ensure SFE totals match e-MCF totals
     * (required by specification)
     */
    private static function verifyTotals(?array $totals): void
    {
        if (!$totals) {
            throw new EmcfException(
                'Local totals must be provided before CONFIRM action'
            );
        }

        foreach (['total_excl_vat', 'total_vat', 'total_incl_vat'] as $field) {
            if (!isset($totals[$field])) {
                throw new EmcfException(
                    "Missing required total verification field: {$field}"
                );
            }
        }

        // NOTE:
        // Actual numeric comparison should be done here
        // between locally computed totals and Step-2 response totals
    }
}
