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
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $worker = uniqid();
        $output->writeln('<comment>Worker Running for Project</comment>');
        $output->writeln('');

        $app = new \Criterion\Application();

        $test = new \Criterion\Model\Test();
        $test->project_id = new \MongoId($input->getArgument('project'));
        $test->branch = $input->getArgument('branch');
        $test->save();

        $test_id = (string) $test->id;

        if ($test->exists) {

            $output->writeln('-----------');
            $output->writeln('<comment>Test Started</comment>');

            $shell_command = $input->getOption('debug') ? 'passthru' : 'shell_exec';
            $shell_command('php ' . ROOT . '/bin/cli test ' . $test_id);

            $output->writeln('<info>Test Finished</info>');
            $output->writeln('');
        }
    }
}
