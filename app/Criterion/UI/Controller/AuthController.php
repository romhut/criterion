<?php
namespace Criterion\UI\Controller;

class AuthController
{
    public function login(\Silex\Application $app)
    {
        $data = array();
        if ($app['request']->getMethod() === 'POST') {
            $username = strtolower($app['request']->get('username'));
            $password = $app['request']->get('password');

            $user = new \Criterion\Model\User(
                array(
                    'username' => $username
                )
            );

            if ($user->exists && $user->password($password)) {
                $app['session']->set(
                    'user',
                    array(
                        'username' => $username
                    )
                );

                return $app->redirect('/');
            } else {
                $data['error'] = 'Account could not be found, please try again.';
            }
        }

        return $app['twig']->render('Auth/Login.twig', $data);
    }

    public function tokens(\Silex\Application $app)
    {
        if (! $app['user']->isAdmin()) {
            return $app->abort(403, 'You do not have permission to do this');
        }

        $data = array();
        if ($app['request']->getMethod() === 'POST') {
            $token = new \Criterion\Model\Token();
            $token->user_id = $app['user']->id;
            $token->generated = new \MongoDate();
            $token->save();
        }

        $data['tokens'] = $app['user']->getTokens();
        return $app['twig']->render('Auth/Tokens.twig', $data);
    }

    public function delete_token(\Silex\Application $app)
    {
        if (! $app['user']->isAdmin()) {
            return $app->abort(403, 'You do not have permission to do this');
        }

        $token = $app['user']->validToken($app['request']->get('id'));
        if ($token) {
            $token->delete();
        }

        return $app->redirect('/tokens');
    }

    public function logout(\Silex\Application $app)
    {
        $app['session']->clear();

        return $app->redirect('/');
    }
}
