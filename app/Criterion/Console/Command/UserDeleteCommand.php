<?php
namespace Criterion\Console\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UserDeleteCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('user:delete')
            ->setDescription('Delete a user')
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

        // Prompt for confirm
        $dialog = $this->getHelperSet()->get('dialog');
        $confirm = $dialog->askConfirmation(
            $output,
            '<question>Are you sure? (y/N)</question>',
            false
        );
        if (! $confirm) {
            $output->writeln('<error>User not deleted</error>');
            exit;
        }

        // Check if user exists
        $user = new \Criterion\Model\User($username);
        if ( ! $user->exists)
        {
            $output->writeln('<error>Could not find user</error>');
            return false;
        }

        $user->delete();
        $output->writeln('<info>User: ' . $username  . ' has been deleted</info>');

    }
}
