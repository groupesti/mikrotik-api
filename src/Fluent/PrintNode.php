<?php

declare(strict_types=1);

namespace MikroTik\Fluent;

use MikroTik\Client\Runtime\RouterOSRuntime;
use MikroTik\DTO\Results\MikroTikResult;

final class PrintNode extends Node
{
    /** @var list<string> */
    private array $query = [];

    /** @var list<string> */
    private array $proplist = [];

    public function __construct(RouterOSRuntime $rt, string $path)
    {
        parent::__construct($rt, $path);
    }

    /** @param list<string> $queryWords */
    public function Query(array $queryWords): self
    {
        $this->query = $queryWords;
        return $this;
    }

    /** @param list<string> $props */
    public function Proplist(array $props): self
    {
        $this->proplist = $props;
        return $this;
    }

    public function Get(): MikroTikResult
    {
        $payload = [];
        if ($this->query !== []) { $payload['.query'] = $this->query; }
        if ($this->proplist !== []) { $payload['.proplist'] = $this->proplist; }

        return $this->rt->restOrApiCommand($this->path, 'print', $payload);
    }
}
