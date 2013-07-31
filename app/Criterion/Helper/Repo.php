<?php
namespace Criterion\Helper;

class Repo extends \Criterion\Helper
{
    public static function provider($url)
    {
        if (is_dir($url)) {
            return 'folder';
        }

        if (strpos($url, 'github.com')) {
            return 'github';
        }

        if (strpos($url, 'bitbucket.org')) {
            return 'bitbucket';
        }

        return false;
    }

    public static function cloneType($repo)
    {
        if (strpos($repo, 'https://') !== false) {
            return 'https';
        }

        return 'ssh';
    }

    public static function fetchCommand($test, $project)
    {

        if (is_dir($project->source)) {
            return 'cp -R ' . $project->source . ' ' . (string) $test->id;
        }

        $git_clone = null;
        if (self::cloneType($project->source) === 'ssh') {
            $git_clone = 'export GIT_SSH=' . ROOT . '/bin/git; export PKEY=' .  Project::sshKeyFile($project) . ';';
        }

        return sprintf(
            '%s git clone -b %s --depth=1 %s %s',
            $git_clone,
            $test->branch,
            $project->source,
            (string) $test->id
        );

    }

    public static function username($repo)
    {
        $username = explode('@', $repo);
        $username = str_replace('https://', null, $username[0]);

        return $username ?: false;
    }

    public static function short($url)
    {
        $provider = self::provider($url);

        switch ($provider) {

            case 'folder':
                return $url;
                break;

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
