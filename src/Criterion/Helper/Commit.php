<?php
namespace Criterion\Helper;

class Commit
{
    public static function getURL($commit, $repo)
    {
        if (strpos($repo, 'github.com') !== false)
        {
            return Github::commitUrl($commit, $repo);
        }

        return false;
    }

    public static function getBranchURL($branch, $repo)
    {
        if (strpos($repo, 'github.com') !== false)
        {
            return Github::branchUrl($branch, $repo);
        }

        return false;
    }
}
