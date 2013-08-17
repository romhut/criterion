<?php

namespace Criterion\Test\Helper;

class NotificationsHelperTest extends \Criterion\Test\TestCase
{
    public function testEmail()
    {
        $this->markTestIncomplete('We need to work out a way to mock emails');

        $response = \Criterion\Helper\Notifications::email('testing@localhost', 'Testing', 'Testing');
        $this->assertTrue($response);
    }

    public function testFailedEmail()
    {
        $this->markTestIncomplete('We need to work out a way to mock emails');

        $project = new \Criterion\Model\Project(array(
            'source' => 'git@github.com:romhut/criterion'
        ));
        $project->email = 'testing@localhost';

        $test = new \Criterion\Model\Test();

        $response = \Criterion\Helper\Notifications::failedEmail($test->id, $project);
        $this->assertTrue($response);
    }

    public function testFailedEmailNoEmail()
    {
        $this->markTestIncomplete('We need to work out a way to mock emails');

        $project = new \Criterion\Model\Project(array(
            'source' => 'git@github.com:romhut/criterion'
        ));

        $test = new \Criterion\Model\Test();
        $response = \Criterion\Helper\Notifications::failedEmail($test->id, $project);
        $this->assertFalse($response);
    }
}
