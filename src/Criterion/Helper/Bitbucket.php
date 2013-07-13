<?php
namespace Criterion\Helper;
class Bitbucket
{
    public static function toSSHUrl($url)
    {
        $username = Repo::username($url);
        $url = str_replace(array('https://','.org/'), array($username . '@','.org:'), $url);
        return $url;
    }

    public static function toHTTPSUrl($url)
    {
        $username = Repo::username($url);
        $url = str_replace(array($username . '@','.org:'), array('https://' . $username . '@','.org/'), $url);
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
        $https_url = explode('@bitbucket.org', $https_url);
        $https_url = str_replace('.git', null, $https_url[1]);

        return substr($https_url, 1);
    }
}
