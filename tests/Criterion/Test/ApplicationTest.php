<?php
namespace Criterion\Test;

class ApplicationTest extends TestCase
{
    public function testSetAndGet()
    {
        $silexApp = '\\Silex\\Application';
        $mockApp = \Mockery::mock($silexApp);

        \Criterion\Application::setApp($mockApp);

        $this->assertInstanceOf($silexApp, \Criterion\Application::getApp());
    }
}
