<?php

namespace Criterion\Test\Helper;

class HelperTest extends \Criterion\Test\TestCase
{

    public function testInit()
    {
        $helper = new \Criterion\Helper();
        $this->assertTrue($helper instanceof \Criterion\Helper);
    }
}
