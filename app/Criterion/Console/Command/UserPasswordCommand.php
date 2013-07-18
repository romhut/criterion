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

         // Check if user exists
        $user = new \Criterion\Model\User(array(
            'username' => $username
        ));

        if ( ! $user->exists)
        {
            $output->writeln('<error>Could not find user</error>');
            return false;
        }

        // Prompt for password
        $dialog = $this->getHelperSet()->get('dialog');
        $user->password = $dialog->askHiddenResponse(
            $output,
            'Enter new password: '
        );

        if (! $user->password)
        {
            $output->writeln('<error>Cannot set a blank password</error>');
            exit;
        }

        $user->password = $user->password();
        $user->save();

        $output->writeln('<info>Password updated for ' . $username  . '</info>');

    }
}
