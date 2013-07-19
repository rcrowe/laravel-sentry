laravel-sentry
==============

Tasty intergration of Laravel &amp; Sentry for sweet reporting of your logs

[![Build Status](https://travis-ci.org/rcrowe/laravel-sentry.png?branch=master)](https://travis-ci.org/rcrowe/laravel-sentry)

Using the same logging functions will send it [Sentry](http://getsentry.com), for example:

```php
Log::error($exception)
```

will send the exception to Sentry. You can control at which level log messages are reported by changing the `level` in the config file. The default `level` is `error`, this means that `Log::info(…)` will not be reported to Sentry.

Installation
============

Add `rcrowe\laravel-sentry` as a requirement to composer.json:

```javascript
{
    "require": {
        "rcrowe/laravel-sentry": "0.1.*"
    }
}
```

Update your packages with `composer update` or install with `composer install`.

Once Composer has installed or updated your packages you need to register Sentry with Laravel itself. Open up app/config/app.php and find the providers key towards the bottom and add:

```php
'rcrowe\Sentry\SentryServiceProvider'
```

Configuration
=============

Sentry configuration file can be extended by creating `app/config/packages/rcrowe/laravel-sentry/config.php`. You can find the default configuration file at vendor/rcrowe/laravel-sentry/src/config/config.php.

You can quickly publish a configuration file by running the following Artisan command.

```
$ php artisan config:publish rcrowe/laravel-sentry
```

Note: Data will only be sent to Sentry if your environment matches the environments defined in the config file.
