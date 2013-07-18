<?php

namespace rcrowe\Sentry;

use Illuminate\Support\ServiceProvider;
use Raven_Client;

class SentryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app['config']->package('rcrowe/laravel-sentry', __DIR__.'/../config');
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException Thrown with an invalid Sentry DSN.
     */
    public function boot()
    {
        $sentry = new Log($this->app);

        // Set cleint to send to Sentry
        $sentry->setRaven( new Raven_Client($sentry->getDsn()) );

        // If enabled add Sentry handler to Monolog
        $sentry->addHandler();

        // Store Sentry in the IoC
        // Useful for getting at the Raven client to send custom messages
        // @see rcrowe\Sentry\Log::getRaven()
        $this->app->instance('sentry', $sentry);
    }
}
