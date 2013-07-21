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

    public function testPreLog()
    {
        $app = new \Criterion\Console\Application('Criterion Testing', '0.1');

        $project = new \Criterion\Model\Project();
        $test = new \Criterion\Model\Test();

        $app->project = $project;
        $app->test = $test;

        $log = $app->preLog('echo "hi";');
        $this->assertTrue(is_object($log));
    }

    public function testLog()
    {
        $app = new \Criterion\Console\Application('Criterion Testing', '0.1');

        $project = new \Criterion\Model\Project();
        $test = new \Criterion\Model\Test();

        $app->project = $project;
        $app->test = $test;

        $log = $app->log('echo "hi";', 'hello');
        $this->assertTrue(is_object($log));
        $this->assertNotNull($log->id);
    }

    public function testParseConfig()
    {
        $config = ROOT . '/.criterion.yml';
        $app = new \Criterion\Console\Application('Criterion Testing', '0.1');

        $project = new \Criterion\Model\Project();
        $test = new \Criterion\Model\Test();

        $app->project = $project;
        $app->test = $test;

        $parse = $app->parseConfig($config);

        $this->assertArrayHasKey('setup', $parse);
        $this->assertArrayHasKey('script', $parse);
        $this->assertArrayHasKey('pass', $parse);
        $this->assertArrayHasKey('fail', $parse);
    }

    public function testParseConfigFail()
    {
        $config = ROOT . '/.criterionfail.yml';
        $app = new \Criterion\Console\Application('Criterion Testing', '0.1');

        $project = new \Criterion\Model\Project();
        $test = new \Criterion\Model\Test();

        $app->project = $project;
        $app->test = $test;

        $parse = $app->parseConfig($config);

        $this->assertFalse($parse);
    }

}
