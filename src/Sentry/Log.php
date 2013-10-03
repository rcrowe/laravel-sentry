<?php

/**
 * Tasty intergration of Laravel & Sentry for sweet reporting of your logs
 *
 * @author Rob Crowe <hello@vivalacrowe.com>
 * @license MIT
 */

namespace rcrowe\Sentry;

use Illuminate\Log\Writer;
use Illuminate\Foundation\Application;
use Monolog\Handler\RavenHandler;
use Raven_Client;
use RuntimeException;

class Log extends Writer
{
    /**
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * @var Raven_Client
     */
    protected $raven;

    /**
     * Create a new instance.
     *
     * @param Illuminate\Foundation\Application $app
     */
    public function __construct(Application $app = null)
    {
        parent::__construct($app['log']->getMonolog());

        $this->setApp($app);
    }

    /**
     * Set the Laravel app.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    public function setApp(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Get the Laravel app.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * Get the environments where we are allowed to report to Sentry in.
     *
     * @return array
     */
    public function getEnvironments()
    {
        return $this->app['config']->get('laravel-sentry::environments', array('prod', 'production'));
    }

    /**
     * Get the Sentry / Raven DSN.
     *
     * @return string
     */
    public function getDsn()
    {
        return $this->app['config']->get('laravel-sentry::dsn', '');
    }

    /**
     * Get the level at which we send to Sentry.
     *
     * @return string
     */
    public function getLevel()
    {
        return $this->app['config']->get('laravel-sentry::level', 'error');
    }

    /**
     * Set the Raven client used to send the message to Sentry.
     *
     * @param Raven_Client $client
     */
    public function setRaven(Raven_Client $client)
    {
        $this->raven = $client;
    }

    /**
     * Get the Raven client used to send logs to Sentry.
     *
     * @return Raven_Client
     */
    public function getRaven()
    {
        return $this->raven;
    }

    /**
     * Is this library enabled and ok to add Sentry handler to Monolog.
     *
     * @return bool
     */
    public function isEnabled()
    {
        $current_env_valid = in_array($this->app['env'], $this->getEnvironments());
        $valid_dsn         = (strlen($this->getDsn()) > 0);

        return ($current_env_valid AND $valid_dsn);
    }

    /**
     * Add Raven handler to Monolog.
     *
     * @param string $level Level at which we should send to Sentry.
     * @throws RuntimeException Raven client not set.
     * @return bool Whether the handler was added.
     */
    public function addHandler()
    {
        if (!$this->isEnabled()) {
            return false;
        }

        // Make sure client has been set
        $client = $this->getRaven();

        if (empty($client)) {
            throw new RuntimeException('Raven client not set');
        }

        // Convert Laravel log level into a Monolog level
        $level = $this->parseLevel($this->getLevel());

        // Create Monolog Sentry handler
        $handler = new RavenHandler($client, $level);

        // Add handler
        $this->getMonolog()->pushHandler($handler);

        return true;
    }
}
