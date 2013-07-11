<?php
namespace Criterion\Helper;

class Commit
{
    public static function getURL($commit, $repo)
    {
        if (Repo::provider($repo) == 'github')
        {
            return Github::commitUrl($commit, $repo);
        }

        return false;
    }

    public static function getBranchURL($branch, $repo)
    {
        if (Repo::provider($repo) == 'github')
        {
            return Github::branchUrl($branch, $repo);
        }

        return false;
    }

    public static function getInfo($repo, $branch = 'master')
    {
        $commit = array();

        exec("git --no-pager show -s --format='%h'", $short_hash);
        $commit['hash']['short'] = $short_hash[0];

        exec("git --no-pager show -s --format='%H'", $long_hash);
        $commit['hash']['long'] = $long_hash[0];

        exec("git --no-pager show -s --format='%an' " . $commit['hash']['long'], $author_name);
        $commit['author']['name'] = $author_name[0];

        exec("git --no-pager show -s --format='%ae' " . $commit['hash']['long'], $author_email);
        $commit['author']['email'] = $author_email[0];

        exec("git --no-pager show -s --format='%s' " . $commit['hash']['long'], $message);
        $commit['message'] = $message[0];

        exec("git show --format='%ci' " . $commit['hash']['long'], $date);
        $commit['date'] = new \MongoDate(strtotime($date[0]));

        $commit['url'] = self::getUrl($commit, $repo);

        $commit['branch']['name'] = $branch;
        $commit['branch']['url'] = self::getBranchUrl($branch, $repo);

        return $commit;
    }
}
