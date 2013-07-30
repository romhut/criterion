<?php
namespace Criterion\Helper;
class Bitbucket extends \Criterion\Helper
{
    public static function toSSHUrl($url)
    {
        $username = Repo::username($url);
        $url = str_replace(array('https://' . $username . '@','.org/'), array('git@','.org:'), $url);

        return $url;
    }

    public static function toHTTPSUrl($url)
    {
        $username = Repo::username($url);
        $url = str_replace(array($username . '@','.org:'), array('https://','.org/'), $url);

        return $url;
    }

    public static function commitUrl($commit, $repo)
    {
        $repo = self::shortRepo($repo);

        return 'https://bitbucket.org/' . $repo . '/commits/' . $commit['hash']['long'];
    }

    public static function branchUrl($branch, $repo)
    {
        $repo = self::shortRepo($repo);

        return 'https://bitbucket.org/' . $repo . '/src?at=' . $branch;
    }

    public static function shortRepo($url)
    {
        $https_url = self::toHTTPSUrl($url);

        return str_replace(array('https://bitbucket.org/','.git'), array(null, null), $https_url);
    }
}
