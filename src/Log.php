<?php

namespace rcrowe\Sentry;

use Illuminate\Log\Writer;
use Raven_Client;
use Monolog\Handler\RavenHandler;

class Log extends Writer
{
    public function setHandler($dsn, $level = 'error')
    {
        // Convert Laravel log level into a Monolog level
        $level = $this->parseLevel($level);

        // Create raven client
        $raven = new Raven_Client($dsn);

        // Create Monolog Sentry handler
        $handler = new RavenHandler($raven, $level);

        // Set handler
        $this->getMonolog()->pushHandler($handler);
    }
}
