<?php

namespace Criterion\Test;

class TestCase extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        putenv('APP_ENV=testing');
    }

}
