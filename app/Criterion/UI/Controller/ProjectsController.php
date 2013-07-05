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
            $data['repo'] = $app['request']->get('repo');
            $data['branch'] = $app['request']->get('branch');
            $data['status'] = array(
                'code' => '2',
                'message' => 'New'
            );

            $app['mongo']->projects->save($data);

            return $app->redirect('/project/run/' . $data['_id']);
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

        if ($app['request']->getMethod() == 'POST')
        {
            $update_data['repo'] = $app['request']->get('repo');
            $update_data['branch'] = $app['request']->get('branch');

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