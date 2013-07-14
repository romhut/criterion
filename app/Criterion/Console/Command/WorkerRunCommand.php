<?php
namespace Criterion\Console\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class WorkerRunCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('worker:start')
            ->setDescription('Start a worker')
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

        $output->writeln('<comment>Worker Started</comment>');
        $output->writeln('');

        $client = new \MongoMinify\Client('mongodb://127.0.0.1:27017', array('connect' => true));
        $tests = $client->criterion->tests;

        while (true)
        {
            $test = $tests->findAndModify(array(
                'status.code' => '4'
            ), array(
                '$set' => array(
                    'status' => array(
                        'code' => '3',
                        'message' => 'Running'
                    )
                )
            ));

            if ($test)
            {
                $output->writeln('-----------');
                $project = (string) $test['project_id'];
                $test = (string)  $test['_id'];

                $output->writeln('<comment>Test Started</comment>');
                $output->writeln(' - Project ID:' . $project);
                $output->writeln(' - Test ID:' . $test);

                $shell_command = $input->getOption('debug') ? 'passthru' : 'shell_exec';
                $shell_command('php ' . ROOT . '/bin/cli test ' . $test);

                $output->writeln('<info>Done</info>');
                $output->writeln('');
            }

            sleep(10);
        }
    }
}
