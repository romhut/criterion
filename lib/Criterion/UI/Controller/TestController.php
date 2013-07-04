<?php
namespace Criterion\UI\Controller;

class TestController
{
    public function view(\Silex\Application $app)
    {
        $data['test'] = $app['mongo']->tests->findOne(array(
            '_id' => new \MongoId($app['request']->get('id'))
        ));

        $logs = $app['mongo']->logs->find(array(
            'test_id' => new \MongoId($app['request']->get('id'))
        ));

        $data['log'] = array();
        foreach ($logs as $log)
        {
            $data['log'][] = $log;
        }

        return $app['twig']->render('Test.twig', $data);
    }

    public function delete(\Silex\Application $app)
    {
        $test = $app['mongo']->tests->findOne(array(
            '_id' => new \MongoId($app['request']->get('id'))
        ));

        if ( ! $test)
        {
            return $app->redirect('/');
        }

        $app['mongo']->tests->remove(array(
            '_id' => new \MongoId($app['request']->get('id'))
        ));

        $app['mongo']->logs->remove(array(
            'test_id' => new \MongoId($app['request']->get('id'))
        ));

        return $app->redirect('/project/' . $test['project_id']);

    }
}