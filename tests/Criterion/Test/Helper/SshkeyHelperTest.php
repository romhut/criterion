<?php

namespace Criterion\Test\Helper;

class SshKeyHelperTest extends \Criterion\Test\TestCase
{
    public function testInit()
    {
        $key = new \Criterion\Helper\SshKey();
        $this->assertTrue(is_object($key));
    }

    public function testGenerate()
    {
        $key = new \Criterion\Helper\SshKey();
        $this->assertTrue(is_object($key));

        $file =  KEY_DIR . '/criterion_testing' . time();
        $generate = $key->generate($file);
        $this->assertTrue(!empty($generate));

        $key->destroy();
    }

    public function testGenerateDirMissing()
    {
        $key = new \Criterion\Helper\SshKey();
        $this->assertTrue(is_object($key));

        $file = KEY_DIR . '/blah/criterion_testing' . time();
        $generate = $key->generate($file);
        $this->assertTrue(!empty($generate));

        $key->destroy();
        rmdir(KEY_DIR . '/blah');
    }

    public function testGetPrivateKey()
    {
        $key = new \Criterion\Helper\SshKey();
        $this->assertTrue(is_object($key));

        $file = KEY_DIR . '/criterion_testing' . time();
        $generate = $key->generate($file);
        $this->assertTrue(!empty($generate));

        $getPrivateKey = $key->getPrivateKey();
        $this->assertTrue(!empty($getPrivateKey));

        $key->destroy();
    }

    public function testGetPublicKey()
    {
        $key = new \Criterion\Helper\SshKey();
        $this->assertTrue(is_object($key));

        $file = KEY_DIR . '/criterion_testing' . time();
        $generate = $key->generate($file);
        $this->assertTrue(!empty($generate));

        $getPublicKey = $key->getPublicKey();
        $this->assertTrue(!empty($getPublicKey));

        $key->destroy();
    }

    public function testDestroy()
    {
        $key = new \Criterion\Helper\SshKey();
        $this->assertTrue(is_object($key));

        $file = KEY_DIR . '/criterion_testing' . time();
        $generate = $key->generate($file);
        $this->assertTrue(!empty($generate));

        $getPublicKey = $key->getPublicKey();
        $this->assertTrue(!empty($getPublicKey));

        $this->assertTrue($key->destroy());
    }
}
