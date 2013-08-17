<?php

namespace Criterion\Test\Helper;

class GithubHelperTest extends \Criterion\Test\TestCase
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

    /**
     * All we can really do here is check that there are no syntax errors.
     * To be able to test this properly we would need a github token, which
     * would leave an account very insecure.
     */
    public function testUpdateStatus()
    {
        $project = new \Criterion\Model\Project(
            array(
                'source' => 'git@github.com:romhut/criterion'
            )
        );

        $project->github = array(
            'token' => 'invalidtoken'
        );

        $test = new \Criterion\Model\Test();
        $test->commit = array(
            'hash' => array(
                'long' => 'notreallyahash'
            )
        );

        $update = \Criterion\Helper\Github::updateStatus('success', $test, $project);
        $this->assertFalse($update); // HACK: this will be true in a real use case. See comment above.

    }
}
