<?php

declare(strict_types=1);

namespace MikroTik\Enums;

enum IpProtocol: string
{
    case TCP = 'tcp';
    case UDP = 'udp';
    case ICMP = 'icmp';
    case GRE = 'gre';
    case IPIP = 'ipip';
    case ESP = 'ipsec-esp';
    case AH = 'ipsec-ah';
    case SCTP = 'sctp';
    case OSPF = 'ospf';
}
