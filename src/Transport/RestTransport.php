<?php

declare(strict_types=1);

namespace MikroTik\Transport;

use MikroTik\Contracts\TransportInterface;
use MikroTik\DTO\ConnectionConfig;
use MikroTik\DTO\RequestOptions;
use MikroTik\DTO\Results\MikroTikResult;
use MikroTik\Exceptions\RestException;

final class RestTransport implements TransportInterface
{
    /** @param array<string,string> $query */
    public function get(ConnectionConfig $cfg, string $path, array $query, RequestOptions $opts): MikroTikResult
    {
        return $this->request($cfg, 'GET', $path, $query, $opts);
    }

    /** @param array<string,mixed> $payload */
    public function put(ConnectionConfig $cfg, string $path, array $payload, RequestOptions $opts): MikroTikResult
    {
        return $this->request($cfg, 'PUT', $path, $payload, $opts);
    }

    /** @param array<string,mixed> $payload */
    public function patch(ConnectionConfig $cfg, string $path, array $payload, RequestOptions $opts): MikroTikResult
    {
        return $this->request($cfg, 'PATCH', $path, $payload, $opts);
    }

    public function delete(ConnectionConfig $cfg, string $path, RequestOptions $opts): MikroTikResult
    {
        return $this->request($cfg, 'DELETE', $path, null, $opts);
    }

    /** @param array<string,mixed> $payload */
    public function post(ConnectionConfig $cfg, string $path, array $payload, RequestOptions $opts): MikroTikResult
    {
        return $this->request($cfg, 'POST', $path, $payload, $opts);
    }

    /** @param array<string,mixed>|array<string,string>|null $body */
    private function request(ConnectionConfig $cfg, string $method, string $path, array|null $body, RequestOptions $opts): MikroTikResult
    {
        $scheme = $cfg->tls ? 'https' : 'http';
        $base = rtrim($scheme . '://' . $cfg->host . ':' . $cfg->restPort, '/');
        $url = $base . '/rest/' . ltrim($path, '/');

        $ch = curl_init();
        if ($ch === false) {
            throw new RestException('cURL init failed');
        }

        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
        ];

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, (int)ceil($opts->timeoutSeconds));
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $cfg->username . ':' . $cfg->password);

        if ($scheme === 'https') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $cfg->verifyTls);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $cfg->verifyTls ? 2 : 0);
        }

        if ($body !== null && $method !== 'GET') {
            $json = json_encode($body, JSON_UNESCAPED_SLASHES);
            if ($json === false) {
                curl_close($ch);
                throw new RestException('json_encode failed');
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $raw = curl_exec($ch);
        if ($raw === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new RestException('REST request failed: ' . $err);
        }

        $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        $data = [];
        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            $data = is_array($decoded) ? $decoded : ['raw' => $raw];
        }

        if ($status >= 400) {
            throw new RestException('REST HTTP ' . $status . ': ' . (is_string($raw) ? $raw : ''));
        }

        return new MikroTikResult($status, $data, ['url' => $url]);
    }
}
