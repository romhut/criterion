<?php

namespace Criterion\Test\Helper;

class CommitHelperTest extends \Criterion\Test\TestCase
{
    public function testCommitUrl()
    {
        $repo = 'git@github.com:romhut/criterion';
        $commit = array(
            'hash' => array(
                'long' => '75d9fa65fdd211fd22a8c00aaf9008a1917e0f15'
            )
        );

        $commit_url = \Criterion\Helper\Commit::getUrl($commit, $repo);
        $this->assertEquals('https://github.com/romhut/criterion/commit/75d9fa65fdd211fd22a8c00aaf9008a1917e0f15', $commit_url);
    }

    public function testCommitUrlFail()
    {
        $repo = 'git@nonegitsite.com:romhut/criterion';
        $commit = array(
            'hash' => array(
                'long' => '75d9fa65fdd211fd22a8c00aaf9008a1917e0f15'
            )
        );

        $commit_url = \Criterion\Helper\Commit::getUrl($commit, $repo);
        $this->assertEquals(false, $commit_url);
    }

    public function testBranchUrl()
    {
        $repo = 'git@github.com:romhut/criterion';
        $commit_url = \Criterion\Helper\Commit::getBranchURL('master', $repo);
        $this->assertEquals('https://github.com/romhut/criterion/tree/master', $commit_url);
    }

    public function testBranchUrlFail()
    {
        $repo = 'git@nonegitsite.com:romhut/criterion';
        $commit_url = \Criterion\Helper\Commit::getBranchURL('master', $repo);
        $this->assertEquals(false, $commit_url);
    }
}
