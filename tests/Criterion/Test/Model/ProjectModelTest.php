<?php

namespace Criterion\Test\Model;

class ProjectModelTest extends \Criterion\Test\TestCase
{

    public function testInit()
    {
        $project = new \Criterion\Model\Project();
        $this->assertTrue(is_object($project));
    }

    public function testInitWithRepo()
    {
        $project = new \Criterion\Model\Project(
            array(
                'source' => 'https://github.com/romhut/criterion'
            )
        );

        $this->assertTrue(is_object($project));
    }

    public function testSetServerConfig()
    {
        $project = new \Criterion\Model\Project(
            array(
                'source' => 'https://github.com/romhut/criterion'
            )
        );

        $this->assertTrue(is_object($project));

        $config = array(
            'source' => 'test',
        );

        $this->assertCount(count($project->serverConfigWhitelist), $project->setServerConfig($config));
    }

    public function testSetServerConfigEnvPass()
    {
        $project = new \Criterion\Model\Project(
            array(
                'source' => 'https://github.com/romhut/criterion'
            )
        );

        $this->assertTrue(is_object($project));

        $config = array(
            'enviroment_variables' => array(
                'test=test'
            )
        );

        $this->assertCount(count($project->serverConfigWhitelist), $project->setServerConfig($config));
    }

    public function testSetServerConfigEnvFail()
    {
        $project = new \Criterion\Model\Project(
            array(
                'source' => 'https://github.com/romhut/criterion'
            )
        );

        $this->assertTrue(is_object($project));

        $config = array(
            'enviroment_variables' => array(
                'test fail'
            )
        );

        $this->assertCount(count($project->serverConfigWhitelist), $project->setServerConfig($config));
    }

    public function testGetServerConfig()
    {
        $project = new \Criterion\Model\Project(
            array(
                'source' => 'https://github.com/romhut/criterion'
            )
        );

        $this->assertTrue(is_object($project));
        $this->assertCount(count($project->serverConfigWhitelist), $project->getServerConfig());
    }

    public function testHasServerConfigTrue()
    {
        $project = new \Criterion\Model\Project(
            array(
                'source' => 'https://github.com/romhut/criterion'
            )
        );

        $config = array(
            'script' => array(
                'echo "hi"'
            )
        );

        $this->assertTrue(is_object($project));
        $project->setServerConfig($config);
        $this->assertTrue($project->hasServerConfig());
    }

    public function testHasServerConfigFalse()
    {
        $project = new \Criterion\Model\Project(
            array(
                'source' => 'https://github.com/romhut/criterion'
            )
        );

        $this->assertTrue(is_object($project));
        $this->assertFalse($project->hasServerConfig());
    }

    public function testGetTests()
    {
        $project = new \Criterion\Model\Project();
        $this->assertTrue(is_object($project));

        $test = new \Criterion\Model\Test();
        $this->assertTrue(is_object($test));
        $test->project_id = $project->id;
        $test->save();

        $tests = $project->getTests();

        $this->assertTrue(is_array($tests));
        $this->assertTrue(count($tests) === 1);
    }
}
