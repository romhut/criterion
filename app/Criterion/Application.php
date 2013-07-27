<?php

namespace Criterion;
use Criterion\Exception\ConfigurationException;

class Application
{
    public static $app;

    public $config = false;
    public $config_file = false;
    public $mongo = false;
    public $db = false;

    public function __construct()
    {

        $this->config = array(
            'url' => 'http://criterion.example.com',
            'mongo' => array(
                'server' => 'mongodb://127.0.0.1',
                'database' => 'criterion',
                'options' => array(
                    'connect' => true
                ),
            ),
            'visibility' => 'private',
            'email' => array(
                'name' => 'Criterion Notifications',
                'address' => 'mail@localhost',
            )
        );

        // Load configuration if file exists
        $this->config_file = dirname(dirname(__DIR__)) . '/config.json';
        if (file_exists($this->config_file)) {
            $raw = file_get_contents($this->config_file);
            if ($raw) {
                $this->config = json_decode($raw, true);
                if (! $this->config) {
                    throw new ConfigurationException('Could not parse config file');
                }
            }
        }

        // Exit if the config is corrupt
        if (empty($this->config)) {
            return false;
        }

        // Get a mongo client instance
        try {
            $this->mongo = new \MongoMinify\Client($this->config['mongo']['server']);
        } catch (\MongoConnectionException $e) {
            throw new \Exception('Could not connect to Mongo. Try running the installer again.');
        }

        // Load a database
        try {
            $db = getenv('APP_ENV') === 'testing' ? 'criterion_test' : $this->config['mongo']['database'];
            $this->db = $this->mongo->selectDb($db);
        } catch (\Exception $e) {
            throw new \Exception('Invalid Database. [' . $db . ']');
        }

    }
}
