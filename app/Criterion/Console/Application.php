<?php
namespace Criterion\Console;

use Symfony\Component\Console\Application as SymfonyApplication;

class Application extends SymfonyApplication
{
    public $mongo = null;
    public $db = array();
    public $app = array();
    public $criterion = array();

    public $data = array();

    public function __construct($name, $version)
    {
        parent::__construct($name, $version);
        $this->app = new \Criterion\Application();
        $this->mongo = $this->app->mongo;
        $this->db = $this->app->db;
    }

    public function __set($key, $val)
    {
        $this->data[$key] = $val;
    }

    public function __get($key)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        return null;
    }
}
