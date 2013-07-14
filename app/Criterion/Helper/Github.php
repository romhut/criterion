<?php
namespace Criterion\Helper;

class Github extends \Criterion\Helper
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
        $repo = str_replace('.git', null, $repo);
        return $repo . '/commit/' . $commit['hash']['long'];
    }

    public static function branchUrl($branch, $repo)
    {
        $repo = self::toHTTPSUrl($repo);
        $repo = str_replace('.git', null, $repo);
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
        $app = new \Criterion\Application();
        $shortrepo = self::shortRepo($project->repo);
        $url = 'https://api.github.com/repos/' . $shortrepo . '/statuses/' . $test->commit['hash']['long'];

        $description = array(
            'pending' => 'Tests are running.',
            'success' => 'Tests have passed.',
            'error' => 'Tests have failed.'
        );

        $status = array(
            'state' => $state,
            'target_url' => $app->config['url'] . '/test/' . $test->id,
            'description' => $description[$state]
        );

        $headers = array(
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: token ' . $project->github['token']
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($status));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($status));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);

        return isset($response['url']);
    }
}
