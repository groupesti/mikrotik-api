<?php

declare(strict_types=1);

namespace MikroTik\Fluent;

use MikroTik\Client\Runtime\RouterOSRuntime;
use MikroTik\DTO\Results\MikroTikResult;
use MikroTik\Exceptions\MikroTikException;

class Node
{
    /** @var array<string,mixed> */
    protected array $payload = [];

    public function __construct(
        protected readonly RouterOSRuntime $rt,
        protected readonly string $path
    ) {}

    public function child(string $segment): Node
    {
        $seg = trim($segment, '/');
        $newPath = $this->path === '' ? $seg : $this->path . '/' . $seg;
        return new Node($this->rt, $newPath);
    }

    public function Path(): string
    {
        return $this->path;
    }

    /** @param array<string,mixed> $payload */
    public function With(array $payload): self
    {
        foreach ($payload as $k => $v) {
            $this->payload[(string)$k] = $v;
        }
        return $this;
    }

    public function __call(string $name, array $args): mixed
    {
        $lname = strtolower($name);

        return match ($name) {
            'Get' => $this->Get(),
            'Add' => $this->Add(),
            'Set' => $this->Set((string)($args[0] ?? '')),
            'Remove' => $this->Remove((string)($args[0] ?? '')),
            'Command' => $this->Command((string)($args[0] ?? ''), (array)($args[1] ?? [])),
            'Print' => $this->Print(),
            default => $this->setPayloadKey($lname, $args),
        };
    }

    private function setPayloadKey(string $key, array $args): self
    {
        if (count($args) !== 1) {
            throw new MikroTikException("Setter {$key} attend 1 argument");
        }
        $this->payload[$key] = $args[0];
        return $this;
    }

    public function Get(): MikroTikResult
    {
        return $this->rt->restOrApiGet($this->path);
    }

    public function Add(): MikroTikResult
    {
        return $this->rt->restOrApiAdd($this->path, $this->normalizePayload($this->payload));
    }

    public function Set(string $id): MikroTikResult
    {
        if ($id === '') { throw new MikroTikException('Set(id) requis'); }
        return $this->rt->restOrApiSet($this->path, $id, $this->normalizePayload($this->payload));
    }

    public function Remove(string $id): MikroTikResult
    {
        if ($id === '') { throw new MikroTikException('Remove(id) requis'); }
        return $this->rt->restOrApiRemove($this->path, $id);
    }

    /** @param array<string,mixed> $payload */
    public function Command(string $command, array $payload = []): MikroTikResult
    {
        if ($command === '') { throw new MikroTikException('Command(name) requis'); }
        return $this->rt->restOrApiCommand($this->path, $command, $this->normalizePayload($payload));
    }

    public function Print(): PrintNode
    {
        return new PrintNode($this->rt, $this->path);
    }


    /** @param array<string,mixed> $payload */
    private function normalizePayload(array $payload): array
    {
        $out = [];
        foreach ($payload as $k => $v) {
            $out[(string)$k] = $this->normalizePayloadValue($v);
        }
        return $out;
    }

    private function normalizePayloadValue(mixed $v): mixed
    {
        if ($v instanceof \BackedEnum) {
            return $v->value;
        }
        if ($v instanceof \UnitEnum) {
            return $v->name;
        }
        if (is_bool($v)) {
            // RouterOS (API) expects yes/no; REST accepts booleans sometimes.
            // We normalize to yes/no for consistency.
            return $v ? 'yes' : 'no';
        }
        return $v;
    }

}

