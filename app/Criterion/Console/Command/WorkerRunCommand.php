<?php
namespace Criterion\Console\Command;
use Symfony\Component\Console\Command\Command;
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
            ->addOption(
               'interval',
               null,
               InputOption::VALUE_OPTIONAL,
               'How often should we poll?'
            )
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * You may think its a bit of a stupid idea polling mongo every
         * {n} seconds instead of using gearman, or beanstalkd. But the reason
         * for this is that we want to reduce the dependancies as much as
         * possible.
         */
        $interval = (int) $input->getOption('interval') ?: 10;
        $worker = uniqid();
        $output->writeln('<comment>Worker Started: ' . $worker . ' (Interval: ' . $interval . ')</comment>');
        $output->writeln('');

        $tests = $this->getApplication()->db->tests;

        while (true) {
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

            $test = new \Criterion\Model\Test(null, $test);

            if ($test->exists) {
                $output->writeln('-----------');
                $project_id = (string) $test->project_id;
                $test_id = (string) $test->id;

                $project = $test->getProject();

                if ($project->exists) {
                    $output->writeln('<comment>Test Started</comment>');
                    $output->writeln(' - Project:' . $project->repo);
                    $output->writeln(' - Test ID:' . $test_id);

                    $shell_command = $input->getOption('debug') ? 'passthru' : 'shell_exec';
                    $shell_command('php ' . ROOT . '/bin/cli test ' . $test_id);

                    $output->writeln('<info>Test has finished</info>');
                } else {
                    $output->writeln('<error>Project does not exist, removing test.</error>');
                    $test->delete();
                }

                $output->writeln('');
            }

            sleep($interval);
        }
    }
}
