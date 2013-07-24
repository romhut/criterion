<?php

namespace Criterion\Test;

class TestCase extends \Silex\WebTestCase
{

    public function createApplication()
    {

        putenv('APP_ENV=testing');

        // Establish Application instance
        try {
            $app = new \Criterion\Application();
        } catch (\MongoConnectionException $e) {
            $this->markTestSkipped('Could not connect to MongoDB');
        }
        $app->db->drop();

        // Define some core system variables
        if (! defined('ROOT')) {
            define('ROOT', dirname(dirname(dirname(__DIR__))));
            define('CONFIG_FILE', ROOT . '/config.json');
            define('DATA_DIR', ROOT  . '/data');
            define('TEST_DIR', DATA_DIR . '/tests');
            define('KEY_DIR', DATA_DIR . '/keys');
        }
    }

}
