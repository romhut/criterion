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
        $worker = uniqid();
        $output->writeln('<comment>Worker Started: ' . $worker . '</comment>');
        $output->writeln('');

        $client = new \MongoMinify\Client('mongodb://127.0.0.1:27017', array('connect' => true));
        $tests = $client->criterion->tests;
        $projects = $client->criterion->projects;

        while (true)
        {
            $test = $tests->findAndModify(array(
                'status.code' => '4'
            ), array(
                '$set' => array(
                    'worker' => $worker,
                    'status' => array(
                        'code' => '3',
                        'message' => 'Running'
                    )
                )
            ));

            if ($test)
            {
                $output->writeln('-----------');
                $project_id = (string) $test['project_id'];
                $test_id = (string)  $test['_id'];

                $project = $projects->findOne(array(
                    '_id' => $test['project_id']
                ));

                if ($project)
                {
                    $output->writeln('<comment>Test Started</comment>');
                    $output->writeln(' - Project:' . $project['repo']);
                    $output->writeln(' - Test ID:' . $test_id);

                    $shell_command = $input->getOption('debug') ? 'passthru' : 'shell_exec';
                    $shell_command('php ' . ROOT . '/bin/cli test ' . $test_id);

                    $output->writeln('<info>Test has finished</info>');
                }
                else
                {
                    $output->writeln('<error>Project does not exist, removing test.</error>');
                    $tests->remove(array(
                        '_id' => $test['_id']
                    ));
                }

                $output->writeln('');
            }

            sleep(10);
        }
    }
}
