<?php
namespace Criterion\Console;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;


class Application extends SymfonyApplication
{
    public $mongo = null;
    public $project = array();
    public $test = null;
    public $output = null;
    public $db = array();

    public function setMongo($mongo)
    {
        $this->mongo = $mongo;
        $this->db = $this->mongo->criterion;
    }

    public function setProject($project)
    {
        $this->project = $project;
    }

    public function setTest($test)
    {
        $this->test = $test;
    }

    public function setOutput($output)
    {
        $this->output = $output;
    }

    public function executeAndLog($command_string, $internal = false)
    {
        $prelog = $this->preLog($command_string, $internal);

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
        $log = array(
            'output' => 'Running...',
            'response' => false,
            'command' => $command,
            'test_id' => new \MongoId($this->test),
            'project_id' => $this->project['_id'],
            'time' => new \MongoDate(),
            'status' => '0',
            'internal' => $internal
        );

        $this->db->logs->save($log);
        return $log['_id'];
    }

    public function log($command, $output, $response = '0', $log_id = false, $internal = false)
    {
        $log = array(
            'output' => $output,
            'response' => (string) $response,
            'command' => $command,
            'test_id' => new \MongoId($this->test),
            'project_id' => $this->project['_id'],
            'time' => new \MongoDate(),
            'status' => '1',
            'internal' => $internal
        );

        if ($log_id)
        {
            $this->db->logs->update(array(
                '_id' => $log_id
            ), array(
                '$set' => $log
            ));
        }
        else
        {
            $this->db->logs->save($log);
        }

        return $log;
    }

    public function testFailed($command_response = false)
    {
        $this->db->tests->update(array(
            '_id' => $this->test,
            'project_id' => $this->project['_id'],
        ), array(
            '$set' => array(
                'status' => array(
                    'message' => 'Failed',
                    'code' => '0',
                    'command' => $command_response
                ),
                'finished' => new \MongoDate()
            )
        ));

        $this->db->projects->update(array(
            '_id' => $this->project['_id'],
        ), array(
            '$set' => array(
                'status' => array(
                    'message' => 'Failed',
                    'code' => '0'
                ),
                'last_run' => new \MongoDate()
            )
        ));

        $this->output->writeln('');
        $this->output->writeln('<question>Running "fail" commands</question>');

        if ($this->project['provider'] === 'github' && $this->project['github']['token'])
        {
            $test = $this->db->tests->findOne(array('_id' => $this->test));
            $github_status = \Criterion\Helper\Github::updateStatus('error', $test, $this->project);
            $this->log('Posting "error" status to Github', $github_status ? 'Success' : 'Failed');
        }

        if (isset($this->project['fail']) && count($this->project['fail']))
        {
            foreach ($this->project['fail'] as $fail)
            {
                $response = $this->executeAndLog($fail);
            }
        }

        $path = TEST_DIR . '/' . $this->project['_id']  . '/' . (string) $this->test;
        $this->executeAndLog(sprintf('rm -rf %s', $path), true);

        $this->output->writeln('<error>test failed</error>');
        return false;
    }

    public function testPassed()
    {
        $this->db->tests->update(array(
            '_id' => $this->test,
            'project_id' => $this->project['_id'],
        ), array(
            '$set' => array(
                'status' => array(
                    'message' => 'Passed',
                    'code' => '1'
                ),
                'finished' => new \MongoDate()
            )
        ));

        $this->db->projects->update(array(
            '_id' => $this->project['_id'],
        ), array(
            '$set' => array(
                'status' => array(
                    'message' => 'Passed',
                    'code' => '1'
                ),
                'last_run' => new \MongoDate()
            )
        ));

        $this->output->writeln('');
        $this->output->writeln('<question>Running "pass" commands</question>');

        if ($this->project['provider'] === 'github' && $this->project['github']['token'])
        {
            $test = $this->db->tests->findOne(array('_id' => $this->test));
            $github_status = \Criterion\Helper\Github::updateStatus('success', $test, $this->project);
            $this->log('Posting "success" status to Github', $github_status ? 'Success' : 'Failed');
        }

        if (isset($this->project['pass']) && count($this->project['pass']))
        {
            foreach ($this->project['pass'] as $pass)
            {
                $response = $this->executeAndLog($pass);
            }
        }

        $path = TEST_DIR . '/' . $this->project['_id']  . '/' . (string) $this->test;
        $this->executeAndLog(sprintf('rm -rf %s', $path), true);

        $this->output->writeln('<question>test passed</question>');
        return false;
    }

    // Parse a criterion.yml file, and log the results
    public function parseConfig($config)
    {
        $project = Yaml::parse($config);

        $command = 'Parsing .criterion.yml file';
        $prelog = $this->prelog($command);

        if( ! isset($project) || ! is_array($project))
        {
            $this->log($command, 'The .criterion.yml file does not seem valid, or does not exist', '1', $prelog);
            return false;
        }

        $this->log($command, 'Successfully parsed .criterion.yml file', '0', $prelog);

        foreach (array('setup', 'test', 'fail', 'pass') as $section)
        {
            if ( ! isset($project[$section]) ||  ! is_array($project[$section]))
            {
                 $project[$section] = array();
            }
        }

        $this->db->tests->update(array(
            '_id' => $this->test,
        ), array(
            '$set' => array(
                'config' => $project
            )
        ));

        $this->project = array_merge($this->project, $project);

        return $this->project;
    }
}
