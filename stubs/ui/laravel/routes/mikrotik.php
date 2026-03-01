<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use MikroTik\Facades\RouterOS;

Route::middleware(['web'])->prefix('mikrotik')->group(function () {
    Route::get('/firewall/nat', function () {
        $router = RouterOS::New()
            ->Username((string) config('mikrotik.connections.main.username'))
            ->Password((string) config('mikrotik.connections.main.password'))
            ->Host((string) config('mikrotik.connections.main.host'))
            ->Port((int) config('mikrotik.connections.main.port'))
            ->Transport((string) config('mikrotik.connections.main.transport'))
            ->Tls((bool) config('mikrotik.connections.main.tls'))
            ->VerifyTls((bool) config('mikrotik.connections.main.verify_tls'))
            ->Connect();

        $res = $router->IP()->Firewall()->Nat()->Get();
        return response()->json($res->data);
    });
});
