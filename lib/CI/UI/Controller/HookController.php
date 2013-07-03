<?php
namespace CI\UI\Controller;

class HookController
{
    public function hook(\Silex\Application $app)
    {
    	$client= new \GearmanClient();
		$client->addServer('127.0.0.1', 4730);
		$test_id = $client->doBackground('create_test', $app['request']->get('id'));

		return $app->json(array(
			'success' => true
		));
    }
}