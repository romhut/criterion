<?php
namespace CI\UI\Controller;

class HookController
{
    public function github(\Silex\Application $app)
    {
    	$client= new \GearmanClient();
		$client->addServer('127.0.0.1', 4730);
		$test_id = $client->doNormal('create_test', $app['request']->get('id'));

		$client->doBackground('test', json_encode(array(
			'project' => $app['request']->get('id'),
			'test' => $test_id
		)));

		return $app->json(array(
			'success' => true,
			'test_id' => $test_id
		));
    }
}