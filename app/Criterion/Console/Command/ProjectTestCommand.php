<?php
namespace Criterion\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class ProjectTestCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('project:test')
            ->setDescription('Run a test for project')
            ->addArgument(
                'project',
                InputArgument::REQUIRED,
                'Project'
            )
            ->addArgument(
                'branch',
                InputArgument::REQUIRED,
                'Branch'
            )
            ->addOption(
                'debug',
                null,
                InputOption::VALUE_NONE,
                'If set, the worker will run in verbose mode'
            )
            ->addOption(
                'skip-open',
                null,
                InputOption::VALUE_NONE,
                'Should we skip the "open" command if the test fails?'
            )
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $worker = uniqid();
        $app = new \Criterion\Application();

        $test = new \Criterion\Model\Test();
        $test->project_id = new \MongoId($input->getArgument('project'));
        $test->branch = $input->getArgument('branch');
        $test->save();

        $test_id = (string) $test->id;

        if ($test->exists) {
            exec('php ' . ROOT . '/bin/cli test ' . $test_id, $response, $pass);

            if ($pass != 0) {
                $output->writeln('<error>Criterion tests failed</error>');

                if (! $input->getOption('skip-open')) {
                    exec('open ' . $app->config['url'] . '/test/' . $test_id);
                }

            }

            exit ((int)$pass);
        }
    }
}
