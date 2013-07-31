<?php

namespace Criterion\Test\Helper;

class CommandHelperTest extends \Criterion\Test\TestCase
{
    public function testExecute()
    {
        $project = new \Criterion\Model\Project(array(
            'source' => ROOT
        ));

        $test = new \Criterion\Model\Test();

        $command = new \Criterion\Helper\Command($project, $test);
        $response = $command->execute('echo "hi";');

        $this->assertTrue($response);
    }
}
