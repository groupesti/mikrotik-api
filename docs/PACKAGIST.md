# Publication Packagist (public)

## 1) Préparer le repo GitHub
- Repo: `groupesti/mikrotik-api`
- Branch principale: `main`
- Tag: `v2.0.0` (SemVer)

## 2) Publication
- Connecte Packagist à GitHub
- Active "Auto-update" (webhook)
- Crée un tag Git: `v2.0.0`

## 3) CI
Le workflow GitHub Actions `.github/workflows/ci.yml` valide:
- PHP 8.4
- PHPUnit
- PHPStan
