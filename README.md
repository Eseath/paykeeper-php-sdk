# PayKeeper SDK (Unofficial)

https://docs.paykeeper.ru/

## Installation

Add to composer.json:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/eseath/paykeeper-php-sdk.git"
        }
    ]
}
```

Require the package via composer:

```shell
composer require eseath/paykeeper-sdk
```

## Example for Laravel

config/services.php:

```php
<?php

return [
    // ...

    'paykeeper' => [
        'username' => 'admin',
        'password' => 'password',
        'hostname' => 'your-company.server.paykeeper.ru',
        'testMode' => true,
    ],
];
```

app/Services/AppServiceProvider.php:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PayKeeperClient::class, function (Application $app) {
            $config = $app['config']['services']['paykeeper'];
            return new PayKeeperClient(
                username: $config['username'],
                password: $config['password'],
                hostname: $config['hostname'],
                testMode: $config['testMode'],
            );
        });
    }
}
```

app/Http/Controllers/PaymentController.php:

```php
<?php

class PaymentController
{
    public function __invoke(PayKeeperClient $client)
    {
        $payments = $client->payments->getList(
            start: new DateTime('2024-09-01'),
            end: new DateTime('2024-09-29'),
            statuses: PaymentStatuses::cases(),
            paymentSystemIds: $paymentSystemIds,
        );

        return $payments[0]->id;
    }
}
```
