<?php
namespace Criterion\Helper;

class Test extends \Criterion\Helper
{
    public static function detectType($folder)
    {
        if (is_dir($folder))
        {
            if (self::isCriterion($folder))
            {
                return 'criterion';
            }
            elseif (self::isPHPUnit($folder))
            {
                return 'phpunit';
            }
        }

        return false;
    }

    public static function isCriterion($folder)
    {
        return file_exists($folder . '/.criterion.yml');
    }

    public static function isComposer($folder)
    {
        return file_exists($folder . '/composer.json');
    }

    public static function isPHPUnit($folder)
    {
        return file_exists($folder . '/phpunit.xml') || file_exists($folder . '/phpunit.xml.dist');
    }
}
