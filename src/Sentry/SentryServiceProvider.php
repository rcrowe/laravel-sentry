<?php

/**
 * Tasty intergration of Laravel & Sentry for sweet reporting of your logs
 *
 * @author Rob Crowe <hello@vivalacrowe.com>
 * @license MIT
 */

namespace rcrowe\Sentry;

use Illuminate\Support\ServiceProvider;
use Raven_Client;

/**
 * Intergrate Sentry into Laravel.
 */
class SentryServiceProvider extends ServiceProvider
{
    /**
     * @var Raven_Client
     */
    protected $sentry;

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app['config']->package('rcrowe/laravel-sentry', __DIR__.'/../config');
    }

    /**
     * @param \rcrowe\Sentry\Log $log
     */
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
        $this->app->instance('log.sentry', $this->sentry);
    }
}
