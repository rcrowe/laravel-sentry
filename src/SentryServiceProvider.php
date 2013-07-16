<?php

namespace rcrowe\Sentry;

use Illuminate\Support\ServiceProvider;
use Raven_Client;

class SentryServiceProvider extends ServiceProvider
{
    public function register()
    {
        // TODO: Register config file
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException Thrown with an invalid Sentry DSN.
     */
    public function boot()
    {
        // TODO: Grab these values from a config file
        $environments = array('prod', 'production');
        $dsn          = 'DSN_GOES_HERE';
        $level        = 'error';

        $enabled = in_array($this->app['env'], $environments);

        if ($enabled) {
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
