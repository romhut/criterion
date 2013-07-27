<?php

namespace Criterion\Test;
use Criterion\Application;

class ConfigTest extends TestCase
{

    public function tearDown()
    {
        if (file_exists(CONFIG_FILE)) {
            unlink(CONFIG_FILE);
        }
    }

    public function testDefaultConfig()
    {
        $app = new Application();
        $this->assertFalse(empty($app->config), 'No default config defined');
        $this->assertArrayHasKey('mongo', $app->config);
        $this->assertEquals($app->config['mongo']['server'], 'mongodb://127.0.0.1');
        $this->assertEquals($app->config['mongo']['database'], 'criterion');
    }

    public function testConfigFile()
    {

        // Write fake config
        $config = array(
            'mongo' => array(
                'server' => 'mongodb://localhost'
            )
        );
        file_put_contents(CONFIG_FILE, json_encode($config));

        // Initialize application
        $app = new Application();
        $this->assertArrayHasKey('mongo', $app->config);
        $this->assertEquals($app->config['mongo']['server'], 'mongodb://localhost');
        $this->assertEquals($app->config['mongo']['database'], 'criterion');
    }

}
