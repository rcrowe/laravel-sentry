<?php

namespace rcrowe\Sentry;

use Illuminate\Support\ServiceProvider;

class SentryServiceProvider extends ServiceProvider
{
    public function register()
    {
    }

    public function boot()
    {
        $sentry = new Log($this->app['log']->getMonolog(), $this->app['events']);

        $dsn   = '';
        $level = 'error';

        $sentry->setHandler($dsn, $level);
    }
}
