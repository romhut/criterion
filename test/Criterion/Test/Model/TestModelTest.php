<?php

namespace Criterion\Test\Model;

class TestModelTest extends \Criterion\Test\TestCase
{

    public function testInit()
    {
        $test = new \Criterion\Model\Test();
        $this->assertTrue(is_object($test));
        $this->assertTrue(isset($test->status));
    }

    public function testGetProject()
    {
        $test = new \Criterion\Model\Test();
        $this->assertTrue(is_object($test));

        $project = new \Criterion\Model\Project();
        $project->save();

        $test->project_id = $project->id;
        $this->assertTrue(is_object($test->getProject()));
    }

    public function testGetLogs()
    {
        $test = new \Criterion\Model\Test();
        $this->assertTrue(is_object($test));
        $test->save();

        $log = new \Criterion\Model\Log();
        $log->test_id = $test->id;
        $log->save();

        $this->assertTrue(is_array($test->getLogs()));
    }
}
