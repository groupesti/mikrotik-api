<?php

declare(strict_types=1);

namespace MikroTik\Events;

final readonly class MikroTikFailed
{
    public function __construct(
        public string $transport,
        public string $method,
        public string $target,
        public string $requestId,
        public int $elapsedMs,
        public string $exceptionClass,
        public string $message,
    ) {}
}
