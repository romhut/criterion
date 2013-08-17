<?php
namespace Criterion\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UserCreateCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('user:create')
            ->setDescription('Create a new user')
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

        // Prompt for password
        $dialog = $this->getHelperSet()->get('dialog');

        // Check if user exists
        $user = new \Criterion\Model\User(
            array(
                'username' => $username
            )
        );

        if ($user->exists) {
            $output->writeln('<error>User already exists</error>');

            return false;
        }

        $user->username = $username;
        $user->password = $dialog->askHiddenResponse(
            $output,
            'Enter password: '
        );

        if (! $user->password) {
            $output->writeln('<error>Cannot set a blank password</error>');
            exit;
        }

        $user->password = $user->password();
        $admin = strtolower($dialog->ask($output, 'Do you want this user to be an admin? [y/N]: '));
        $user->role = 'user';
        if ($admin === 'y') {
            $user->role = 'admin';
        }

        $user->save();
        $output->writeln('<info>User created (' . $user->_id . ':' . $user->role . ')</info>');

    }
}
