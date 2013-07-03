<?php
namespace CI;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Input\InputInterface;

class Application extends SymfonyApplication
{
	public $mongo = null;
	public $project = null;

	public function setMongo($mongo)
	{
		$this->mongo = $mongo;
	}

	public function setProject($project)
	{
		$this->project = $project;
	}

	public function executeAndLog($command, $build_id, $project = [])
	{
		$db = $this->mongo->ci;
		$collection = $db->logs;

		ob_start();
		passthru($command . ' 2>&1', $response);
		$output = ob_get_contents();
		ob_end_clean();

		$data = [
			'output' => trim($output),
			'response' => $response,
			'command' => $command,
			'build_id' => $build_id
		];
		$collection->save($data);

		echo $data['command'] . ":\n";
		echo $data['output'] . "\n\n";

		if ($response != 0)
		{
			if ($project)
			{
				foreach ($project['tests']['fail'] as $fail)
				{
					$this->executeAndLog($fail, $build_id);
				}
			}
		}
		else
		{
			if ($project)
			{
				if ($command === end($project['tests']['test']) || !count($project['tests']['test']))
				{
					foreach ($project['tests']['success'] as $success)
					{
						$this->executeAndLog($success, $build_id);
					}
				}
			}
		}

		return $data;
	}
}