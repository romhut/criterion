<?php
namespace CI\UI\Controller;

class HookController
{
    public function github(\Silex\Application $app)
    {
    	$client= new \GearmanClient();
		$client->addServer('127.0.0.1', 4730);
		$client->doBackground('build', $app['request']->get('id'));

		return $app->json(array(
			'success' => true
		));
    }
}