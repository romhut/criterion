<?php
namespace Criterion\Helper;

class Test extends \Criterion\Helper
{
    public static function isComposer($folder)
    {
        return file_exists($folder . '/composer.json');
    }
}
