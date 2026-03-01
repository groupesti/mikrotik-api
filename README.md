# groupesti/mikrotik-api

Client RouterOS **7.* (incl. 7.21.3)** avec **REST + API socket** pour **Laravel 12 / PHP 8.4**, typé, avec **Events** et **Logs**, et une API fluent:

```php
use MikroTik\RouterOS;

$router = RouterOS::New()
  ->Username('admin')
  ->Password('secret')
  ->Host('192.168.88.1')
  ->Port(443)              // REST uniquement (option 1)
  ->Transport('rest')      // rest|api (défaut: rest)
  ->Tls(true)              // REST: https ; API: 8729 (TLS) / 8728 (plain)
  ->VerifyTls(false)       // REST uniquement
  ->Connect();

$router->IP()->Firewall()->Rules()->Get(); // /ip/firewall/filter
$router->IP()->Firewall()->Nat()->Get();   // /ip/firewall/nat
```

## Print avancé (.query / .proplist)

```php
$res = $router->Interface()
  ->Print()
  ->Query(['type=ether'])
  ->Proplist(['.id','name','type'])
  ->Get();
```

## REST + API fallback

Si `Transport('rest')`, le runtime tente REST et bascule sur API socket en cas d'échec (optionnel).
Vous pouvez forcer: `->Api()` ou `->Rest()` sur l'objet connecté.

## Docs
Voir `docs/`.


## Toutes les commandes (RouterOS 7.21.3)

Le runtime supporte **tout** via `Path()` (accès universel).
Pour générer un schema/stubs IDE depuis ton routeur (liste exacte des commandes):
```bash
php artisan mikrotik:inspect --host=192.168.88.1 --username=admin --password='***' --tls=1 --port=443
```

Voir `docs/ALL_COMMANDS.md`.


## PHPStorm
Voir `docs/PHPSTORM.md`.


## Alias ergonomiques
Voir `docs/ALIASES.md`.

## Enums
Voir `docs/ENUMS.md`.

## Packagist
Voir `docs/PACKAGIST.md`.
