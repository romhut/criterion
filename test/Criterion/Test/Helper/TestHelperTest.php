<?php

namespace Criterion\Test\Helper;

class TestHelperTest extends \Criterion\Test\TestCase
{
    public function testDetectType()
    {
        $type = \Criterion\Helper\Test::detectType(ROOT);
        $this->assertEquals('criterion', $type);
    }

    public function testDetectTypeSilex()
    {
        $type = \Criterion\Helper\Test::detectType(ROOT . '/vendor/silex/silex');
        $this->assertEquals('phpunit', $type);
    }

    public function testDetectTypeFail()
    {
        $type = \Criterion\Helper\Test::detectType(ROOT . '/vendor/silex');
        $this->assertFalse($type);
    }

    public function testIsCriterion()
    {
        $type = \Criterion\Helper\Test::isCriterion(ROOT);
        $this->assertTrue($type);
    }

    public function testIsComposer()
    {
        $type = \Criterion\Helper\Test::isComposer(ROOT);
        $this->assertTrue($type);
    }

    public function testIsPHPUnit()
    {
        $type = \Criterion\Helper\Test::isPHPUnit(ROOT);
        $this->assertTrue($type);
    }
}
