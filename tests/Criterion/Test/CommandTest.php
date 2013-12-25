<?php
namespace Criterion\Test;

class CommandTest extends TestCase
{
    public function testJobSetAndGet()
    {
        $strJob = '\\Criterion\\Model\\Job';
        $strCommand = 'phpunit';

        $mockJob = \Mockery::mock($strJob);

        $command = new \Criterion\Command($mockJob, $strCommand);
        $this->assertInstanceOf($strJob, $command->getJob());
    }

    public function testGetCommand()
    {
        $strJob = '\\Criterion\\Model\\Job';
        $strCommand = 'phpunit';
        $mockJob = \Mockery::mock($strJob);

        $command = new \Criterion\Command($mockJob, $strCommand);
        $this->assertEquals($strCommand, $command->getCommand());
    }

    public function testExecute()
    {
        $strJob = '\Criterion\Model\Job';
        $strCommand = 'echo "hi"';

        $mockJob = \Mockery::mock($strJob)
            ->shouldReceive('getPath')
            ->once()
            ->andReturn(ROOT)
            ->mock();

        $command = new \Criterion\Command($mockJob, $strCommand);
        $execute = $command->execute();

        $this->assertInstanceOf('\\Criterion\Command', $execute);
    }

    public function testGetStatus()
    {
        $strJob = '\Criterion\Model\Job';
        $strCommand = 'echo "hi"';

        $mockJob = \Mockery::mock($strJob)
            ->shouldReceive('getPath')
            ->once()
            ->andReturn(ROOT)
            ->mock();

        $command = new \Criterion\Command($mockJob, $strCommand);
        $execute = $command->execute();

        $this->assertEquals(0, $execute->getStatus());
    }

    public function testGetOutput()
    {
        $strJob = '\Criterion\Model\Job';
        $strCommand = 'echo "hi"';

        $mockJob = \Mockery::mock($strJob)
            ->shouldReceive('getPath')
            ->once()
            ->andReturn(ROOT)
            ->mock();

        $command = new \Criterion\Command($mockJob, $strCommand);
        $execute = $command->execute();

        $this->assertEquals('hi', $execute->getOutput());
    }

    public function testExecuteFail()
    {

        $this->setExpectedException('Criterion\Exception\Command\Failed');

        $strJob = '\\Criterion\\Model\\Job';
        $strCommand = 'thiscommandwillfail';

        $mockJob = \Mockery::mock($strJob)
            ->shouldReceive('getPath')
            ->once()
            ->andReturn(ROOT)
            ->mock();

        $command = new \Criterion\Command($mockJob, $strCommand);
        $command->execute();
    }
}
