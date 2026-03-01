<?php

declare(strict_types=1);

namespace MikroTik\Nodes;

use MikroTik\Client\Runtime\RouterOSRuntime;
use MikroTik\Fluent\Node;

final class IPNode extends Node
{
    public function __construct(RouterOSRuntime $rt)
    {
        parent::__construct($rt, 'ip');
    }

    public function Firewall(): FirewallNode
    {
        return new FirewallNode($this->rt, $this->path . '/firewall');
    }
}
