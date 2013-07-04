<?php
namespace CI\UI\Controller;

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
}