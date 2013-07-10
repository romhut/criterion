<?php
namespace Criterion\Helper;

class Test
{
    public static function detectType($folder)
    {
        if (is_dir($folder))
        {
            if (file_exists($folder . '.criterion.yml'))
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

    public static function isComposer($folder)
    {
        return file_exists($folder . 'composer.json');
    }

    public static function isPHPUnit($folder)
    {
        return file_exists($folder . 'phpunit.xml') || file_exists($folder . 'phpunit.xml.dist');
    }
}
