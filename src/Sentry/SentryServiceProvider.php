<?php

namespace rcrowe\Sentry;

use Illuminate\Support\ServiceProvider;
use Raven_Client;

class SentryServiceProvider extends ServiceProvider
{
    protected $sentry;

    public function register()
    {
        $this->app['config']->package('rcrowe/laravel-sentry', __DIR__.'/../config');
    }

    public function setSentry(Log $log)
    {
        $this->sentry = $log;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException Thrown with an invalid Sentry DSN.
     */
    public function boot()
    {
        $this->sentry OR $this->sentry = new Log($this->app);

        // Set cleint to send to Sentry
        $this->sentry->setRaven( new Raven_Client($this->sentry->getDsn()) );

        // If enabled add Sentry handler to Monolog
        $this->sentry->addHandler();

        // Store Sentry in the IoC
        // Useful for getting at the Raven client to send custom messages
        // @see rcrowe\Sentry\Log::getRaven()
        $this->app->instance('sentry', $this->sentry);
    }
}
