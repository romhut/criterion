<?php
namespace Criterion\Helper;

class Repo
{
    public static function provider($url)
    {
        if (strpos($url, 'github.com'))
        {
            return 'github';
        }

        return false;
    }

    public static function short($url)
    {
        if (self::provider($url) == 'github')
        {
            return Github::shortRepo($url);
        }

        return $url;
    }
}
