# tiden/telemetry-laravel

Laravel integration for [Tiden](https://tiden.ai) error tracking. Auto-captures
every exception Laravel reports and sends it to your Tiden project. Built on
[`tiden/telemetry-php`](https://github.com/qase-tms/tiden-telemetry-php).

## Install

```bash
composer require tiden/telemetry-laravel
```

The service provider is auto-discovered. Set your DSN:

```dotenv
TIDEN_DSN=http://<publicKey>@<host:ingestPort>/<projectId>
TIDEN_RELEASE=my-app@1.2.3
```

That's it — reported exceptions now appear in your Tiden project. `environment`
defaults to `APP_ENV`.

## Configuration (optional)

```bash
php artisan vendor:publish --tag=tiden-config
```

`config/tiden.php`:

| Key | Env | Default | Description |
|---|---|---|---|
| `dsn` | `TIDEN_DSN` | — | Project DSN. No DSN → the integration is inert. |
| `release` | `TIDEN_RELEASE` | — | App version. |
| `environment` | `TIDEN_ENVIRONMENT` | `APP_ENV` | Deployment environment. |
| `send_default_pii` | `TIDEN_SEND_DEFAULT_PII` | `false` | Send likely-PII (off by default; PII is scrubbed). |

## Manual capture

```php
use Tiden\Sdk;

Sdk::captureException($e);
Sdk::captureMessage('checkout completed', 'info');
```

## License

[MIT](LICENSE)
