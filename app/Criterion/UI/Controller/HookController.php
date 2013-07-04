<?php
namespace Criterion\UI\Controller;

class HookController
{
    public function hook(\Silex\Application $app)
    {
        $payload = json_decode($app['request']->get('payload'));

        if ( ! isset($payload['repository']['url']))
        {
            return $app->json(array(
                'success' => false
            ));
        }

        $project = $app['mongo']->projects->findOne(array(
            'repo' => $payload['repository']['url']
        ));

        if ( ! $project)
        {
            $project['repo'] = $payload['repository']['url'];
            $app['mongo']->projects->save($project);
        }

    	$client= new \GearmanClient();
		$client->addServer('127.0.0.1', 4730);
		$test_id = $client->doBackground('create_test', (string) $project['_id']);

		return $app->json(array(
			'success' => true
		));
    }
}