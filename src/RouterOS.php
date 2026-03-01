<?php

declare(strict_types=1);

namespace MikroTik;

use MikroTik\Client\Builder\RouterOSBuilder;

final class RouterOS
{
    public static function New(): RouterOSBuilder
    {
        return new RouterOSBuilder();
    }
}
