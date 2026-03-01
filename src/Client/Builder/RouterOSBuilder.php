<?php

declare(strict_types=1);

namespace MikroTik\Client\Builder;

use MikroTik\Client\Runtime\RouterOSRuntime;
use MikroTik\DTO\ConnectionConfig;
use MikroTik\Exceptions\ConnectionException;

final class RouterOSBuilder
{
    private ?string $username = null;
    private ?string $password = null;
    private ?string $host = null;

    private int $port = 443;

    /** @var 'rest'|'api' */
    private string $transport = 'rest';

    private bool $tls = true;
    private bool $verifyTls = false;

    private float $timeoutSeconds = 10.0;
    private int $retryMaxAttempts = 2;
    private int $retryBackoffMs = 150;

    private bool $logEnabled = true;
    private string $logChannel = 'stack';

    public function Username(string $username): self { $this->username = $username; return $this; }
    public function Password(string $password): self { $this->password = $password; return $this; }
    public function Host(string $host): self { $this->host = $host; return $this; }

    public function Port(int $port): self { $this->port = $port; return $this; }

    /** @param 'rest'|'api' $transport */
    public function Transport(string $transport): self
    {
        if (!in_array($transport, ['rest','api'], true)) {
            throw new ConnectionException("Transport invalide: {$transport}");
        }
        $this->transport = $transport;
        return $this;
    }

    public function Tls(bool $enabled): self { $this->tls = $enabled; return $this; }
    public function VerifyTls(bool $verify): self { $this->verifyTls = $verify; return $this; }

    public function TimeoutSeconds(float $seconds): self
    {
        if ($seconds <= 0) { throw new ConnectionException('TimeoutSeconds doit être > 0'); }
        $this->timeoutSeconds = $seconds;
        return $this;
    }

    public function Retry(int $maxAttempts, int $backoffMs = 150): self
    {
        if ($maxAttempts < 0 || $backoffMs < 0) {
            throw new ConnectionException('Retry invalide');
        }
        $this->retryMaxAttempts = $maxAttempts;
        $this->retryBackoffMs = $backoffMs;
        return $this;
    }

    public function Logging(bool $enabled, string $channel = 'stack'): self
    {
        $this->logEnabled = $enabled;
        $this->logChannel = $channel;
        return $this;
    }

    public function Connect(): RouterOSRuntime
    {
        if ($this->username === null || $this->password === null || $this->host === null) {
            throw new ConnectionException('Username, Password, Host requis');
        }

        $cfg = new ConnectionConfig(
            transport: $this->transport,
            host: $this->host,
            restPort: $this->port, // REST only (option 1)
            username: $this->username,
            password: $this->password,
            tls: $this->tls,
            verifyTls: $this->verifyTls,
            timeoutSeconds: $this->timeoutSeconds,
            retryMaxAttempts: $this->retryMaxAttempts,
            retryBackoffMs: $this->retryBackoffMs,
            logEnabled: $this->logEnabled,
            logChannel: $this->logChannel,
        );

        return RouterOSRuntime::fromConfig($cfg);
    }
}
