<?php

namespace Criterion\Test;

class ProjectHelperTest extends TestCase
{
    public function testCreate()
    {
        $repo = 'https://github.com/romhut/criterion';
        $project = \Criterion\Helper\Project::fromRepo($repo);

        $this->assertArrayHasKey('repo', $project);
        $this->assertArrayHasKey('short_repo', $project);
        $this->assertArrayHasKey('provider', $project);
        $this->assertArrayHasKey('last_run', $project);
        $this->assertArrayHasKey('status', $project);
        $this->assertArrayHasKey('code', $project['status']);
        $this->assertArrayHasKey('message', $project['status']);
        $this->assertEquals('2', $project['status']['code']);

    }

}
