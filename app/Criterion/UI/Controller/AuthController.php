<?php
namespace Criterion\UI\Controller;

class AuthController
{
    public function login(\Silex\Application $app)
    {
        $data = array();
        if ($app['request']->getMethod() === 'POST')
        {
            $username = strtolower($app['request']->get('username'));
            $password = $app['request']->get('password');

            $user = new \Criterion\Model\User(array(
                'username' => $username
            ));

            if ($user->exists && $user->password($password))
            {
                $app['session']->set('user', array(
                    'username' => $username
                ));
                return $app->redirect('/');
            }
            else
            {
                $data['error'] = 'Account could not be found, please try again.';
            }
        }

        return $app['twig']->render('Auth/Login.twig', $data);
    }

    public function logout(\Silex\Application $app)
    {
        $app['session']->clear();
        return $app->redirect('/');
    }
}
