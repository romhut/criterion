<?php
namespace Criterion\UI\Controller;

class HookController
{
    public function github(\Silex\Application $app)
    {
        $payload = $app['request']->get('payload');

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
            $project['status'] = array(
                'code' => '2',
                'message' => 'New'
            );
            $project['last_run'] = new \MongoDate();
            $app['mongo']->projects->save($project);
        }

        $branch = str_replace('refs/heads/', null, $payload['ref']);

        $test = array(
            'project_id' => $project['_id'],
            'status' => array(
                'code' => '4',
                'message' => 'Pending'
            ),
            'started' => new \MongoDate(),
            'branch' => $branch
        );

        $app['mongo']->tests->save($test);

        return $app->json(array(
            'success' => true,
            'test' => (string) $test['_id']
        ));
    }
}
