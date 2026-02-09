<?php

class EmcfConfig
{
    public const BASE_URL = 'https://developper.dgirdc.cd/edef/api/invoice';
    public const ENTREPRISE_NIF = 'A1309334L';
    public const ENTREPRISE_EMCF_ID = 'CD01003075-1';
    public const TIMEOUT = 30; // seconds

    public static function getJwtToken(): string
    {
        $token = getenv('EMCF_JWT_TOKEN');
        // Trim surrounding quotes if present
        if (is_string($token) && strlen($token) >= 2) {
            if ((($token[0] === "'" && substr($token, -1) === "'") || ($token[0] === '"' && substr($token, -1) === '"'))) {
                $token = substr($token, 1, -1);
            }
        }

        return (string)($token ?? '');
    }

    public static function getNifNumber(): string
    {
        return self::ENTREPRISE_NIF;
    }
    
    public static function getIsfCode(): string
    {
        return self::ENTREPRISE_EMCF_ID;
    }
}
