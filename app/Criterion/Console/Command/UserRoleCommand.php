<?php
namespace Criterion\Console\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UserRoleCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('user:role')
            ->setDescription('Change a users role')
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'Username'
            )
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $username = trim(strtolower($input->getArgument('username')));

        // Check if user exists
        $user = new \Criterion\Model\User(array(
            'username' => $username
        ));

        if ( ! $user->exists)
        {
            $output->writeln('<error>Could not find user</error>');
            return false;
        }

        // Prompt for confirm
        $dialog = $this->getHelperSet()->get('dialog');
        $admin = strtolower($dialog->ask($output, 'Do you want this user to be an admin? [y/N]: '));

        $user->role = 'user';
        if ($admin === 'y')
        {
            $user->role = 'admin';
        }

        $user->save();
        $output->writeln('<info>User: ' . $username  . ' has assigned: '.$user->role.'</info>');

    }
}
