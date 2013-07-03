<?php
namespace CI\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends Command
{
	public $app = null;
	public function __construct($var, $app)
	{
		parent::__construct($var);
		$this->app = $app;
	}
    protected function configure()
    {
        $this
            ->setName('test')
            ->setDescription('Run a test')
             ->setDefinition(array(
                new InputArgument('project_id', InputArgument::REQUIRED, 'Project ID', null),
             ))
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	$build_id = 'build_' . uniqid();
    	$project_id = $input->getArgument('project_id');
        $output->writeln('Running tests for project: ' . $project_id);

        $project_folder = TEST_DIR . '/' . $project_id;
        $build_folder = $project_folder . '/' . $build_id;
        if ( ! is_dir($project_folder))
        {
        	mkdir($project_folder, 0777, true);
        }

        $project = [
        	'git' => 'git@github.com:scottymeuk/ping',
        	'tests' => [
        		'setup' => [

        		],
        		'test' => [
        			"echo 'hi'"
        		],
        		'success' => [

        		],
        		'fail' => [

        		]

        	]
       	];

       	array_unshift($project['tests']['setup'], sprintf('git clone %s %s', $project['git'], $build_id));
       	$project['tests']['fail'][] = sprintf('rm -rf %s', $build_id);
       	$project['tests']['success'][] = sprintf('rm -rf %s', $build_id);

       	$original_dir = getcwd();
       	chdir($project_folder);

       	foreach ($project['tests']['setup'] as $setup)
       	{
       		$this->app->executeAndLog($setup, $build_id, $project);
       	}

       	foreach ($project['tests']['test'] as $test)
       	{
       		$this->app->executeAndLog($test, $build_id, $project);
       	}

       	chdir($original_dir);

    }

}