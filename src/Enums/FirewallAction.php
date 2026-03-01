<?php

declare(strict_types=1);

namespace MikroTik\Enums;

enum FirewallAction: string
{
    case Accept = 'accept';
    case Drop = 'drop';
    case Reject = 'reject';
    case Log = 'log';
    case Tarpit = 'tarpit';
    case FasttrackConnection = 'fasttrack-connection';
    case Jump = 'jump';
    case Return = 'return';
    case AddSrcToAddressList = 'add-src-to-address-list';
    case AddDstToAddressList = 'add-dst-to-address-list';
    case Masquerade = 'masquerade';
    case SrcNat = 'src-nat';
    case DstNat = 'dst-nat';
    case Netmap = 'netmap';
}
