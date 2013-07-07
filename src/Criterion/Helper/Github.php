<?php
namespace Criterion\Helper;
class Github
{
    public static function toSSHUrl($url)
    {
        $url = str_replace(array('https://','.com/'), array('git@','.com:'), $url);
        return $url;
    }

    public static function toHTTPSUrl($url)
    {
        $url = str_replace(array('git@','.com:'), array('https://','.com/'), $url);
        return $url;
    }

    public static function commitUrl($commit, $repo)
    {
        $repo = self::toHTTPSUrl($repo);
        return $repo . '/commit/' . $commit['hash']['long'];
    }

    public static function branchUrl($branch, $repo)
    {
        $repo = self::toHTTPSUrl($repo);
        return $repo . '/tree/' . $branch;
    }

    public static function shortRepo($url)
    {
        $https_url = self::toHTTPSUrl($url);
        return str_replace('https://github.com/', null, $https_url);
    }
}
