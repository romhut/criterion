<?php

namespace Criterion\Test;

class CommitHelperTest extends TestCase
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
        $this->assertEquals($commit_url, 'https://github.com/romhut/criterion/commit/75d9fa65fdd211fd22a8c00aaf9008a1917e0f15');
    }

    public function testBranchUrl()
    {
        $repo = 'git@github.com:romhut/criterion';
        $commit_url = \Criterion\Helper\Commit::getBranchURL('master', $repo);
        $this->assertEquals($commit_url, 'https://github.com/romhut/criterion/tree/master');
    }
}
