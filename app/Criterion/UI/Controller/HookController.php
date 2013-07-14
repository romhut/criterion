<?php
namespace Criterion\UI\Controller;

class HookController
{
    public function github(\Silex\Application $app)
    {
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

        $project = $app['criterion']->db->projects->findOne(array(
            'repo' => $repo
        ));

        if ( ! $project)
        {
            $project = \Criterion\Helper\Project::fromRepo($repo);
            $app['criterion']->db->projects->save($project);

            $ssh_key_file = KEY_DIR . '/' . (string) $project['_id'];

            exec('ssh-keygen -t rsa -q -f "' . $ssh_key_file . '" -N "" -C "ci@criterion"', $ssh_key, $response);

            if ((string) $response !== '0')
            {
                $app['criterion']->db->projects->remove(array(
                    '_id' => $project['_id']
                ));

                return $app->json(array(
                    'success' => false
                ));
            }

            $app['criterion']->db->projects->update(array(
                '_id' => $project['_id']
            ), array(
                '$set' => array(
                    'ssh_key' => array(
                        'public' => file_get_contents($ssh_key_file . '.pub'),
                        'private' => file_get_contents($ssh_key_file),
                    )
                )
            ));

            exec('rm -rf ' . $ssh_key_file);
            exec('rm -rf ' . $ssh_key_file . '.pub');
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

        $app['criterion']->db->tests->save($test);

        return $app->json(array(
            'success' => true,
            'test' => (string) $test['_id']
        ));
    }
}
