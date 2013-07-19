<?php

namespace rcrowe\Sentry\Tests;

use PHPUnit_Framework_TestCase;
use Mockery as m;
use Illuminate\Foundation\Application;
use Illuminate\Config\Repository;
use rcrowe\Sentry\Log;
use RuntimeException;

class LogTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testExtendsWriter()
    {
        $app = new Application;
        $app['sentry.test'] = 'hello.world';

        // Monolog
        $log = m::mock('Illuminate\Log\Writer');
        $log->shouldReceive('getMonolog')->andReturn(m::mock('Monolog\Logger'));
        $app['log'] = $log;

        $log = new Log($app);

        $this->assertEquals(get_class($log), 'rcrowe\Sentry\Log');
        $this->assertTrue(is_a($log, 'Illuminate\Log\Writer'));

        $this->assertEquals($log->getApp()->make('sentry.test'), 'hello.world');
    }

    public function testSetApp()
    {
        $app = new Application;

        // Monolog
        $log = m::mock('Illuminate\Log\Writer');
        $log->shouldReceive('getMonolog')->andReturn(m::mock('Monolog\Logger'));
        $app['log'] = $log;

        $log = new Log($app);
        $this->assertFalse($log->getApp()->bound('sentry.test'));

        $app['sentry.test'] = 'foo.bar';
        $log->setApp($app);
        $this->assertTrue($log->getApp()->bound('sentry.test'));
    }

    public function testGetEnvironments()
    {
        $app = new Application;

        // Monolog
        $log = m::mock('Illuminate\Log\Writer');
        $log->shouldReceive('getMonolog')->andReturn(m::mock('Monolog\Logger'));
        $app['log'] = $log;

        // Config
        $config = new Repository(m::mock('Illuminate\Config\LoaderInterface'), 'production');

        $config->getLoader()->shouldReceive('addNamespace')->with('laravel-sentry', __DIR__);
        $config->getLoader()->shouldReceive('cascadePackage')->andReturnUsing(function($env, $package, $group, $items) { return $items; });
        $config->getLoader()->shouldReceive('exists')->with('environments', 'laravel-sentry')->andReturn(false);
        $config->getLoader()->shouldReceive('load')->with('production', 'config', 'laravel-sentry')->andReturn(
            array(
                'environments' => array('prod', 'production', 'testing'),
            )
        );
        $config->package('foo/laravel-sentry', __DIR__);
        $app['config'] = $config;

        $log = new Log($app);
        $environments = $log->getEnvironments();

        $this->assertEquals(count($environments), 3);
        $this->assertEquals($environments[2], 'testing');
    }

    public function testGetDsn()
    {
        $test_dsn = 'http://abc:123@getsentry.com/foobar';
        $app = new Application;

        // Monolog
        $log = m::mock('Illuminate\Log\Writer');
        $log->shouldReceive('getMonolog')->andReturn(m::mock('Monolog\Logger'));
        $app['log'] = $log;

        // Config
        $config = new Repository(m::mock('Illuminate\Config\LoaderInterface'), 'production');

        $config->getLoader()->shouldReceive('addNamespace')->with('laravel-sentry', __DIR__);
        $config->getLoader()->shouldReceive('cascadePackage')->andReturnUsing(function($env, $package, $group, $items) { return $items; });
        $config->getLoader()->shouldReceive('exists')->with('dsn', 'laravel-sentry')->andReturn(false);
        $config->getLoader()->shouldReceive('load')->with('production', 'config', 'laravel-sentry')->andReturn(
            array(
                'dsn' => $test_dsn,
            )
        );
        $config->package('foo/laravel-sentry', __DIR__);
        $app['config'] = $config;

        $log = new Log($app);
        $dsn = $log->getDsn();

        $this->assertEquals($dsn, $test_dsn);
    }

    public function testGetLevel()
    {
        $app = new Application;

        // Monolog
        $log = m::mock('Illuminate\Log\Writer');
        $log->shouldReceive('getMonolog')->andReturn(m::mock('Monolog\Logger'));
        $app['log'] = $log;

        // Config
        $config = new Repository(m::mock('Illuminate\Config\LoaderInterface'), 'production');

        $config->getLoader()->shouldReceive('addNamespace')->with('laravel-sentry', __DIR__);
        $config->getLoader()->shouldReceive('cascadePackage')->andReturnUsing(function($env, $package, $group, $items) { return $items; });
        $config->getLoader()->shouldReceive('exists')->with('level', 'laravel-sentry')->andReturn(false);
        $config->getLoader()->shouldReceive('load')->with('production', 'config', 'laravel-sentry')->andReturn(
            array(
                'level' => 'info',
            )
        );
        $config->package('foo/laravel-sentry', __DIR__);
        $app['config'] = $config;

        $log = new Log($app);
        $level = $log->getLevel();

        $this->assertEquals($level, 'info');
    }

    public function testSetRaven()
    {
        $app = new Application;

        // Monolog
        $log = m::mock('Illuminate\Log\Writer');
        $log->shouldReceive('getMonolog')->andReturn(m::mock('Monolog\Logger'));
        $app['log'] = $log;

        $log = new Log($app);
        $this->assertNull($log->getRaven());

        // Raven
        $raven = m::mock('Raven_Client');
        $raven->shouldReceive('getFoo')->andReturn('BAR');
        $log->setRaven($raven);

        $this->assertNotNull($log->getRaven());
        $this->assertEquals($log->getRaven()->getFoo(), 'BAR');
    }

    public function testNotEnabled()
    {
        $app = new Application;

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
        $config->getLoader()->shouldReceive('load')->with('production', 'config', 'laravel-sentry')->andReturn(
            array(
                'environments' => array('prod', 'production'),
                'dsn'          => '',
            )
        );
        $config->package('foo/laravel-sentry', __DIR__);
        $app['config'] = $config;


        $app['env'] = 'testing';
        $log = new Log($app);

        $this->assertFalse($log->isEnabled());
    }

    public function testIsEnabled()
    {
        $app = new Application;

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
        $config->getLoader()->shouldReceive('load')->with('production', 'config', 'laravel-sentry')->andReturn(
            array(
                'environments' => array('prod', 'production'),
                'dsn'          => 'http://example.com',
            )
        );
        $config->package('foo/laravel-sentry', __DIR__);
        $app['config'] = $config;


        $app['env'] = 'production';
        $log = new Log($app);

        $this->assertTrue($log->isEnabled());
    }

    public function testAddHandlerNotEnabled()
    {
        $app = new Application;

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
        $config->getLoader()->shouldReceive('load')->with('production', 'config', 'laravel-sentry')->andReturn(
            array(
                'environments' => array('prod', 'production'),
                'dsn'          => '',
            )
        );
        $config->package('foo/laravel-sentry', __DIR__);
        $app['config'] = $config;


        $app['env'] = 'testing';
        $log = new Log($app);

        $this->assertFalse($log->addHandler());
    }

    public function testRavenNotSet()
    {
        $app = new Application;

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
        $config->getLoader()->shouldReceive('load')->with('production', 'config', 'laravel-sentry')->andReturn(
            array(
                'environments' => array('prod', 'production'),
                'dsn'          => 'http://example.com',
            )
        );
        $config->package('foo/laravel-sentry', __DIR__);
        $app['config'] = $config;


        $app['env'] = 'production';
        $log = new Log($app);

        try {
            $log->addHandler();
            $this->assertFalse(true);
        } catch (RuntimeException $ex) {
            $this->assertEquals($ex->getMessage(), 'Raven client not set');
        }
    }

    public function testAddHandlerEnabled()
    {
        $app = new Application;

        // Monolog
        $log = m::mock('Illuminate\Log\Writer');
        $monolog = m::mock('Monolog\Logger');
        $monolog->shouldReceive('pushHandler')->once();
        $log->shouldReceive('getMonolog')->andReturn($monolog);
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
                'dsn'          => 'http://example.com',
                'level'        => 'error',
            )
        );
        $config->package('foo/laravel-sentry', __DIR__);
        $app['config'] = $config;


        $app['env'] = 'production';
        $log = new Log($app);

        // Raven
        $raven = m::mock('Raven_Client');
        $raven->shouldReceive('getFoo')->andReturn('BAR');
        $log->setRaven($raven);

        $log->addHandler();
    }
}
