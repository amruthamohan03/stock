<?php

class EmcfStatusService
{
    /**
     * Check API availability and system status
     */
    public static function checkStatus(): array
    {
        $response = EmcfClient::request('GET', '');

        return [
            'status'                => $response['data']['status'] ?? false,
            'version'               => $response['data']['version'] ?? null,
            'nif'                   => $response['data']['nif'] ?? null,
            'nim'                   => $response['data']['nim'] ?? null,
            'tokenValid'            => $response['data']['tokenValid'] ?? null,
            'serverDateTime'        => $response['data']['serverDateTime'] ?? null,
            'pendingRequestsCount'  => $response['data']['pendingRequestsCount'] ?? 0,
            'pendingRequestsList'   => $response['data']['pendingRequestsList'] ?? [],
        ];
    }
}
