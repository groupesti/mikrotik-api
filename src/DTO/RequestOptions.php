<?php

declare(strict_types=1);

namespace MikroTik\DTO;

final readonly class RequestOptions
{
    public function __construct(
        public string $requestId,
        public float $timeoutSeconds,
        public int $retryMaxAttempts,
        public int $retryBackoffMs,
        public bool $logEnabled,
        public string $logChannel,
    ) {}
}
