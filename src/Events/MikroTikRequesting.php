<?php

declare(strict_types=1);

namespace MikroTik\Events;

final readonly class MikroTikRequesting
{
    /**
     * @param array<string,mixed> $meta
     */
    public function __construct(
        public string $transport,
        public string $method,
        public string $target,
        public string $requestId,
        public array $meta = [],
    ) {}
}
