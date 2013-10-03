<?php

namespace rcrowe\Sentry\Tests;

use PHPUnit_Framework_TestCase;
use Mockery as m;
use Illuminate\Foundation\Application;
use Illuminate\Config\Repository;
use rcrowe\Sentry\SentryServiceProvider;

class ProviderTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testHandlerAdd()
    {
        $provider = new SentryServiceProvider($this->getApplication());

        $sentry = m::mock('rcrowe\Sentry\Log');
        $sentry->shouldReceive('getDsn')->once();
        $sentry->shouldReceive('setRaven')->once()->with(m::type('Raven_Client'));
        $sentry->shouldReceive('addHandler')->once();

        $provider->setSentry($sentry);

        $provider->boot();
    }

    public function testSentryBound()
    {
        $app = $this->getApplication();

        $this->assertFalse($app->bound('log.sentry'));

        $provider = new SentryServiceProvider($app);
        $provider->boot();

        $this->assertTrue($app->bound('log.sentry'));
        $this->assertEquals(get_class($app['log.sentry']), 'rcrowe\Sentry\Log');
    }

    public function getApplication()
    {
        $app = new Application;
        $app->instance('path', __DIR__);

        $app['path.storage'] = __DIR__.'/storage';

        // Monolog
        $log = m::mock('Illuminate\Log\Writer');
        $log->shouldReceive('getMonolog')->andReturn(m::mock('Monolog\Logger'));
        $app['log'] = $log;

        // Config
        $config = new Repository(m::mock('Illuminate\Config\LoaderInterface'), 'production');

        $config->getLoader()->shouldReceive('addNamespace')->with('laravel-sentry', __DIR__);
        $config->getLoader()->shouldReceive('cascadePackage')->andReturnUsing(function($env, $package, $group, $items) { return $items; });
        $config->getLoader()->shouldReceive('exists')->with('environments', 'laravel-sentry')->andReturn(false);
        $config->getLoader()->shouldReceive('exists')->with('dsn', 'laravel-sentry')->andReturn(false);
        $config->getLoader()->shouldReceive('exists')->with('level', 'laravel-sentry')->andReturn(false);
        $config->getLoader()->shouldReceive('load')->with('production', 'config', 'laravel-sentry')->andReturn(
            array(
                'environments' => array('prod', 'production'),
                'dsn'          => '',
                'level'        => 'error',
            )
        );
        $config->package('foo/laravel-sentry', __DIR__);
        $app['config'] = $config;

        // Env
        $app['env'] = 'production';

        return $app;
    }
}
