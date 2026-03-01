# PHPStorm (autocomplete complet)

## 1) Générer les stubs FULL depuis ton routeur

```bash
php artisan mikrotik:inspect --host=192.168.88.1 --username=admin --password='***' --tls=1 --port=443
```

Fichiers générés:
- `resources/routeros/schema-local.json`
- `stubs/routeros.full.stub.php`

## 2) (Recommandé) Charger les stubs via Composer

Le package contient déjà:

```json
"autoload-dev": {
  "files": [
    "stubs/routeros.full.stub.php"
  ]
}
```

Ensuite, dans ton projet:

```bash
composer dump-autoload
```

## 3) Forcer PHPStorm à indexer (si nécessaire)

- `File → Invalidate Caches… → Invalidate and Restart`

Option alternative:
- Mark `stubs/` as **Resource Root**
- Ajoute `<project>/stubs` dans `Settings → PHP → Include Path`
