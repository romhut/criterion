<?php
namespace CI\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('test')
            ->setDescription('Run a test')
             ->setDefinition(array(
                new InputArgument('project_id', InputArgument::REQUIRED, 'Project ID', null),
             ))
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $build_id = 'build_' . uniqid();
        $project_id = $input->getArgument('project_id');


        $this->getApplication()->db->builds->save(array(
            'build_id' => $build_id,
            'project_id' => $project_id,
            'started' => new \MongoDate()
        ));

        $output->writeln('CI has started...');
        $output->writeln('     - Project: '. $project_id);
        $output->writeln('     - Build: '. $build_id);
        $output->writeln('');

        $project_folder = TEST_DIR . '/' . $project_id;
        $build_folder = $project_folder . '/' . $build_id;
        if ( ! is_dir($project_folder))
        {
            mkdir($project_folder, 0777, true);
        }

        $project = array(
            'id' => $project_id,
            'repo' => 'git@github.com:romhut/api',
            'branch' => 'master',
            'commands' => array(
                'setup' => array(
                    'composer install --dev',
                ),
                'test' => array(
                    // 'vendor/bin/phpunit'
                ),
                'pass' => array(
                    'echo "pass"'
                ),
                'fail' => array(

                )
            )
        );
        $project['commands']['fail'][] = sprintf('rm -rf %s', $build_id);
        $project['commands']['pass'][] = sprintf('rm -rf %s', $build_id);

        $this->getApplication()->setProject($project);
        $this->getApplication()->setBuild($build_id);
        $this->getApplication()->setOutput($output);

        $output->writeln('<question>Running "setup" commands</question>');
        $original_dir = getcwd();
        chdir($project_folder);
        $this->getApplication()->executeAndLog(sprintf('git clone -b %s --depth=5 %s %s', $project['branch'], $project['repo'], $build_id));
        chdir($project_folder . '/' . $build_id);

        // Get and store git info
        exec('git rev-parse HEAD', $hash);
        $commit['hash'] = $hash[0];

        exec("git --no-pager show -s --format='%an <%ae>' " . $commit['hash'], $author);
        $commit['author'] = $author[0];

        exec("git show --format='%ci' " . $commit['hash'], $date);
        $commit['date'] = new \MongoDate(strtotime($date[0]));

        $this->getApplication()->db->builds->update(array(
            'build_id' => $build_id,
            'project_id' => $project_id,
        ), array(
            '$set' => array(
                'commit' => $commit,
                'branch' => $project['branch'],
                'repo' => $project['repo']
            )
        ));

        if (count($project['commands']['setup']))
        {
            foreach ($project['commands']['setup'] as $setup)
            {
                $response = $this->getApplication()->executeAndLog($setup);
                if ($response['response'] != 0)
                {
                    return $this->getApplication()->buildFailed($response);
                }
            }
        }

        $output->writeln('<question>Running "test" commands</question>');

        if (count($project['commands']['test']))
        {
            foreach ($project['commands']['test'] as $test)
            {
                $response = $this->getApplication()->executeAndLog($test);
                if ($response['response'] != 0)
                {
                    return $this->getApplication()->buildFailed($response);
                }
            }
        }
        else
        {
            $output->writeln('No tests to run');
        }

        return $this->getApplication()->buildPassed();
    }
}