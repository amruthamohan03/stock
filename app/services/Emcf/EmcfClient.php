<?php

class EmcfClient
{
    public static function request(
        string $method,
        string $endpoint,
        array $payload = null
    ): array {
        $url = rtrim(EmcfConfig::BASE_URL, '/') . '/' . ltrim($endpoint, '/');
        $ch = curl_init($url);
//print_r($payload);exit;
        $headers = EmcfAuth::getHeaders();
        if ($payload !== null) {
            $headers[] = "Content-Length: " . strlen(json_encode($payload));
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => strtoupper($method),
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => EmcfConfig::TIMEOUT,
            CURLOPT_ENCODING       => '',
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        }

        $env = getenv('APP_ENV');
        if ($env === 'development') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false) {
            throw new EmcfException('cURL error: ' . curl_error($ch));
        }

        curl_close($ch);

        $decoded = json_decode($response, true);//echo $httpCode;

        if ($httpCode >= 400 || isset($decoded['errorCode'])) {

            $errorCode = $decoded['errorCode'] ?? null;
            $errorDesc = $decoded['errorDesc'] ?? 'e-MCF API error';

            $message = $errorDesc;
            if ($errorCode !== null) {
                $message = EmcfErrorCodes::getMessage((int)$errorCode,$message);
            }

            throw new EmcfException(
                $message,
                $httpCode,
                $decoded,
                $errorCode ? (int)$errorCode : null
            );
        }


        return [
            'status_code' => $httpCode,
            'data'        => $decoded,
        ];
    }
}
