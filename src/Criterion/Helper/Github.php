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

    public static function updateStatus($state, $test, $project)
    {
        if ( ! file_exists(CONFIG_FILE))
        {
            return false;
        }

        $config = json_decode(file_get_contents(CONFIG_FILE));

        $shortrepo = self::shortRepo($project['repo']);
        $url = 'https://api.github.com/repos/' . $shortrepo . '/statuses/' . $test['commit']['hash']['long'];

        $description = array(
            'pending' => 'Tests are pending.',
            'success' => 'Tests has passed.',
            'error' => 'Tests has failed.'
        );

        $status = array(
            'state' => $state,
            'target_url' => $config->url . '/test/' . $test['_id'],
            'description' => $description[$state]
        );

        $headers = array(
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: token ' . $project['github']['token']
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($status));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($status));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        curl_close($ch);

        return true;
    }
}
