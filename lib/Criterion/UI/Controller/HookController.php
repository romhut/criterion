<?php
namespace Criterion\UI\Controller;

class HookController
{
    public function hook(\Silex\Application $app)
    {
        if ($app['request']->getMethod() !== 'POST')
        {
            return $app->json(array(
                'success' => false
            ));
        }

    	$client= new \GearmanClient();
		$client->addServer('127.0.0.1', 4730);
		$test_id = $client->doBackground('create_test', $app['request']->get('id'));

		return $app->json(array(
			'success' => true
		));
    }
}