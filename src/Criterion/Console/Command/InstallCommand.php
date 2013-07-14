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

        // Create configuration
        $config_dir = ROOT . '/src/Config';
        $config_file = $config_dir . '/config.json';
        if (! file_exists($config_file)) {
            copy($config_file . '.dist', $config_file);
            $output->writeln('<info>Created config file</info>');
        }
        $config = json_decode(file_get_contents($config_file), true);

        // Setup URL
        $default_url = isset($config['url']) ? $config['url'] : 'http://criterion.example.com';
        $url = $dialog->ask($output, 'What is your Criterion URL? [' . $default_url . ']: ', $default_url);
        if ($url) {
            $config['url'] = $url;
        }

        // Set permissions
        shell_exec('chmod +x ' . ROOT . '/bin/*');

        // Save config
        file_put_contents($config_file, json_encode($config));
        $output->writeln('<info>Saved config settings</info>');

        // Offer samples
        $samples = array(
            'romhut/criterion',
            'marcqualie/mongominify',
            'marcqualie/hoard'
        );
        $install_samples = strtolower($dialog->ask($output, 'Do you want to install sample projects? [y/N]: '));
        if ($install_samples === 'y') {
            foreach ($samples as $sample) {
                $project = \Criterion\Helper\Project::fromRepo('https://github.com/' . $sample);
                $this->getApplication()->db->selectCollection('projects')->save($project);
                $output->writeln('<info>- Installed ' . $sample . '</info>');
            }
        }


    }
}
