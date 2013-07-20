<?php
namespace Criterion\Console;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;


class Application extends SymfonyApplication
{
    public $mongo = null;
    public $db = array();
    public $app = array();
    public $criterion = array();

    public $data = array();

    public function __construct($name, $version)
    {
        parent::__construct($name, $version);
        $this->app = new \Criterion\Application();
        $this->mongo = $this->app->mongo;
        $this->db = $this->app->db;
    }

    public function __set($key, $val)
    {
        $this->data[$key] = $val;
    }

    public function __get($key)
    {
        if (isset($this->data[$key]))
        {
            return $this->data[$key];
        }

        return null;
    }

    public function executeAndLog($command_string, $internal = false)
    {
        $prelog = $this->preLog($command_string, $internal);

        $command_string = str_replace('{path}', $this->test->path, $command_string);

        $command = new \Criterion\Helper\Command();
        $command->execute($command_string);
        $log = $this->log($command->command, $command->output, $command->response, $prelog, $internal);

        $output = $command->response == 0 ? "<info>success</info>" : "<error>failed</error>";
        $this->output->writeln($command->command);
        $this->output->writeln('... ' . $command->output);
        $this->output->writeln('');

        return $log;
    }

    // Add a log item before the command has run
    public function preLog($command, $internal = false)
    {
        $command = str_replace(DATA_DIR, null, $command);

        $log = new \Criterion\Model\Log();
        $log->output = 'Running...';
        $log->response = false;
        $log->command = $command;
        $log->test_id = $this->test->id;
        $log->time = new \MongoDate();
        $log->status = '0';
        $log->internal = $internal;
        $log->save();
        return $log->id;
    }

    public function log($command, $output, $response = '0', $log_id = null, $internal = false)
    {
        $command = str_replace(DATA_DIR, null, $command);
        $output = str_replace(DATA_DIR, null, $output);

        $log = new \Criterion\Model\Log($log_id);
        $log->output = $output;
        $log->response = (string) $response;
        $log->command = $command;
        $log->test_id = $this->test->id;
        $log->project_id = $this->project->id;
        $log->time = new \MongoDate();
        $log->status = '1';
        $log->internal = $internal;
        $log->save();
        return $log;
    }

    public function testFailed()
    {
        $this->test->status = array(
            'message' => 'Failed',
            'code' => '0',
        );
        $this->test->finished = new \MongoDate();
        $this->test->save();

        $this->project->status = array(
            'message' => 'Failed',
            'code' => '0',
        );
        $this->project->last_run = new \MongoDate();
        $this->project->save();

        $this->output->writeln('');
        $this->output->writeln('<question>Running "fail" commands</question>');

        if ($this->project->provider === 'github' && $this->project->github['token'])
        {
            $github_status = \Criterion\Helper\Github::updateStatus('error', $this->test, $this->project);
            $this->log('Posting "error" status to Github', $github_status ? 'Success' : 'Failed');
        }

        \Criterion\Helper\Notifications::failedEmail($this->test->id, $this->project);

        if (isset($this->criterion['fail']) && count($this->criterion['fail']))
        {
            foreach ($this->criterion['fail'] as $fail)
            {
                $response = $this->executeAndLog($fail);
            }
        }

        $path = TEST_DIR . '/' . $this->project->id  . '/' . (string) $this->test->id;
        $this->executeAndLog(sprintf('rm -rf %s', $path), true);

        $this->output->writeln('<error>test failed</error>');
        return false;
    }

    public function testPassed()
    {
        $this->test->status = array(
            'message' => 'Passed',
            'code' => '1'
        );
        $this->test->finished = new \MongoDate();
        $this->test->save();

        $this->project->status = array(
            'message' => 'Passed',
            'code' => '1'
        );
        $this->project->last_run = new \MongoDate();
        $this->project->save();

        $this->output->writeln('');
        $this->output->writeln('<question>Running "pass" commands</question>');

        if ($this->project->provider === 'github' && $this->project->github['token'])
        {
            $github_status = \Criterion\Helper\Github::updateStatus('success', $this->test, $this->project);
            $this->log('Posting "success" status to Github', $github_status ? 'Success' : 'Failed');
        }

        if (isset($this->criterion['pass']) && count($this->criterion['pass']))
        {
            foreach ($this->criterion['pass'] as $pass)
            {
                $response = $this->executeAndLog($pass);
            }
        }

        $path = TEST_DIR . '/' . $this->project->id  . '/' . (string) $this->test->id;
        $this->executeAndLog(sprintf('rm -rf %s', $path), true);

        $this->output->writeln('<question>test passed</question>');
        return false;
    }

    // Parse a criterion.yml file, and log the results
    public function parseConfig($config)
    {
        $criterion = Yaml::parse($config);

        $command = 'Parsing .criterion.yml file';
        $prelog = $this->prelog($command);

        if( ! isset($criterion) || ! is_array($criterion))
        {
            $this->log($command, 'The .criterion.yml file does not seem valid, or does not exist', '1', $prelog);
            return false;
        }

        $this->log($command, 'Successfully parsed .criterion.yml file', '0', $prelog);

        foreach (array('setup', 'test', 'fail', 'pass') as $section)
        {
            if ( ! isset($criterion[$section]) ||  ! is_array($criterion[$section]))
            {
                 $criterion[$section] = array();
            }
        }

        $this->test->config = $criterion;
        $this->test->save();
        $this->criterion = $criterion;
        return $criterion;
    }
}
