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
        return password_hash(
            $this->password,
            PASSWORD_BCRYPT,
            array(
                'cost' => 12
            )
        );
    }

    public function validToken($token)
    {
        $getToken = new \Criterion\Model\Token($token);
        if ($getToken->user_id !== $this->id) {
            return false;
        }

        return $getToken;
    }

    public function getTokens()
    {
        $getTokens = $this->app->db->tokens->find(
            array(
                'user_id' => $this->id
            )
        );

        $tokens = array();
        foreach ($getTokens as $token) {
            $tokens[] = new \Criterion\Model\Token(false, $token);
        }

        return $tokens;
    }
}
