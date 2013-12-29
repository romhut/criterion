<?php

namespace Criterion\Console\Worker;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class JobCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('worker:job')
            ->setDescription('Start the job worker')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

    }
}
