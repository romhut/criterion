<?php

namespace Criterion\Test\Helper;

class RepoHelperTest extends \Criterion\Test\TestCase
{
    public function testShortRepoUrl()
    {
        $repo = 'https://github.com/romhut/criterion';

        $short = \Criterion\Helper\Repo::short($repo);
        $this->assertEquals('romhut/criterion', $short);
    }

    public function testShortRepoUrlFail()
    {
        $repo = 'https://nonegitsite.com/romhut/criterion';

        $short = \Criterion\Helper\Repo::short($repo);
        $this->assertEquals($repo, $short);
    }

    public function testProvider()
    {
        $repo = 'https://github.com/romhut/criterion';

        $provider = \Criterion\Helper\Repo::provider($repo);
        $this->assertEquals('github', $provider);
    }

    public function testProviderFail()
    {
        $repo = 'https://nonegitsite.com/romhut/criterion';

        $provider = \Criterion\Helper\Repo::provider($repo);
        $this->assertEquals(false, $provider);
    }

}
