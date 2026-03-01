# UI Stubs (simples) — Inertia + Vue 3

Objectif: fournir des stubs prêts à copier (pas un admin panel complet).

## Générer (publier) les stubs

### Tailwind CSS 4.*
```bash
php artisan mikrotik:ui --framework=tailwind
```

### Bootstrap 5.3.8
```bash
php artisan mikrotik:ui --framework=bootstrap
```

## Ce qui est créé dans ton projet

- Pages:
  - `resources/js/Pages/MikroTik/FirewallNat/Index.vue`
- Components:
  - `resources/js/Components/MikroTik/{Card,Button,Table}.vue`
- Route demo:
  - `routes/mikrotik.php` (à inclure dans `routes/web.php`)

## Notes
- Le endpoint demo retourne JSON via le package (`IP()->Firewall()->Nat()->Get()`).
- Adapte la sécurité (auth/middleware) selon ton contexte.
