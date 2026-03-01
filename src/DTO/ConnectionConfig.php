<?php

declare(strict_types=1);

namespace MikroTik\DTO;

final readonly class ConnectionConfig
{
    /**
     * @param 'rest'|'api' $transport
     */
    public function __construct(
        public string $transport,
        public string $host,
        public int $restPort,
        public string $username,
        public string $password,
        public bool $tls,
        public bool $verifyTls,
        public float $timeoutSeconds,
        public int $retryMaxAttempts,
        public int $retryBackoffMs,
        public bool $logEnabled,
        public string $logChannel,
    ) {}

    public function apiPort(): int
    {
        return $this->tls ? 8729 : 8728;
    }
}
