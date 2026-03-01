# REST Transport

Transport REST via cURL (sans dépendance additionnelle).
- Base path: `/rest`
- Auth: Basic
- TLS: optionnel, `VerifyTls(false)` pour certs self-signed.

Actions standard:
- Get() => GET /rest/<path>
- Add() => PUT /rest/<path>
- Set(id) => PATCH /rest/<path>/<id>
- Remove(id) => DELETE /rest/<path>/<id>
- Command(name) => POST /rest/<path>/<name>
- Print()->Query()->Proplist()->Get() => POST /rest/<path>/print
