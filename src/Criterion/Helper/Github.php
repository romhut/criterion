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
        $https_url = str_replace('.git', null, $https_url);
        return str_replace('https://github.com/', null, $https_url);
    }

    public static function updateStatus($state, $target_url, $repo, $hash)
    {
        $shortrepo = self::shortRepo($repo);
        $url = 'https://api.github.com/repos/' . $shortrepo . '/statuses/' . $hash;

        $description = array(
            'pending' => 'Test is pending',
            'success' => 'Test has passed ',
            'error' => 'Test has failed'
        );

        $status = array(
            'state' => $state,
            'target_url' => $target_url,
            'description' => $description[$state]
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($status));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($status));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
        return true;
    }
}
