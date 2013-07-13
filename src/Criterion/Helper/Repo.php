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

    public static function username($repo)
    {
        $username = explode('@', $repo);
        return str_replace('https://', null, $username[0]);
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
