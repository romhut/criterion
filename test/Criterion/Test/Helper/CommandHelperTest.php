<?php

namespace Criterion\Test\Helper;

class CommandHelperTest extends \Criterion\Test\TestCase
{
    public function testExecture()
    {
        $command = new \Criterion\Helper\Command();
        $response = $command->execute('echo "hi";');

        $this->assertTrue($response);
    }
}
