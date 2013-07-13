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

        if (strpos($url, 'bitbucket.org'))
        {
            return 'bitbucket';
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
        $provider = self::provider($url);

        switch ($provider) {
            case 'github':
                return Github::shortRepo($url);
                break;

            case 'bitbucket':
                return Bitbucket::shortRepo($url);
                break;

            default:
                return $url;
                break;
        }
    }
}
