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

        if (isset($this->config['content']['fail']) && count($this->config['content']['fail'])) {
            foreach ($this->config['content']['fail'] as $fail) {
                $response = $command->execute($fail);
            }
        }

        $path = TEST_DIR . '/' . $this->project->id  . '/' . (string) $this->id;
        $command->execute(sprintf('rm -rf %s', $path), true);

        return true;
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

        if (isset($this->config['content']['pass']) && count($this->config['content']['pass'])) {
            foreach ($this->config['content']['pass'] as $pass) {
                $response = $command->execute($pass);
            }
        }

        $path = TEST_DIR . '/' . $this->project->id  . '/' . (string) $this->id;
        $command->execute(sprintf('rm -rf %s', $path), true);

        return true;
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

        $this->save();

        return $config;
    }

    public function preLog($command, $internal = false)
    {
        $command = str_replace(DATA_DIR, null, $command);

        $log = new \Criterion\Model\Log();
        $log->output = 'Running...';
        $log->response = false;
        $log->command = $command;
        $log->test_id = $this->id;
        $log->time = new \MongoDate();
        $log->status = '0';
        $log->internal = $internal;
        $log->save();

        return $log->id;
    }

    public function log($command, $output, $response = '0', $log_id = null, $internal = false)
    {
        $this->getProject();

        $command = str_replace(DATA_DIR, null, $command);
        $output = str_replace(DATA_DIR, null, $output);

        $log = new \Criterion\Model\Log($log_id);

        $log->output = $output;
        $log->response = (string) $response;
        $log->command = $command;
        $log->test_id = $this->id;
        $log->project_id = $this->project->id;
        $log->time = new \MongoDate();
        $log->status = '1';
        $log->internal = $internal;
        $log->save();

        return $log;
    }

    public function setEnviromentVariables()
    {
        $this->getProject();

        if (is_array($this->project->enviroment_variables)) {
            $set_env_variables = $this->preLog('Setting enviroment variables');

            $env_variables = array();
            foreach ($this->project->enviroment_variables as $env_var) {
                $env_variables[] = $env_var;
                putenv($env_var);
            }
            $this->log('Setting environment variables', implode(', ',$env_variables), 0, $set_env_variables);
        }
    }

    public function fetch()
    {
        $this->getProject();
        $command = new \Criterion\Helper\Command($this->project, $this);

        // Add a fake "clone" log entry. This is a lot cleaner when outputting the logs.
        $prelog_fetch = $this->preLog('Fetching ' . $this->project->source);
        $fetch_start = microtime(true);

        // Get a fully formatted clone command, and then run it.
        $fetch_command = \Criterion\Helper\Repo::fetchCommand($this, $this->project);
        $fetch = $command->execute($fetch_command, true);

        $fetch_end = microtime(true);
        $clone_output = 'Failed';
        if ($fetch->success) {
            $clone_output = 'Fetched in ' . number_format($fetch_end - $fetch_start) . ' seconds';
        }

        // Update fake log command with the response
        $this->log('Fetching ' . $this->project->source, $clone_output, $fetch->response, $prelog_fetch);
        if (! $fetch->success) {
            return $this->failed();
        }
    }

    public function run()
    {
        $this->getProject();
        $this->getConfig();

        $command = new \Criterion\Helper\Command($this->project, $this);

        // Push pending status to github
        if ($this->project->provider === 'github' && $this->project->github['token']) {
            $github_status = \Criterion\Helper\Github::updateStatus('pending', $this, $this->project);
            $this->log('Posting "running" status to Github', $github_status ? 'Success' : 'Failed');
        }

        if ($this->type === 'criterion') {
            // Check the config file
            if (! $this->config['content']) {
                return $this->failed();
            }

            // Run any setup commands that we have
            if (count($this->config['content']['setup'])) {

                foreach ($this->config['content']['setup'] as $setup) {

                    $response = $command->execute($setup);
                    if (! $response->success) {
                        return $this->failed();
                    }
                }
            }

            // Run any script commands we have
            if (count($this->config['content']['script'])) {

                foreach ($this->config['content']['script'] as $script) {

                    $response = $command->execute($script);
                    if (! $response->success) {
                        return $this->failed();
                    }
                }
            }
        } elseif ($this->type === 'phpunit') {
            // Check to see if a composer.json file exists, if it does then
            // we need to run "composer install" to get all dependencies
            $is_composer = \Criterion\Helper\Test::isComposer($this->path);
            if ($is_composer) {

                $response = $command->execute('composer install');
                if (! $response->success) {
                    return $this->failed();
                }
            }

            // Because there are a few ways of running phpunit, we need to
            // check them. First we check the vendor dir in case composer
            // has installed it.
            if (file_exists($this->path . '/vendor/bin/phpunit')) {
                $response = $command->execute('vendor/bin/phpunit');
                if (! $response->success) {
                    return $this->failed();
                }
            } else {
                $response = $command->execute('phpunit');
                if (! $response->success) {
                    return $this->failed();
                }
            }
        } else {
            return $this->failed();
        }

        return $this->passed();
    }
}
