<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use MikroTik\RouterOS;

final class BasicTest extends TestCase
{
    public function testBuilderCreatesRuntime(): void
    {
        $rt = RouterOS::New()
            ->Host('127.0.0.1')
            ->Username('u')
            ->Password('p')
            ->Port(443)
            ->Transport('rest')
            ->Tls(false)
            ->VerifyTls(false)
            ->Connect();

        $this->assertNotNull($rt);
    }
}
