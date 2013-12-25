<?php
namespace Criterion\Test;

class ConfigTest extends TestCase
{
    public function testInit()
    {
        $strJob = '\\Criterion\\Model\\Job';

        $mockJob = \Mockery::mock($strJob)
            ->shouldReceive('getPath')
            ->once()
            ->andReturn(ROOT)
            ->mock();

        $config = new \Criterion\Config($mockJob);

        $this->assertInstanceOf('Criterion\Config', $config);
    }

    public function testSetConfig()
    {
        $strJob = '\\Criterion\\Model\\Job';

        $mockJob = \Mockery::mock($strJob)
            ->shouldReceive('getPath')
            ->once()
            ->andReturn(ROOT)
            ->mock();

        $config = new \Criterion\Config($mockJob);
        $config->setConfig([]);

        $this->assertEquals([], $config->getConfig());
    }

    public function testEmptyServices()
    {
        $strJob = '\\Criterion\\Model\\Job';

        $mockJob = \Mockery::mock($strJob)
            ->shouldReceive('getPath')
            ->once()
            ->andReturn(ROOT)
            ->mock();

        $config = new \Criterion\Config($mockJob);
        $config->setConfig([]);

        $this->assertCount(0, $config->getServices());
    }

    public function testGetServices()
    {
        $strJob = '\\Criterion\\Model\\Job';

        $mockJob = \Mockery::mock($strJob)
            ->shouldReceive('getPath')
            ->once()
            ->andReturn(ROOT)
            ->mock();

        $config = new \Criterion\Config($mockJob);

        $services = $config->getServices();
        $this->assertInstanceOf('\Criterion\Service', $services[0]);
    }

    public function testServiceNotFound()
    {
        $strJob = '\\Criterion\\Model\\Job';

        $mockJob = \Mockery::mock($strJob)
            ->shouldReceive('getPath')
            ->once()
            ->andReturn(ROOT)
            ->mock();

        $config = new \Criterion\Config($mockJob);
        $config->setConfig([
            'services' => [
                'notfound' => []
            ]
        ]);

        $this->assertCount(0, $config->getServices());
    }

    public function testGetHooks()
    {
        $strJob = '\\Criterion\\Model\\Job';

        $mockJob = \Mockery::mock($strJob)
            ->shouldReceive('getPath')
            ->once()
            ->andReturn(ROOT)
            ->mock();

        $config = new \Criterion\Config($mockJob);
        $hooks = $config->getHooks();
        $this->assertInstanceOf('\Criterion\Hook', $hooks[0]);
    }

    public function testEmptyHooks()
    {
        $strJob = '\\Criterion\\Model\\Job';

        $mockJob = \Mockery::mock($strJob)
            ->shouldReceive('getPath')
            ->once()
            ->andReturn(ROOT)
            ->mock();

        $config = new \Criterion\Config($mockJob);
        $config->setConfig([]);

        $this->assertCount(0, $config->getHooks());
    }

    public function testHookNotFound()
    {
        $strJob = '\\Criterion\\Model\\Job';

        $mockJob = \Mockery::mock($strJob)
            ->shouldReceive('getPath')
            ->once()
            ->andReturn(ROOT)
            ->mock();

        $config = new \Criterion\Config($mockJob);
        $config->setConfig([
            'hooks' => [
                'notfound' => []
            ]
        ]);

        $this->assertCount(0, $config->getHooks());
    }
}
