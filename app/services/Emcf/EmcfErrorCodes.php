<?php

class EmcfErrorCodes
{
    public const MAX_PENDING_EXCEEDED        = 1;
    public const INVALID_INVOICE_TYPE        = 3;
    public const MISSING_REFERENCE           = 4;
    public const INVALID_REFERENCE_LENGTH    = 5;
    public const INVALID_PAYMENT_TYPE        = 7;
    public const NO_ITEMS                    = 8;
    public const INVALID_TAX_GROUP           = 9;
    public const REF_VALIDATION_TEMP_FAIL    = 10;
    public const REF_NOT_FOUND               = 11;
    public const CREDIT_EXCEEDS_ORIGINAL     = 12;
    public const INVOICE_NOT_FOUND_OR_DONE   = 20;
    public const INTERNAL_ERROR              = 99;

    public static function getMessage(int $code, $message): string
    {
        return match ($code) {
            1  => 'Maximum number of pending invoices exceeded (limit: 10)',
            3  => 'Invoice type is not valid',
            4  => 'Original invoice reference is missing (FA/EA required)',
            5  => 'Original invoice reference must be exactly 24 characters',
            7  => 'Payment type is not valid',
            8  => 'Invoice must contain at least one item',
            9  => 'Invalid tax group at item level',
            10 => 'Original invoice reference cannot be validated - retry later',
            11 => 'Original invoice reference not found in system',
            12 => 'Credit note amount exceeds original invoice amount',
            20 => 'Invoice does not exist or is already finalized/cancelled',
            24 => 'The article type is not valid',
            33 => 'The price format must be inclusive of tax or exclusive of tax',
            99 => 'Internal processing error - contact DGI support',
            default => $message
        };
    }

    /**
     * Errors that are safe to retry automatically
     */
    public static function isRetryable(int $code): bool
    {
        return in_array($code, [
            self::REF_VALIDATION_TEMP_FAIL
        ], true);
    }
}
