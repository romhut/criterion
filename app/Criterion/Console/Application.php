<?php
namespace Criterion\Console;

use Symfony\Component\Console\Application as SymfonyApplication;

class Application extends SymfonyApplication
{

    public $app;

    public function __construct($name, $version)
    {
        parent::__construct($name, $version);
        $this->app = new \Criterion\Application();
    }
}
