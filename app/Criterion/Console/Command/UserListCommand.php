<?php
namespace Criterion\Console\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UserListCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('user:list')
            ->setDescription('List all users')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Check for users
        $users = $this->getApplication()->db->selectCollection('users')->find();
        if ($users->count() < 1)
        {
            $output->writeln('<error>Could not find any users</error>');
            return false;
        }

        $i = 0;
        foreach ($users as $user)
        {
            echo '[' . (++$i) . '] ' . $user['username'] . ':' . (isset($user['role']) ? $user['role'] : 'user') . "\n";
        }
    }
}
