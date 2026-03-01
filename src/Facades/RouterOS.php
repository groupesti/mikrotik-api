<?php

declare(strict_types=1);

namespace MikroTik\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \MikroTik\Client\Builder\RouterOSBuilder New()
 */
final class RouterOS extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'mikrotik.routeros';
    }
}
