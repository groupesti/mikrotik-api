<?php

declare(strict_types=1);

namespace MikroTik\DTO\Results;

final readonly class MikroTikResult
{
    /**
     * @param array<int,array<string,string>>|array<string,string>|array<string,mixed> $data
     * @param array<string,mixed> $meta
     */
    public function __construct(
        public int $status,
        public array $data,
        public array $meta = [],
    ) {}

    public function ok(): bool
    {
        return $this->status >= 200 && $this->status < 300;
    }
}
