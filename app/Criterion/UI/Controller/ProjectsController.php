<?php
namespace Criterion\UI\Controller;

use Symfony\Component\Yaml\Yaml;

class ProjectsController
{
    public function all(\Silex\Application $app)
    {
        $projects = $app['criterion']->db->projects->find()->sort(
            array(
                'last_run' => -1
            )
        );

        $data['projects'] = array();
        $data['failing'] = 0;
        foreach ($projects as $project) {
            $project = new \Criterion\Model\Project(null, $project);
            if ($project->status['code'] === '0') {
                $data['failing']++;
            }

            $data['projects'][] = $project;
        }

        $data['title'] = 'Projects';

        if ($data['failing'] > 0) {
            $data['title'] .= ' (' . $data['failing'] . ')';
        }

        return $app['twig']->render('Projects/All.twig', $data);
    }

    public function create(\Silex\Application $app)
    {
        if (! $app['user']->isAdmin()) {
            return $app->abort(403, 'You do not have permission to do this');
        }

        $project = new \Criterion\Model\Project();
        $project->emptyProject($app['request']->get('source'));

        if ($project->save()) {
            return $app->redirect('/project/' . (string) $project->id);
        }

        return $app->abort(500, 'Error creating project');

    }

    public function status(\Silex\Application $app)
    {
        $project = new \Criterion\Model\Project(
            array(
                'short_repo' => implode(
                    '/',
                    array(
                        $app['request']->get('vendor'),
                        $app['request']->get('package')
                    )
                )
            )
        );

        if (! $project->exists) {
            return $app->abort(404, 'Project not found.');
        }

        $images = array(
            0 => 'fail',
            1 => 'pass',
            2 => 'pending'
        );

        $status = $project->status['code'];
        if (! isset($images[$status])) {
            $status = 0;
        }

        $file = ROOT . '/public/img/status/' . $images[$status] . '.jpg';
        $stream = function () use ($file) {
            readfile($file);
        };

        return $app->stream(
            $stream,
            200,
            array(
                'Content-Type' => 'image/jpg',
                'Cache-Control' => 'no-cache, must-revalidate',
                'Expires' => 'Sat, 26 Jul 1997 05:00:00 GMT'
            )
        );
    }

    public function delete(\Silex\Application $app)
    {
        if (! $app['user']->isAdmin()) {
            return $app->abort(403, 'You do not have permission to do this');
        }

        $project = new \Criterion\Model\Project($app['request']->get('id'));

        if (! $project->exists) {
            return $app->abort(404, 'Project not found.');
        }

        foreach ($project->getTests() as $test) {
            $test->delete();
            $logs = $test->getLogs();
            foreach ($logs as $log) {
                $log->delete();
            }
        }

        $project->delete();

        return $app->redirect('/');
    }

    public function run(\Silex\Application $app)
    {
        if (! $app['user']->isAdmin()) {
            return $app->abort(403, 'You do not have permission to do this');
        }

        $project = new \Criterion\Model\Project($app['request']->get('id'));
        if (! $project->exists) {
            return $app->abort(404, 'Project not found.');
        }

        $test = new \Criterion\Model\Test($app['request']->get('test_id'));

        // If a test ID is specified, then clear out the logs as its a rerun
        if ($test->exists) {
            $logs = $test->getLogs();
            foreach ($logs as $log) {
                $log->delete();
            }

            $test->started = new \MongoDate();
            $test->status = array(
                'code' => '4',
                'message' => 'Pending'
            );
        }

        $test->project_id = new \MongoId($app['request']->get('id'));
        $test->branch = $app['request']->get('branch') ?: 'master';
        $test->save();

        return $app->redirect('/test/' . (string) $test->id);
    }

    public function view(\Silex\Application $app)
    {
        $project = new \Criterion\Model\Project($app['request']->get('id'));

        if (! $project->exists) {
            return $app->abort(404, 'Project not found.');
        }

        if ($app['request']->getMethod() == 'POST') {
            if (! $app['user']->isAdmin()) {
                return $app->abort(403, 'You do not have permission to do this');
            }

            $config = Yaml::parse($app['request']->get('config'));
            $project->setServerConfig($config);

            $project->short_repo = \Criterion\Helper\Repo::short($project->source);
            $project->provider = \Criterion\Helper\Repo::provider($project->source);
            $project->save();

            return $app->redirect('/project/' . $project->id);

        }

        if (isset($project->name) && $project->name) {
            $data['title'] = $project->name;
        } else {
            $data['title'] = $project->short_repo;
        }

        $data['tests'] = $project->getTests();
        $data['project'] = $project;
        $data['config'] = Yaml::dump($project->getServerConfig());

        return $app['twig']->render('Project.twig', $data);
    }
}
