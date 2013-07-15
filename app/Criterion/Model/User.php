<?php
namespace Criterion\Model;

class User extends \Criterion\Model
{
    public $collection = 'users';

    public function isAdmin()
    {
        return isset($this->role) && $this->role === 'admin';
    }

}