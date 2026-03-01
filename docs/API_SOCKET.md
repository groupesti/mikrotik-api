# API Socket Transport

Transport API socket RouterOS (8728/8729).

- TLS: `Tls(true)` => 8729 ; `Tls(false)` => 8728
- Login: supporte 2 modes:
  - direct: `/login =name=... =password=...`
  - challenge/response legacy (si `=ret=` est retourné)

Les commandes sont envoyées sous forme de "sentences" (liste de words).
Les réponses sont lues jusqu'à `!done`.
En cas de `!trap` -> ApiException.

Mapping générique:
- Get('ip/firewall/nat') => `/ip/firewall/nat/print`
- Add('ip/firewall/nat', payload) => `/ip/firewall/nat/add`
- Set('ip/firewall/nat/<id>', payload) => `/ip/firewall/nat/set` + `.id`
- Remove('ip/firewall/nat/<id>') => `/ip/firewall/nat/remove` + `.id`
- Command('ip/firewall/nat/move', payload) => `/ip/firewall/nat/move`
