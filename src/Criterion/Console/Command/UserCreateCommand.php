<?php
namespace Criterion\Console\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
        $password = $dialog->askHiddenResponse(
            $output,
            'Enter password: '
        );
        if (! $password) {
            $output->writeln('<error>Cannot set a blank password</error>');
            exit;
        }

        // Check if user exists
        $user = $this->getApplication()->db->selectCollection('users')->findOne(array(
            '_id' => $username
        ));
        if ($user){
            $output->writeln('<error>User already exists</error>');
            return false;
        }

        // Create User
        $user = array(
            '_id' => $username,
            'password' => password_hash($password, PASSWORD_BCRYPT, array('cost' => 12))
        );
        $this->getApplication()->db->selectCollection('users')->save($user);
        $output->writeln('<info>User created!</info>');

    }
}
