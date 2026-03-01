<?php

declare(strict_types=1);

namespace MikroTik\Enums;

enum FirewallChain: string
{
    case Input = 'input';
    case Forward = 'forward';
    case Output = 'output';
    case Prerouting = 'prerouting';
    case Postrouting = 'postrouting';
    case Srcnat = 'srcnat';
    case Dstnat = 'dstnat';
}
