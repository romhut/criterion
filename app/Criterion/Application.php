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
        }

        $this->mongo = new \MongoMinify\Client();
        $this->db = $this->mongo->criterion;
        return $this;
    }
}