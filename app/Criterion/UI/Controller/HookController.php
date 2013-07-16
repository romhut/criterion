<?php
namespace Criterion\UI\Controller;

class HookController
{
    public function github(\Silex\Application $app)
    {
        $query_token = $app['request']->query->get('token');

        $token = new \Criterion\Model\Token($query_token);

        if ( ! $token->exists)
        {
            return $app->abort(404, 'Page does not exist');
        }


        $payload = json_decode($app['request']->get('payload'), true);
        if ( ! isset($payload['repository']['url']))
        {
            return $app->json(array(
                'success' => false
            ));
        }

        $repo = $payload['repository']['url'];
        // Detect if its an private repository, if so then we need to use SSH
        if ($payload['repository']['private'])
        {
            $repo = \Criterion\Helper\Github::toSSHUrl($repo);
        }

        $project = new \Criterion\Model\Project(array(
            'repo' => $repo
        ));

        if ( ! $project->exists)
        {
            $project->save();
        }

        $test = new \Criterion\Model\Test();
        $test->project_id = $project->id;
        $test->branch = str_replace('refs/heads/', null, $payload['ref']);
        $test->save();

        return $app->json(array(
            'success' => true,
            'test' => (string) $test->id
        ));
    }
}
