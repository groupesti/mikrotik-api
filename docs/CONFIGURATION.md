# Configuration

Builder runtime:

```php
$router = \MikroTik\RouterOS::New()
  ->Username('admin')->Password('***')->Host('192.168.88.1')->Port(443)
  ->Transport('rest')->Tls(true)->VerifyTls(false)
  ->Connect();
```

Ports:
- REST: `Port()` (443 par défaut)
- API socket: auto 8729 si TLS, sinon 8728 (ignore Port()).
