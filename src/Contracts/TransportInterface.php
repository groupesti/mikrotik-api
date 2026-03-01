<?php

declare(strict_types=1);

namespace MikroTik\Contracts;

use MikroTik\DTO\ConnectionConfig;
use MikroTik\DTO\RequestOptions;
use MikroTik\DTO\Results\MikroTikResult;

interface TransportInterface
{
    /** @param array<string,string> $query */
    public function get(ConnectionConfig $cfg, string $path, array $query, RequestOptions $opts): MikroTikResult;

    /** @param array<string,mixed> $payload */
    public function put(ConnectionConfig $cfg, string $path, array $payload, RequestOptions $opts): MikroTikResult;

    /** @param array<string,mixed> $payload */
    public function patch(ConnectionConfig $cfg, string $path, array $payload, RequestOptions $opts): MikroTikResult;

    public function delete(ConnectionConfig $cfg, string $path, RequestOptions $opts): MikroTikResult;

    /** @param array<string,mixed> $payload */
    public function post(ConnectionConfig $cfg, string $path, array $payload, RequestOptions $opts): MikroTikResult;
}
