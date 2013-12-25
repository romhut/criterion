<?php

namespace Criterion\Console\Runner;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class JobCommand extends Command
{
    /**
     * Setup the Job command
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('runner:job')
            ->setDescription('Run a job')
        ;
    }

    /**
     * Run the Job command
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // TODO: Get the real Job
        $job = new \Criterion\Model\Job();

        // Get the Config for this Job
        $config = new \Criterion\Config($job);

        // Subscribe the Hooks to Events
        $job->addSubscriptions(
            $config->getHooks()
        );

        // Notify Services that we have started the job
        $job->event(\Criterion\Model\Job::EVENT_STARTED);

        // Loop over each the defined Services and
        // execute the command.
        foreach ($config->getServices() as $service) {

            $output->writeln('- Executing Service: ' . $service->getName());

            // Run the service
            $command = $service->execute();

            // Log the Command result
            $job->log(
                $command->getCommand(),
                $command->getOutput(),
                $command->getStatus()
            );

            // If this command fails, then we notify the Services
            if ($command->getStatus() !== 0) {
                $output->writeln('  <error>failure</error>');
                $job->event(\Criterion\Model\Job::EVENT_FAILURE);
                break;
            } else {
                $output->writeln('  <info>success</info>');
            }
        }

        // Notify Hooks that the job was a success
        if ($job->status !== \Criterion\Model\Job::EVENT_FAILURE) {
            $job->event(\Criterion\Model\Job::EVENT_SUCCESS);
        }

        // Notify Hooks that we have finished the job
        $job->event(\Criterion\Model\Job::EVENT_FINISHED);
        $job->save();
    }
}
