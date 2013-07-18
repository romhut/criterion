<?php

namespace Criterion\Test\Helper;

class TestHelperTest extends \Criterion\Test\TestCase
{
    public function testDetectType()
    {
        $dir = dirname(dirname(dirname(dirname(__DIR__))));
        $type = \Criterion\Helper\Test::detectType($dir);
        $this->assertEquals('criterion', $type);
    }

    public function testIsCriterion()
    {
        $dir = dirname(dirname(dirname(dirname(__DIR__))));
        $type = \Criterion\Helper\Test::isCriterion($dir);
        $this->assertTrue($type);
    }

    public function testIsComposer()
    {
        $dir = dirname(dirname(dirname(dirname(__DIR__))));
        $type = \Criterion\Helper\Test::isComposer($dir);
        $this->assertTrue($type);
    }

    public function testIsPHPUnit()
    {
        $dir = dirname(dirname(dirname(dirname(__DIR__))));
        $type = \Criterion\Helper\Test::isPHPUnit($dir);
        $this->assertTrue($type);
    }
}
