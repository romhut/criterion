<?php

namespace Criterion\Test;

class TestCase extends \Silex\WebTestCase
{
    public function createApplication()
    {
        if (! defined('ROOT')) {
            define('ROOT', dirname(dirname(dirname(__DIR__))));
        }

        putenv('APP_ENV=testing');
    }
}
