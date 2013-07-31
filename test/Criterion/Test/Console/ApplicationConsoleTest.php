<?php

namespace Criterion\Test\Console;

class ApplicationConsoleTest extends \Criterion\Test\TestCase
{

    public function testInit()
    {
        $app = new \Criterion\Console\Application('Criterion Testing', '0.1');
        $this->assertTrue(is_object($app));
        $this->assertTrue(property_exists($app, 'mongo'));
        $this->assertTrue(property_exists($app, 'app'));
        $this->assertTrue(property_exists($app, 'mongo'));
    }

    public function testSetAndGet()
    {
        $app = new \Criterion\Console\Application('Criterion Testing', '0.1');
        $this->assertTrue(is_object($app));

        $app->testing = 'test';
        $this->assertEquals('test', $app->testing);
    }

    public function testNull()
    {
        $app = new \Criterion\Console\Application('Criterion Testing', '0.1');
        $this->assertTrue(is_object($app));
        $this->assertNull($app->testing);
    }
}
