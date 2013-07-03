<?php
namespace CI;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class Application extends SymfonyApplication
{
	public $mongo = null;
	public $project = array();
	public $build = null;
	public $output = null;
	public $db = array();

	public function setMongo($mongo)
	{
		$db = $mongo->ci;
        $this->db['builds'] = $db->builds;
        $this->db['logs'] = $db->logs;

		$this->mongo = $mongo;
	}

	public function setProject($project)
	{
		$this->project = $project;
	}

	public function setBuild($build)
	{
		$this->build = $build;
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

		$data = array(
			'output' => trim($output),
			'response' => $response,
			'command' => $command,
			'build_id' => $this->build,
			'project_id' => $this->project['id']
		);
		$this->db['logs']->save($data);

		$output = $response == 0 ? "<info>success</info>" : "<error>failed</error>";

		$this->output->writeln($data['command']);
		$this->output->writeln('... ' . $output);
		$this->output->writeln('');

		return $data;
	}

	public function buildFailed($command_response)
	{
		$this->db['builds']->update(array(
            'build_id' => $this->build,
            'project_id' => $this->project['id'],
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

		$this->output->writeln('');
		$this->output->writeln('<question>Running failed</question>');
        foreach ($this->project['commands']['fail'] as $fail)
        {
            $response = $this->executeAndLog($fail);
        }

        $this->output->writeln('<error>Build failed</error>');
		return false;
	}

	public function buildPassed()
	{
		$this->db['builds']->update(array(
            'build_id' => $this->build,
            'project_id' => $this->project['id'],
        ), array(
            '$set' => array(
                'status' => array(
                    'message' => 'Passed',
                    'code' => '1'
                ),
                'finished' => new \MongoDate()
            )
        ));

		$this->output->writeln('');
		$this->output->writeln('<question>Running success</question>');
        foreach ($this->project['commands']['pass'] as $pass)
        {
            $response = $this->executeAndLog($pass);
        }

        $this->output->writeln('<question>Build passed</question>');
		return false;
	}


}