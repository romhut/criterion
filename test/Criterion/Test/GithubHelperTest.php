<?php

namespace Criterion\Test;

class GithubHelperTest extends TestCase
{

    public function testSSHUrl()
    {
        $url = 'https://github.com/romhut/criterion';
        $new_url = \Criterion\Helper\Github::toSSHUrl($url);
        $this->assertEquals($new_url, 'git@github.com:romhut/criterion');
    }

    public function testHTTPSUrl()
    {
        $url = 'git@github.com:romhut/criterion';
        $new_url = \Criterion\Helper\Github::toHTTPSUrl($url);
        $this->assertEquals($new_url, 'https://github.com/romhut/criterion');
    }

    public function testCommitUrl()
    {
        $repo = 'git@github.com:romhut/criterion';
        $commit = array(
            'hash' => array(
                'long' => '75d9fa65fdd211fd22a8c00aaf9008a1917e0f15'
            )
        );

        $commit_url = \Criterion\Helper\Github::commitUrl($commit, $repo);
        $this->assertEquals($commit_url, 'https://github.com/romhut/criterion/commit/75d9fa65fdd211fd22a8c00aaf9008a1917e0f15');
    }

    public function testBranchUrl()
    {
        $repo = 'git@github.com:romhut/criterion';
        $commit_url = \Criterion\Helper\Github::branchUrl('master', $repo);
        $this->assertEquals($commit_url, 'https://github.com/romhut/criterion/tree/master');
    }

    public function testShortRepo()
    {
        $repo = 'git@github.com:romhut/criterion';
        $commit_url = \Criterion\Helper\Github::shortRepo($repo);
        $this->assertEquals($commit_url, 'romhut/criterion');
    }

}
