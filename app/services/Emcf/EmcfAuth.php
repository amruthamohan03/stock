<?php

class EmcfAuth
{
    public static function getHeaders(): array
    {
        return [
            'Content-Type: application/json; charset=utf-8',
            'Accept: application/json',
            'Authorization: Bearer ' . EmcfConfig::getJwtToken(),
        ];
    }
}
