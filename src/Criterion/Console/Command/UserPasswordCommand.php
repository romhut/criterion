<?php
namespace Criterion\Console\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UserPasswordCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('user:password')
            ->setDescription('Changed a user password')
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
            'Enter new password: '
        );
        if (! $password) {
            $output->writeln('<error>Cannot set a blank password</error>');
            exit;
        }

        // Check if user exists
        $user = $this->getApplication()->db->selectCollection('users')->findOne(array(
            '_id' => $username
        ));
        if (! $user){
            $output->writeln('<error>Could not find user</error>');
            return false;
        }

        // Change the users password
        $this->getApplication()->db->selectCollection('users')->update(array(
            '_id' => $username
        ), array(
            '$set' => array(
                'password' => password_hash($password, PASSWORD_BCRYPT, array('cost' => 12))
            )
        ));
        $output->writeln('<info>Password updated for ' . $username  . '</info>');

    }
}
