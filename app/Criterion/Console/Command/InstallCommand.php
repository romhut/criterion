<?php
namespace Criterion\Console\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('install')
            ->setDescription('Install system')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getHelperSet()->get('dialog');

        // Create default configuration
        $config = array(
            'url' => 'http://criterion.example.com',
            'email' => array(
                "name" => "Criterion Notifications",
                "address" => "notifications@criterion.romhut.com",
            )
        );

        // Overwrite default config with current setup
        if ($this->getApplication()->app->config) {
            $output->writeln('<info>Created config file</info>');
            $config = array_merge($config, $this->getApplication()->app->config);
        }

        // Setup URL
        $default_url = isset($config['url']) ? $config['url'] : 'http://criterion.example.com';
        $url = $dialog->ask($output, 'What is your Criterion URL? [' . $default_url . ']: ', $default_url);
        if ($url) {
            $config['url'] = $url;
        }

        // Create a user to login with
        $output->writeln('<info>You need to create an admin user to login to the web interface</info>');
        $username = $dialog->ask($output, 'Username [admin]: ', 'admin');
        $password = $dialog->ask($output, 'Password: [password]: ', 'password');
        $user = array(
            '_id' => $username,
            'password' => password_hash($password, PASSWORD_BCRYPT, array('cost' => 12))
        );
        $this->getApplication()->db->selectCollection('users')->save($user);

        $output->writeln('<info>Email Setup: Used for notifications</info>');

        $config['email']['address'] = $dialog->ask($output, 'From address [mail@localhost]: ', 'mail@localhost');
        $config['email']['name'] = $dialog->ask($output, 'From name [Criterion Notifications]: ', 'Criterion Notifications');

        $use_smtp = strtolower($dialog->ask($output, 'Do you want to setup SMTP? [y/N]: '));
        if ($use_smtp === 'y')
        {
            $config['email']['smtp']['server'] = $dialog->ask($output, 'SMTP Server [localhost]: ', 'localhost');
            $config['email']['smtp']['port'] = $dialog->ask($output, 'SMTP Port [25]: ', '25');
            $config['email']['smtp']['username'] = $dialog->ask($output, 'SMTP Username [mail@localhost]: ', 'mail@localhost');
            $config['email']['smtp']['password'] = $dialog->ask($output, 'SMTP Password [password123]: ', 'password123');
        }

        // Set permissions
        shell_exec('chmod +x ' . ROOT . '/bin/*');

        // Create data folder structure
        if ( ! is_dir(ROOT . '/data/tests'))
        {
            mkdir(ROOT . '/data/tests', 0777, true);
        }

        if ( ! is_dir(ROOT . '/data/keys'))
        {
            mkdir(ROOT . '/data/keys', 0777, true);
        }

        // Save config
        file_put_contents($this->getApplication()->app->config_file, json_encode($config));
        $output->writeln('<info>Saved config settings</info>');

        // Offer samples
        $samples = array(
            'romhut/criterion',
            'marcqualie/mongominify',
            'marcqualie/hoard'
        );
        $install_samples = strtolower($dialog->ask($output, 'Do you want to install sample projects? [y/N]: '));
        if ($install_samples === 'y')
        {
            foreach ($samples as $sample)
            {
                $project = \Criterion\Helper\Project::fromRepo('https://github.com/' . $sample);
                $this->getApplication()->db->selectCollection('projects')->save($project);
                $output->writeln('<info>- Installed ' . $sample . '</info>');
            }
        }

        // Installation complete
        $output->writeln(' ');
        $output->writeln('<info>Installation Complete!</info>');
        $output->writeln('Visit <info>' . $config['url'] . '</info> and login with <info>' . $username . ':' . $password . '</info>');
        $output->writeln('Remember to start the worker: <info>bin/cli worker:start</info>');

    }
}
