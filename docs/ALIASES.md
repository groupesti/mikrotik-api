# Alias ergonomiques

Le package supporte:
- Alias *automatiques* (singulier/pluriel, règles courantes)
- Alias *configurables* via `resources/routeros/aliases.json`

## Génération IDE (FULL)
Après `mikrotik:inspect`, le fichier `stubs/routeros.full.stub.php` contiendra des méthodes supplémentaires comme:
- `Rules()` (alias de `Filter()` sous `/ip/firewall`)
- `Addresses()` (alias de `Address()`)
- `Interfaces()` (alias de `Interface()`)
- Pluriels auto (ex: `Connection()` + `Connections()`)

## Personnaliser
Édite `resources/routeros/aliases.json` et relance `mikrotik:inspect`.
