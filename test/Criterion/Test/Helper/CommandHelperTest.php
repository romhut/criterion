<?php

namespace Criterion\Test\Helper;

class CommandHelperTest extends \Criterion\Test\TestCase
{
    public function testExecute()
    {
        $command = new \Criterion\Helper\Command();
        $response = $command->execute('echo "hi";');

        $this->assertTrue($response);
    }
}
