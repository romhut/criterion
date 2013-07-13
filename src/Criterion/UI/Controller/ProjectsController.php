<?php
namespace Criterion\UI\Controller;

class ProjectsController
{
    public function all(\Silex\Application $app)
    {
        $projects = $app['mongo']->projects->find()->sort(array(
            'last_run' => -1
        ));

        $data['projects'] = array();
        $data['failing'] = 0;
        foreach ($projects as $project)
        {
            if ($project['status']['code'] === '0')
            {
                $data['failing']++;
            }

            $data['projects'][] = $project;
        }

        $data['title'] = 'Projects';

        return $app['twig']->render('Projects/All.twig', $data);
    }

    public function create(\Silex\Application $app)
    {
        $project = \Criterion\Helper\Project::fromRepo($app['request']->get('repo'));
        $project['github']['token'] = $app['request']->get('github_token');

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

    public function status(\Silex\Application $app)
    {
        $project = $app['mongo']->projects->findOne(array(
            '_id' => new \MongoId($app['request']->get('id'))
        ));

        if ( ! $project)
        {
            return $app->abort(404, 'Project not found.');
        }

        $images = array(
            0 => 'fail',
            1 => 'pass'
        );

        return $app->redirect('/img/status/' . $images[$project['status']['code']] . '.jpg');
    }

    public function delete(\Silex\Application $app)
    {
        $project = $app['mongo']->projects->findOne(array(
            '_id' => new \MongoId($app['request']->get('id'))
        ));

        if ( ! $project)
        {
            return $app->abort(404, 'Project not found.');
        }

        $tests = $app['mongo']->tests->find(array(
            'project_id' => new \MongoId($app['request']->get('id'))
        ));

        foreach ($tests as $test)
        {
            $app['mongo']->logs->remove(array(
                'test_id' => $test['_id']
            ));

            $app['mongo']->tests->remove(array(
                '_id' => $test['_id']
            ));
        }

        $app['mongo']->projects->remove(array(
            '_id' => new \MongoId($app['request']->get('id'))
        ));

        return $app->redirect('/');
    }

    public function run(\Silex\Application $app)
    {
        $data['project'] = $app['mongo']->projects->findOne(array(
            '_id' => new \MongoId($app['request']->get('id'))
        ));

        if ( ! $data['project'])
        {
            return $app->abort(404, 'Project not found.');
        }

        $test = array(
            'project_id' => new \MongoId($app['request']->get('id')),
            'status' => array(
                'code' => '4',
                'message' => 'Pending'
            ),
            'started' => new \MongoDate(),
            'branch' => $app['request']->get('branch') ?: 'master'
        );

        // If a test ID is specified, then clear out the logs as its a rerun
        if ($app['request']->get('test_id'))
        {
            $app['mongo']->logs->remove(array(
                'test_id' => new \MongoId($app['request']->get('test_id'))
            ));
            $test['_id'] = new \MongoId($app['request']->get('test_id'));
        }

        $app['mongo']->tests->save($test);
        return $app->redirect('/test/' . (string)$test['_id']);
    }

    public function view(\Silex\Application $app)
    {
        $data['project'] = $app['mongo']->projects->findOne(array(
            '_id' => new \MongoId($app['request']->get('id'))
        ));

        if ( ! $data['project'])
        {
            return $app->abort(404, 'Project not found.');
        }

        if ($app['request']->getMethod() == 'POST')
        {
            $update_data['repo'] = $app['request']->get('repo');
            $update_data['short_repo'] = \Criterion\Helper\Repo::short($update_data['repo']);
            $update_data['provider'] = \Criterion\Helper\Repo::provider($update_data['repo']);
            $update_data['ssh_key']['public'] = $app['request']->get('ssh_key_public');
            $update_data['ssh_key']['private'] = $app['request']->get('ssh_key_private');
            $update_data['github']['token'] = $app['request']->get('github_token');


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

        $data['title'] = $data['project']['short_repo'];

        return $app['twig']->render('Projects/View.twig', $data);
    }
}
