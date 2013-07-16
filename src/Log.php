<?php

namespace rcrowe\Sentry;

use Illuminate\Log\Writer;
use Monolog\Handler\RavenHandler;
use Raven_Client;
use RuntimeException;

class Log extends Writer
{
    /**
     * @var Raven_Client
     */
    protected $raven;

    /**
     * Set the Raven client used to send the message to Sentry.
     *
     * @param Raven_Client $client
     *
     * @return void
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
     * Add Raven handler to Monolog.
     *
     * @param string $level Level at which we should send to Sentry.
     *
     * @return void
     */
    public function addHandler($level = 'error')
    {
        // Make sure client has been set
        $client = $this->getRaven();

        if (empty($client)) {
            throw new RuntimeException('Raven client not set');
        }

        // Convert Laravel log level into a Monolog level
        $level = $this->parseLevel($level);

        // Create Monolog Sentry handler
        $handler = new RavenHandler($client, $level);

        // Set handler
        $this->getMonolog()->pushHandler($handler);
    }
}
