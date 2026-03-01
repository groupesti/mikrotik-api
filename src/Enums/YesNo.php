<?php

declare(strict_types=1);

namespace MikroTik\Enums;

enum YesNo: string
{
    case Yes = 'yes';
    case No = 'no';

    public static function fromBool(bool $value): self
    {
        return $value ? self::Yes : self::No;
    }
}
