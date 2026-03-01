<?php

declare(strict_types=1);

namespace MikroTik\Transport;

use MikroTik\Contracts\TransportInterface;
use MikroTik\DTO\ConnectionConfig;
use MikroTik\DTO\RequestOptions;
use MikroTik\DTO\Results\MikroTikResult;
use MikroTik\Exceptions\ApiException;
use MikroTik\Transport\Api\ApiClient;

final class ApiTransport implements TransportInterface
{
    /** @param array<string,string> $query */
    public function get(ConnectionConfig $cfg, string $path, array $query, RequestOptions $opts): MikroTikResult
    {
        // Map generic GET to /<path>/print
        $cmd = '/' . trim($path, '/') . '/print';
        return $this->run($cfg, [$cmd], $opts);
    }

    /** @param array<string,mixed> $payload */
    public function put(ConnectionConfig $cfg, string $path, array $payload, RequestOptions $opts): MikroTikResult
    {
        // Map REST PUT add => /<path>/add
        $cmd = '/' . trim($path, '/') . '/add';
        return $this->run($cfg, [$cmd], $opts, $payload);
    }

    /** @param array<string,mixed> $payload */
    public function patch(ConnectionConfig $cfg, string $path, array $payload, RequestOptions $opts): MikroTikResult
    {
        // REST uses /<path>/<id>. Here /<path> includes id at end.
        $parts = explode('/', trim($path,'/'));
        $id = array_pop($parts);
        $base = implode('/', $parts);

        if ($id === '' || $base === '') {
            throw new ApiException('PATCH expects <path>/<id>');
        }

        $cmd = '/' . $base . '/set';
        $payload['.id'] = $id;
        return $this->run($cfg, [$cmd], $opts, $payload);
    }

    public function delete(ConnectionConfig $cfg, string $path, RequestOptions $opts): MikroTikResult
    {
        $parts = explode('/', trim($path,'/'));
        $id = array_pop($parts);
        $base = implode('/', $parts);

        if ($id === '' || $base === '') {
            throw new ApiException('DELETE expects <path>/<id>');
        }

        $cmd = '/' . $base . '/remove';
        return $this->run($cfg, [$cmd], $opts, ['.id' => $id]);
    }

    /** @param array<string,mixed> $payload */
    public function post(ConnectionConfig $cfg, string $path, array $payload, RequestOptions $opts): MikroTikResult
    {
        // POST is universal: path is already /menu/command in runtime.
        $cmd = '/' . trim($path, '/');
        return $this->run($cfg, [$cmd], $opts, $payload);
    }

    /**
     * @param list<string> $words
     * @param array<string,mixed> $params
     */
    private function run(ConnectionConfig $cfg, array $words, RequestOptions $opts, array $params = []): MikroTikResult
    {
        $client = new ApiClient($cfg, $opts);
        try {
            $reply = $client->command($words, $params);
            return new MikroTikResult(200, $reply['data'], $reply['meta']);
        } finally {
            $client->close();
        }
    }
}
