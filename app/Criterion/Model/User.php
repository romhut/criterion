<?php
namespace Criterion\Model;

class User extends \Criterion\Model
{
    public $collection = 'users';

    public function isAdmin()
    {
        return isset($this->role) && $this->role === 'admin';
    }

    public function password($check = false)
    {
        if (! $this->password) {
            return false;
        }

        if ($check) {
            return password_verify($check, $this->password);
        }

        return $this->passwordHash($this->password);

    }

    private function passwordHash($password)
    {
        return password_hash($this->password, PASSWORD_BCRYPT, array(
            'cost' => 12
        ));
    }

}
