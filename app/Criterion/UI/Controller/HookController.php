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

        $test = array(
            'project_id' => $project['_id'],
            'status' => array(
                'code' => '4',
                'message' => 'Pending'
            ),
            'started' => new \MongoDate()
        );

        $app['mongo']->tests->save($test);

		return $app->json(array(
			'success' => true,
            'test' => (string) $test['_id']
		));
    }
}