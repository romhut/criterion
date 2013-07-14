<?php
namespace Criterion\UI\Controller;

class TestController
{

    public function status(\Silex\Application $app)
    {
        $test = new \Criterion\Model\Test($app['request']->get('id'));
        if ( ! $test->exists)
        {
            return $app->abort(404, 'Test not found.');
        }

        $data = $test->data;
        $data['_id'] = (string) $test->id;

        $logs = $test->getLogs();
        $data['log'] = array();
        foreach ($logs as $log)
        {
            $data['log'][] = $log->data;
        }

        $data['project'] = $test->getProject()->data;
        $data['project']['_id'] = (string) $data['project']['_id'];

        return $app->json($data);
    }

    public function view(\Silex\Application $app)
    {
        $data['test'] = new \Criterion\Model\Test($app['request']->get('id'));
        if ( ! $data['test']->exists)
        {
            return $app->abort(404, 'Test not found.');
        }

        $data['logs'] = $data['test']->getLogs();
        $data['project'] = $data['test']->getProject();

        if ( ! $data['project']->exists)
        {
            return $app->abort(404, 'Project not found.');
        }

        $data['title'] = $data['test']->id . ' | ' . $data['project']->short_repo;

        return $app['twig']->render('Test.twig', $data);
    }

    public function delete(\Silex\Application $app)
    {
        $test = new \Criterion\Model\Test($app['request']->get('id'));
        if ( ! $test->exists)
        {
            return $app->abort(404, 'Test not found.');
        }

        $test->delete();
        foreach ($test->getLogs() as $log)
        {
            $log->delete();
        }

        return $app->redirect('/project/' . $test->project_id);
    }
}
