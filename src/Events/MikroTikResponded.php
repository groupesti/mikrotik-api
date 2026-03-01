<?php

declare(strict_types=1);

namespace MikroTik\Events;

final readonly class MikroTikResponded
{
    public function __construct(
        public string $transport,
        public string $method,
        public string $target,
        public string $requestId,
        public int $status,
        public int $elapsedMs,
    ) {}
}
