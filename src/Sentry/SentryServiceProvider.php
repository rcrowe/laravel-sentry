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
        // Grab config
        $environments = $this->app['config']->get('laravel-sentry::environments', array('prod', 'production'));
        $dsn          = $this->app['config']->get('laravel-sentry::dsn', '');
        $level        = $this->app['config']->get('laravel-sentry::level', 'error');

        // Running in a valid environment
        $enabled = in_array($this->app['env'], $environments);

        if ($enabled AND !empty($dsn)) {
            // Add the Raven handler to Monolog
            // Log::error(...) will then send a message straight to Sentry
            $sentry = new Log($this->app['log']->getMonolog());
            $sentry->setRaven(new Raven_Client($dsn));
            $sentry->addHandler($level);

            // Store Sentry in the IoC
            // Useful for getting at the Raven client to send custom messages
            $this->app->instance('sentry', $sentry);
        }
    }
}
