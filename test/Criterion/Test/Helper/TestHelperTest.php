<?php

namespace Criterion\Test\Helper;

class TestHelperTest extends \Criterion\Test\TestCase
{
    public function testIsComposer()
    {
        $type = \Criterion\Helper\Test::isComposer(ROOT);
        $this->assertTrue($type);
    }
}
