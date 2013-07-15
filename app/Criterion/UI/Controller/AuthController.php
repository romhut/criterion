<?php
namespace Criterion\UI\Controller;

class AuthController
{
    public function login(\Silex\Application $app)
    {
        if ($app['user'])
        {
            return $app->redirect('/');
        }
        return $app->abort(401, 'Please login');
    }

    public function logout(\Silex\Application $app)
    {
        return $app->abort(401, 'You have been logged out.');
    }
}
