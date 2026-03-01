<?php

declare(strict_types=1);

namespace MikroTik\Logging;

final class Sanitizer
{
    /** @param array<string,mixed> $data */
    public function sanitize(array $data): array
    {
        $blocked = ['password', 'pass', 'token', 'authorization', 'cookie'];
        $out = [];
        foreach ($data as $k => $v) {
            $key = is_string($k) ? strtolower($k) : '';
            if ($key !== '' && in_array($key, $blocked, true)) {
                $out[$k] = '***';
                continue;
            }
            $out[$k] = is_array($v) ? $this->sanitize($v) : $v;
        }
        return $out;
    }
}
