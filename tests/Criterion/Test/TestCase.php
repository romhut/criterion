<?php

namespace Criterion\Test;

class TestCase extends \Silex\WebTestCase
{

    public function setUp()
    {
        // Define some core system variables
        if (! defined('ROOT')) {
            define('ROOT', dirname(dirname(dirname(__DIR__))));
            define('CONFIG_FILE', ROOT . '/testing.json');
            define('DATA_DIR', ROOT  . '/data');
            define('TEST_DIR', DATA_DIR . '/tests');
            define('KEY_DIR', DATA_DIR . '/keys');
        }

        parent::setUp();
    }

    public function createApplication()
    {
        putenv('APP_ENV=criterion_test');

        // Establish Application instance
        try {
            $app = new \Criterion\Application();
        } catch (\MongoConnectionException $e) {
            $this->markTestSkipped('Could not connect to MongoDB');
        }

        // Clean all collections
        foreach ($app->db->native->getCollectionNames() as $collection) {
            $app->db->selectCollection($collection)->remove();
        }

    }

}
