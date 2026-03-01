<?php

declare(strict_types=1);

namespace MikroTik\Client\Runtime;

use Illuminate\Contracts\Events\Dispatcher as LaravelDispatcher;
use MikroTik\Contracts\TransportInterface;
use MikroTik\DTO\ConnectionConfig;
use MikroTik\DTO\RequestOptions;
use MikroTik\DTO\Results\MikroTikResult;
use MikroTik\Events\MikroTikFailed;
use MikroTik\Events\MikroTikRequesting;
use MikroTik\Events\MikroTikResponded;
use MikroTik\Fluent\Node;
use MikroTik\Nodes\IPNode;
use MikroTik\Transport\ApiTransport;
use MikroTik\Transport\RestTransport;

final class RouterOSRuntime
{
    private readonly RestTransport $rest;
    private readonly ApiTransport $api;
    private readonly LaravelDispatcher $events;

    private function __construct(
        private readonly ConnectionConfig $cfg,
        ?LaravelDispatcher $events = null
    ) {
        $this->rest = new RestTransport();
        $this->api = new ApiTransport();
        $this->events = $events ?? new class implements LaravelDispatcher {
            public function dispatch($event, $payload = [], $halt = false) { return null; }
            public function push($event, $payload = []) { }
            public function until($event, $payload = []) { return null; }
            public function listen($events, $listener = null) { }
            public function hasListeners($eventName) { return false; }
            public function subscribe($subscriber) { }
            public function flush($event) { }
            public function forget($event) { }
            public function forgetPushed() { }
        };
    }

    public static function fromConfig(ConnectionConfig $cfg, ?LaravelDispatcher $events = null): self
    {
        return new self($cfg, $events);
    }

    public function Rest(): self { return $this->withTransport('rest'); }
    public function Api(): self { return $this->withTransport('api'); }

    private function withTransport(string $transport): self
    {
        return new self(new ConnectionConfig(
            transport: $transport,
            host: $this->cfg->host,
            restPort: $this->cfg->restPort,
            username: $this->cfg->username,
            password: $this->cfg->password,
            tls: $this->cfg->tls,
            verifyTls: $this->cfg->verifyTls,
            timeoutSeconds: $this->cfg->timeoutSeconds,
            retryMaxAttempts: $this->cfg->retryMaxAttempts,
            retryBackoffMs: $this->cfg->retryBackoffMs,
            logEnabled: $this->cfg->logEnabled,
            logChannel: $this->cfg->logChannel,
        ), $this->events);
    }

    // Root nodes
    public function IP(): IPNode { return new IPNode($this); }
    public function Ip(): IPNode { return $this->IP(); }

    public function Ipv6(): Node { return $this->Path('ipv6'); }
    public function Interface(): Node { return $this->Path('interface'); }
    public function System(): Node { return $this->Path('system'); }
    public function Tool(): Node { return $this->Path('tool'); }
    public function User(): Node { return $this->Path('user'); }
    public function UserManager(): Node { return $this->Path('user-manager'); }
    public function Certificate(): Node { return $this->Path('certificate'); }
    public function Console(): Node { return $this->Path('console'); }
    public function Disk(): Node { return $this->Path('disk'); }
    public function File(): Node { return $this->Path('file'); }
    public function Log(): Node { return $this->Path('log'); }
    public function Mpls(): Node { return $this->Path('mpls'); }
    public function Partitions(): Node { return $this->Path('partitions'); }
    public function Port(): Node { return $this->Path('port'); }
    public function Ppp(): Node { return $this->Path('ppp'); }
    public function Queue(): Node { return $this->Path('queue'); }
    public function Routing(): Node { return $this->Path('routing'); }
    public function Snmp(): Node { return $this->Path('snmp'); }
    public function SpecialLogin(): Node { return $this->Path('special-login'); }
    public function Task(): Node { return $this->Path('task'); }

    public function Path(string $path): Node
    {
        return new Node($this, trim($path, '/'));
    }

