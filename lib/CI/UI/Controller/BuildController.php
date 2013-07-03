<?php
namespace CI\UI\Controller;

class BuildController
{
    public function view(\Silex\Application $app)
    {
        $data['build'] = $app['mongo']->builds->findOne(array(
            '_id' => new \MongoId($app['request']->get('id'))
        ));

        $logs = $app['mongo']->logs->find(array(
            'build_id' => $app['request']->get('id') // TODO: use mongoid
        ));

        $data['log'] = array();
        foreach ($logs as $log)
        {
            $data['log'][] = $log;
        }

        return $app->json($data);
    }
}