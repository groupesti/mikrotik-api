# Toutes les commandes (réalité RouterOS)

RouterOS expose un arbre de commandes qui dépend:
- version exacte (ex: 7.21.3)
- paquets installés
- matériel

Le package fournit deux choses:

1) **Accès universel**: vous pouvez appeler N'IMPORTE QUEL chemin via `Path()`:
```php
$router->Path('ip/firewall/nat')->Get();
$router->Path('system/script')->Command('run', ['.id' => '*1']);
```

2) **Génération d'autocomplétion** (schema + stubs) depuis VOTRE routeur:
```bash
php artisan mikrotik:inspect --host=192.168.88.1 --username=admin --password='***' --tls=1 --port=443
```

Cela génère:
- `resources/routeros/schema-local.json` (dump de /rest/console/inspect)
- `stubs/routeros.full.stub.php` (methods racines pour l'IDE)
