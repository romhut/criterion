<?php

namespace Criterion\Test\Console;

class ApplicationConsoleTest extends \Criterion\Test\TestCase
{

    public function testInit()
    {
        $app = new \Criterion\Console\Application('Criterion Testing', '0.1');
        $this->assertTrue(is_object($app));
    }
}