    // REST+API operations with fallback (if prefer rest)
    /** @param array<string,mixed> $payload */
    public function restOrApiAdd(string $path, array $payload): MikroTikResult
    {
        return $this->callWithFallback(
            fn(TransportInterface $t, RequestOptions $o) => $t->put($this->cfg, $path, $payload, $o),
            'PUT',
            $path,
            ['payload' => $payload]
        );
    }

    public function restOrApiGet(string $path): MikroTikResult
    {
        return $this->callWithFallback(
            fn(TransportInterface $t, RequestOptions $o) => $t->get($this->cfg, $path, [], $o),
            'GET',
            $path
        );
    }

    /** @param array<string,mixed> $payload */
    public function restOrApiSet(string $path, string $id, array $payload): MikroTikResult
    {
        $full = trim($path,'/') . '/' . $id;
        return $this->callWithFallback(
            fn(TransportInterface $t, RequestOptions $o) => $t->patch($this->cfg, $full, $payload, $o),
            'PATCH',
            $full,
            ['payload' => $payload]
        );
    }

    public function restOrApiRemove(string $path, string $id): MikroTikResult
    {
        $full = trim($path,'/') . '/' . $id;
        return $this->callWithFallback(
            fn(TransportInterface $t, RequestOptions $o) => $t->delete($this->cfg, $full, $o),
            'DELETE',
            $full
        );
    }

    /** @param array<string,mixed> $payload */
    public function restOrApiCommand(string $path, string $command, array $payload): MikroTikResult
    {
        $full = trim($path,'/') . '/' . trim($command,'/');
        return $this->callWithFallback(
            fn(TransportInterface $t, RequestOptions $o) => $t->post($this->cfg, $full, $payload, $o),
            'POST',
            $full,
            ['payload' => $payload]
        );
    }

    /** @param callable(TransportInterface,RequestOptions):MikroTikResult $fn
        @param array<string,mixed> $meta */
    private function callWithFallback(callable $fn, string $method, string $target, array $meta = []): MikroTikResult
    {
        $opts = new RequestOptions(
            requestId: bin2hex(random_bytes(12)),
            timeoutSeconds: $this->cfg->timeoutSeconds,
            retryMaxAttempts: $this->cfg->retryMaxAttempts,
            retryBackoffMs: $this->cfg->retryBackoffMs,
            logEnabled: $this->cfg->logEnabled,
            logChannel: $this->cfg->logChannel,
        );

        $primary = $this->cfg->transport === 'api' ? $this->api : $this->rest;

        $this->events->dispatch(new MikroTikRequesting($this->cfg->transport, $method, $target, $opts->requestId, $meta));
        $start = hrtime(true);

        try {
            $res = $fn($primary, $opts);
            $elapsed = (int)((hrtime(true)-$start)/1_000_000);
            $this->events->dispatch(new MikroTikResponded($this->cfg->transport, $method, $target, $opts->requestId, $res->status, $elapsed));
            return $res;
        } catch (\Throwable $e) {
            // Fallback only when prefer rest
            if ($this->cfg->transport === 'rest') {
                try {
                    $res = $fn($this->api, $opts);
                    $elapsed = (int)((hrtime(true)-$start)/1_000_000);
                    $this->events->dispatch(new MikroTikResponded('api', $method, $target, $opts->requestId, $res->status, $elapsed));
                    return $res;
                } catch (\Throwable $e2) {
                    $elapsed = (int)((hrtime(true)-$start)/1_000_000);
                    $this->events->dispatch(new MikroTikFailed($this->cfg->transport, $method, $target, $opts->requestId, $elapsed, $e2::class, $e2->getMessage()));
                    throw $e2;
                }
            }

            $elapsed = (int)((hrtime(true)-$start)/1_000_000);
            $this->events->dispatch(new MikroTikFailed($this->cfg->transport, $method, $target, $opts->requestId, $elapsed, $e::class, $e->getMessage()));
            throw $e;
        }
    }
}
