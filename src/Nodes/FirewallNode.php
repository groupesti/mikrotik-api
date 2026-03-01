<?php

declare(strict_types=1);

namespace MikroTik\Nodes;

use MikroTik\Client\Runtime\RouterOSRuntime;
use MikroTik\Fluent\Node;

final class FirewallNode extends Node
{
    public function __construct(RouterOSRuntime $rt, string $path)
    {
        parent::__construct($rt, $path);
    }

    public function Rules(): Node
    {
        return $this->child('filter');
    }

    public function Filter(): Node
    {
        return $this->child('filter');
    }

    public function Nat(): Node
    {
        return $this->child('nat');
    }

    public function Mangle(): Node
    {
        return $this->child('mangle');
    }

    public function Raw(): Node
    {
        return $this->child('raw');
    }

    public function AddressList(): Node
    {
        return $this->child('address-list');
    }

    public function ServicePort(): Node
    {
        return $this->child('service-port');
    }
}
