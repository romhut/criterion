<?php

namespace Criterion\Test;
use Criterion\Application;
use Criterion\Utils;

class ArraySortTest extends TestCase
{

    public function testAlphaOnlySorting()
    {
        $array = array(
            array('key' => 'b'),
            array('key' => 'a'),
            array('key' => 'd'),
            array('key' => 'c'),
        );
        Utils::array_sort($array, 'key');
        $this->assertEquals($array, array(
            array('key' => 'a'),
            array('key' => 'b'),
            array('key' => 'c'),
            array('key' => 'd'),
        ));
    }

    public function testAlphaOnlySortingDesc()
    {
        $array = array(
            array('key' => 'b'),
            array('key' => 'a'),
            array('key' => 'd'),
            array('key' => 'c'),
        );
        Utils::array_sort($array, '!key');
        $this->assertEquals($array, array(
            array('key' => 'd'),
            array('key' => 'c'),
            array('key' => 'b'),
            array('key' => 'a'),
        ));
    }

}
