<?php
namespace Criterion;
class Application
{
    public static $app;

    public $config = false;
    public $config_file = false;
    public $mongo = false;
    public $db = false;

    public function __construct()
    {
        $this->config_file = dirname(dirname(__DIR__)) . '/config.json';

        if (file_exists($this->config_file))
        {
            $this->config = json_decode(file_get_contents($this->config_file), true);
            
            if(empty($this->config))
                return false;
        }

        $db = getenv('APP_ENV') === 'testing' ? 'criterion_test' : $this->config['mongo']['database'];

        try
        {
            $this->mongo = new \MongoMinify\Client($this->config['mongo']['server']);
        }
        catch (\MongoConnectionException $e)
        {
            throw new \Exception('Could not connect to Mongo. Try running the installer again.');
        }

        $this->db = $this->mongo->{$db};

        return $this;
    }
}