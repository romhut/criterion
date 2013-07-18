<?php

namespace Criterion\Test\Model;

class UserModelTest extends \Criterion\Test\TestCase
{

    public function testInit()
    {
        $user = new \Criterion\Model\User();
        $this->assertTrue(is_object($user));
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
