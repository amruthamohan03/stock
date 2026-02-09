<?php

class EmcfInvoiceTypes
{
    // Domestic
    public const FV = 'FV'; // Sales Invoice
    public const FT = 'FT'; // Advance Payment Invoice
    public const FA = 'FA'; // Credit Note

    // Export
    public const EV = 'EV'; // Export Sales Invoice
    public const ET = 'ET'; // Export Advance Payment
    public const EA = 'EA'; // Export Credit Note

    /**
     * All valid invoice types
     */
    public const ALL = [
        self::FV,
        self::FT,
        self::FA,
        self::EV,
        self::ET,
        self::EA,
    ];

    /**
     * Credit note types
     */
    public const CREDIT_NOTES = [
        self::FA,
        self::EA,
    ];

    /**
     * Export invoice types
     */
    public const EXPORT_TYPES = [
        self::EV,
        self::ET,
        self::EA,
    ];

    public static function isValid(string $type): bool
    {
        return in_array($type, self::ALL, true);
    }

    public static function isCreditNote(string $type): bool
    {
        return in_array($type, self::CREDIT_NOTES, true);
    }

    public static function isExport(string $type): bool
    {
        return in_array($type, self::EXPORT_TYPES, true);
    }
}
