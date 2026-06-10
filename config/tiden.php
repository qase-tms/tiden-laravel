<?php

declare(strict_types=1);

return [
    // Your project DSN: http://<publicKey>@<host:ingestPort>/<projectId>
    'dsn' => env('TIDEN_DSN'),

    // App version, e.g. "my-app@1.2.3".
    'release' => env('TIDEN_RELEASE'),

    // Defaults to the Laravel environment.
    'environment' => env('TIDEN_ENVIRONMENT', env('APP_ENV')),

    // When false (default), likely-PII is scrubbed before sending.
    'send_default_pii' => (bool) env('TIDEN_SEND_DEFAULT_PII', false),
];
