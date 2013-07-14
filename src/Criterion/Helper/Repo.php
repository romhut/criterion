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

    public static function cloneType($repo)
    {
        if (strpos($repo, 'https://'))
        {
            return 'https';
        }

        return 'ssh';
    }

    public static function cloneCommand($test, $project)
    {
        $git_clone = null;
        if (self::cloneType($project['repo']) === 'ssh')
        {
            $git_clone = 'export GIT_SSH=' . ROOT . '/bin/git; export PKEY=' .  Project::sshKeyFile($project) . ';';
        }

        return sprintf(
            '%s git clone -b %s --depth=1 %s %s',
            $git_clone,
            $test['branch'],
            $project['repo'],
            (string) $test['_id']
        );

    }

    public static function username($repo)
    {
        $username = explode('@', $repo);
        $username = str_replace('https://', null, $username[0]);

        if ( ! $username)
        {
            $username = 'git';
        }

        return $username;
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
