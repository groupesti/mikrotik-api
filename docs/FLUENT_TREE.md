# Fluent Tree

Menus racines disponibles (méthodes):

- Certificate(), Console(), Disk(), File(), Interface(), IP()/Ip(), Ipv6(), Log(), Mpls(), Partitions(), Port(),
  Ppp(), Queue(), Routing(), Snmp(), SpecialLogin(), System(), Task(), Tool(), User(), UserManager()

Fallback universel:
- `Path('ip/firewall/nat')`

Exemples:
```php
$router->IP()->Firewall()->Rules()->Get(); // alias vers /ip/firewall/filter
$router->IP()->Firewall()->Nat()->Get();   // /ip/firewall/nat
```
