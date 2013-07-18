<?php

namespace Criterion\Test\Helper;

class BitbucketHelperTest extends \Criterion\Test\TestCase
{

    public function testSSHUrl()
    {
        $url = 'https://romhut@bitbucket.org/romhut/criterion';
        $new_url = \Criterion\Helper\Bitbucket::toSSHUrl($url);
        $this->assertEquals('git@bitbucket.org:romhut/criterion', $new_url);
    }

    public function testHTTPSUrl()
    {
        $url = 'git@bitbucket.org:romhut/criterion';
        $new_url = \Criterion\Helper\Bitbucket::toHTTPSUrl($url);
        $this->assertEquals('https://bitbucket.org/romhut/criterion', $new_url);
    }

    public function testCommitUrl()
    {
        $repo = 'git@bitbucket.org:romhut/criterion';
        $commit = array(
            'hash' => array(
                'long' => '75d9fa65fdd211fd22a8c00aaf9008a1917e0f15'
            )
        );

        $commit_url = \Criterion\Helper\Bitbucket::commitUrl($commit, $repo);
        $this->assertEquals('https://bitbucket.org/romhut/criterion/commits/75d9fa65fdd211fd22a8c00aaf9008a1917e0f15', $commit_url);
    }

    public function testBranchUrl()
    {
        $repo = 'git@bitbucket.org:romhut/criterion';
        $commit_url = \Criterion\Helper\Bitbucket::branchUrl('master', $repo);
        $this->assertEquals('https://bitbucket.org/romhut/criterion/src?at=master', $commit_url);
    }

    public function testShortRepo()
    {
        $repo = 'git@bitbucket.org:romhut/criterion';
        $commit_url = \Criterion\Helper\Bitbucket::shortRepo($repo);
        $this->assertEquals('romhut/criterion', $commit_url);
    }

}
