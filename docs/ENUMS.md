# Enums (valeurs typées)

Le package fournit des enums utiles:

- `MikroTik\Enums\YesNo`
- `MikroTik\Enums\IpProtocol`
- `MikroTik\Enums\FirewallChain`
- `MikroTik\Enums\FirewallAction`

Exemple:

```php
use MikroTik\Enums\FirewallAction;
use MikroTik\Enums\FirewallChain;

$router->IP()->Firewall()->Nat()
  ->Chain(FirewallChain::Srcnat)
  ->Action(FirewallAction::Masquerade)
  ->OutInterface('ether1')
  ->Add();
```
