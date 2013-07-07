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
}
