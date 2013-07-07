<?php

namespace Criterion\Test;

class RepoHelperTest extends TestCase
{
    public function testShortRepoUrl()
    {
        $repo = 'https://github.com/romhut/criterion';

        $short = \Criterion\Helper\Repo::short($repo);
        $this->assertEquals('romhut/criterion', $short);

        $provider = \Criterion\Helper\Repo::provider($repo);
        $this->assertEquals('github', $provider);
    }

}
