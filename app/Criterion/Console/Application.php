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

    public function executeAndLog($command)
    {

        ob_start();
        passthru($command . ' 2>&1', $response);
        $output = ob_get_contents();
        ob_end_clean();

        $output = trim($output);
        $output = str_replace(TEST_DIR, null, $output);

        $data = array(
            'output' => $output,
            'response' => (string) $response,
            'command' => $command,
            'test_id' => new \MongoId($this->test),
            'project_id' => $this->project['_id'],
            'time' => new \MongoDate()
        );
        $this->db->logs->save($data);

        $output = $response == 0 ? "<info>success</info>" : "<error>failed</error>";

        $this->output->writeln($data['command']);
        $this->output->writeln('... ' . $output);
        $this->output->writeln('');

        return $data;
    }

    public function testFailed($command_response)
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

        if (isset($this->project['fail']) && count($this->project['fail']))
        {
            foreach ($this->project['fail'] as $fail)
            {
                $response = $this->executeAndLog($fail);
            }
        }

        $path = TEST_DIR . '/' . $this->project['_id']  . '/' . (string) $this->test;
        $this->executeAndLog(sprintf('rm -rf %s', $path));

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

        if (isset($this->project['pass']) && count($this->project['pass']))
        {
            foreach ($this->project['pass'] as $pass)
            {
                $response = $this->executeAndLog($pass);
            }
        }

        $path = TEST_DIR . '/' . $this->project['_id']  . '/' . (string) $this->test;
        $this->executeAndLog(sprintf('rm -rf %s', $path));

        $this->output->writeln('<question>test passed</question>');
        return false;
    }

    public function parseConfig($config)
    {
        $project = Yaml::parse($config);

        if( ! isset($project) || ! is_array($project))
        {
            $data = array(
                'output' => 'The .criterion.yml file does not seem valid, or does not exist',
                'response' => '1',
                'command' => 'Checking .criterion.yml',
                'test_id' => $this->test,
                'project_id' => $this->project['_id'],
                'time' => new \MongoDate()
            );

            $this->db->logs->save($data);

            return false;
        }

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

        return $project;
    }


}
