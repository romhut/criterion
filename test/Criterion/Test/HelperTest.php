<?php

namespace Criterion\Test;

class HelperTest extends TestCase
{

    public function testInit()
    {
        $helper = new \Criterion\Helper();
        $this->assertTrue(is_object($helper));
    }
}
