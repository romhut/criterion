<?php

namespace Criterion\Test\Helper;

class NotificationsHelperTest extends \Criterion\Test\TestCase
{
    public function testEmail()
    {
        $response = \Criterion\Helper\Notifications::email('testing@localhost', 'Testing', 'Testing');
        $this->assertTrue($response);
    }

    public function testFailedEmail()
    {
        $project = new \Criterion\Model\Project(array(
            'repo' => 'git@github.com:romhut/criterion'
        ));
        $project->email = 'testing@localhost';

        $test = new \Criterion\Model\Test();

        $response = \Criterion\Helper\Notifications::failedEmail($test->id, $project);
        $this->assertTrue($response);
    }

    public function testFailedEmailNoEmail()
    {
        $project = new \Criterion\Model\Project(array(
            'repo' => 'git@github.com:romhut/criterion'
        ));

        $test = new \Criterion\Model\Test();
        $response = \Criterion\Helper\Notifications::failedEmail($test->id, $project);
        $this->assertFalse($response);
    }
}
