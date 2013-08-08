<?php

namespace Criterion\Test\Model;

class UserModelTest extends \Criterion\Test\TestCase
{

    public function testInit()
    {
        $user = new \Criterion\Model\User();
        $this->assertTrue(is_object($user));
    }

    public function testPasswordFail()
    {
        $user = new \Criterion\Model\User();
        $this->assertTrue(is_object($user));

        $this->assertFalse($user->password('testing'));
    }

    public function testPassword()
    {
        $user = new \Criterion\Model\User();
        $this->assertTrue(is_object($user));

        $password = 'criterion';

        $user->password = $password;
        $user->password = $user->password();

        $this->assertTrue($user->password($password));
    }

    public function testIsAdminFalse()
    {
        $user = new \Criterion\Model\User();
        $this->assertFalse($user->isAdmin());
    }

    public function testIsAdminTrue()
    {
        $user = new \Criterion\Model\User();
        $user->role = 'admin';
        $this->assertTrue($user->isAdmin());
    }
}
