<?php
namespace Criterion\Model;

use Symfony\Component\Yaml\Yaml;

class Test extends \Criterion\Model
{
    public $collection = 'tests';
    public $project = false;

    public function __construct($query = null, $existing = null)
    {
        parent::__construct($query, $existing);

        if (! $this->exists) {
            $this->status = array(
                'code' => '4',
                'message' => 'Pending'
            );
            $this->started = new \MongoDate();
        }
    }

    public function getProject()
    {
        if (! $this->project) {
            $this->project = new Project($this->project_id);
        }

        return $this->project;
    }

    public function getType()
    {
        if (file_exists($this->path . '/.criterion.yml') || $this->getProject()->hasServerConfig()) {
            return 'criterion';
        } elseif (file_exists($this->path . '/phpunit.xml') || file_exists($this->path . '/phpunit.xml.dist')) {
            return 'phpunit';
        } else {
            return false;
        }
    }

    public function getLogs($internal = false)
    {
        $logs = $this->app->db->logs->find(array(
            'test_id' => new \MongoId($this->id),
            'internal' => $internal
        ))->sort(array(
            'time' => 1
        ));

        $log_models = array();
        foreach ($logs as $log) {
            $log_models[] = new Log(null, $log);
        }

        return $log_models;
    }

    public function failed()
    {
        $this->getProject();
        $command = new \Criterion\Helper\Command($this->project, $this);

        $this->status = array(
            'message' => 'Failed',
            'code' => '0',
        );
        $this->finished = new \MongoDate();
        $this->save();

        $this->project->status = array(
            'message' => 'Failed',
            'code' => '0',
        );
        $this->project->last_run = new \MongoDate();
        $this->project->save();

        if ($this->project->provider === 'github' && $this->project->github['token']) {
            $github_status = \Criterion\Helper\Github::updateStatus('error', $this, $this->project);
            $command->log('Posting "error" status to Github', $github_status ? 'Success' : 'Failed');
        }

        \Criterion\Helper\Notifications::failedEmail($this->id, $this->project);

        if (isset($this->criterion['fail']) && count($this->criterion['fail'])) {
            foreach ($this->criterion['fail'] as $fail) {
                $response = $command->execute($fail);
            }
        }

        $path = TEST_DIR . '/' . $this->project->id  . '/' . (string) $this->id;
        $command->execute(sprintf('rm -rf %s', $path), true);

        return false;
    }

    public function passed()
    {
        $this->getProject();
        $command = new \Criterion\Helper\Command($this->project, $this);

        $this->status = array(
            'message' => 'Passed',
            'code' => '1'
        );
        $this->finished = new \MongoDate();
        $this->save();

        $this->project->status = array(
            'message' => 'Passed',
            'code' => '1'
        );
        $this->project->last_run = new \MongoDate();
        $this->project->save();

        if ($this->project->provider === 'github' && $this->project->github['token']) {
            $github_status = \Criterion\Helper\Github::updateStatus('success', $this, $this->project);
            $command->log('Posting "success" status to Github', $github_status ? 'Success' : 'Failed');
        }

        if (isset($this->criterion['pass']) && count($this->criterion['pass'])) {
            foreach ($this->criterion['pass'] as $pass) {
                $response = $command->execute($pass);
            }
        }

        $path = TEST_DIR . '/' . $this->project->id  . '/' . (string) $this->id;
        $command->execute(sprintf('rm -rf %s', $path), true);

        return false;
    }

    public function getConfig()
    {
        $this->getProject();
        $command = new \Criterion\Helper\Command($this->project, $this);

        if (file_exists($this->config['path'])) {
            $config = Yaml::parse($this->config['path']);
        } else {
            $config = array();
        }

        if (! is_array($config)) {
            $command->log($command, 'The .criterion.yml file does not seem valid, or does not exist', '1');

            return false;
        }

        $serverConfig = $this->project->getServerConfig();

        foreach (array('setup', 'script', 'fail', 'pass') as $section) {

            if (! empty($serverConfig[$section])) {
                if (! is_array($serverConfig[$section])) {
                    $config[$section] = array($serverConfig[$section]);
                } else {
                    $config[$section] = $serverConfig[$section];
                }
            } else {
                if (! isset($config[$section]) || ! is_array($config[$section])) {
                    $config[$section] = array();
                }
            }
        }

        $this->config = array(
            'path' => $this->config['path'],
            'content' => $config
        );

        return $config;
    }
}
