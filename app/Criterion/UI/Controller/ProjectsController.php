<?php
namespace Criterion\UI\Controller;

class ProjectsController
{
    public function all(\Silex\Application $app)
    {
        $projects = $app['mongo']->projects->find()->sort(array(
            '_id' => -1
        ));

        $data['projects'] = array();
        foreach ($projects as $project)
        {
            $data['projects'][] = $project;
        }

        return $app['twig']->render('Projects/All.twig', $data);
    }

    public function create(\Silex\Application $app)
    {
        if ($app['request']->getMethod() === 'POST')
        {
            $project['repo'] = $app['request']->get('repo');
            $project['status'] = array(
                'code' => '2',
                'message' => 'New'
            );
            $project['last_run'] = new \MongoDate();
            $app['mongo']->projects->save($project);

            $ssh_key_file = KEY_DIR . '/' . (string) $project['_id'];

            exec('ssh-keygen -t rsa -q -f "' . $ssh_key_file . '" -N "" -C "ci@criterion"', $ssh_key, $response);

            if ((string) $response !== '0')
            {
                $app['mongo']->projects->remove(array(
                    '_id' => $project['_id']
                ));

                return $app->json(array(
                    'success' => false
                ));
            }

            $app['mongo']->projects->update(array(
                '_id' => $project['_id']
            ), array(
                '$set' => array(
                    'ssh_key' => array(
                        'public' => file_get_contents($ssh_key_file . '.pub'),
                        'private' => file_get_contents($ssh_key_file),
                    )
                )
            ));

            // Remove the SSH files due to permissions issue, let PHP generate them later on.
            exec('rm ' . $ssh_key_file);
            exec('rm ' . $ssh_key_file . '.pub');

            return $app->redirect('/project/' . (string)$project['_id']);
        }

        return $app['twig']->render('Projects/Create.twig');

    }

    public function run(\Silex\Application $app)
    {
        $test = array(
            'project_id' => new \MongoId($app['request']->get('id')),
            'status' => array(
                'code' => '4',
                'message' => 'Pending'
            ),
            'started' => new \MongoDate()
        );

        $app['mongo']->tests->save($test);
        return $app->redirect('/project/' . $app['request']->get('id'));
    }

    public function view(\Silex\Application $app)
    {
        $data['project'] = $app['mongo']->projects->findOne(array(
            '_id' => new \MongoId($app['request']->get('id'))
        ));

        if ( ! $data['project'])
        {
            return $app->redirect('/');
        }

        if ($app['request']->getMethod() == 'POST')
        {
            $update_data['repo'] = $app['request']->get('repo');
            $update_data['ssh_key']['public'] = $app['request']->get('ssh_key_public');
            $update_data['ssh_key']['private'] = $app['request']->get('ssh_key_private');

            $update = $app['mongo']->projects->update($data['project'], array(
                '$set' => $update_data
            ));

            return $app->redirect('/project/' . $app['request']->get('id'));

        }

        $tests = $app['mongo']->tests->find(array(
            'project_id' => new \MongoId($app['request']->get('id'))
        ))->sort(array(
            'started' => -1
        ));

        $data['tests'] = array();
        foreach ($tests as $test)
        {
            $data['tests'][] = $test;
        }

        return $app['twig']->render('Projects/View.twig', $data);
    }
}
