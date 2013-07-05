<?php
namespace Criterion\Console\Command;
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
                new InputArgument('test_id', InputArgument::REQUIRED, 'test ID', null),
             ))
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $project_id = new \MongoId($input->getArgument('project_id'));
        $test_id = new \MongoId($input->getArgument('test_id'));

        $project = $this->getApplication()->db->projects->findOne(array(
            '_id' => $project_id
        ));

        if ( ! $project)
        {
            $output->writeln('<error>No Project Found</error>');
            return false;
        }

        $get_test = $this->getApplication()->db->tests->findOne(array(
            '_id' => $test_id
        ));

        if ( ! $project)
        {
            $output->writeln('<error>No Project Found</error>');
            return false;
        }

        $data = array(
            'status' => array(
                'code' => '3',
                'message' => 'Running'
            )
        );

        $this->getApplication()->db->tests->update(array(
            '_id' => $test_id,
            'project_id' => $project_id
        ), array(
            '$set' => $data
        ));

        $this->getApplication()->setTest($test_id);

        $test_id = (String) $test_id;

        $output->writeln('Criterion test has started...');
        $output->writeln('     - Project: '. (String) $project_id);
        $output->writeln('     - test: '.  $test_id);
        $output->writeln('');

        $project_folder = TEST_DIR . '/' . (String) $project_id;
        $test_folder = $project_folder . '/' . $test_id;
        if ( ! is_dir($project_folder))
        {
            mkdir($project_folder, 0777, true);
        }

        if ( ! isset($get_test['branch']))
        {
            $get_test['branch'] = 'master';
        }

        $this->getApplication()->setProject($project);
        $this->getApplication()->setOutput($output);

        $output->writeln('<question>Running "setup" commands</question>');
        $original_dir = getcwd();
        chdir($project_folder);
        $git_clone = $this->getApplication()->executeAndLog(sprintf('git clone -b %s --depth=1 %s %s', $get_test['branch'], $project['repo'], $test_id));

        if ($git_clone['response'] != 0)
        {
            return $this->getApplication()->testFailed($git_clone);
        }

        $config_file = $project_folder . '/' . $test_id . '/criterion.yml';
        $project_config = $this->getApplication()->parseConfig($config_file);

        if ( ! $project_config)
        {
            return $this->getApplication()->testFailed('Config file invalid.');
        }

        $project = $project + $project_config;
        $this->getApplication()->setProject($project);

        chdir($project_folder . '/' . $test_id);

        exec("git --no-pager show -s --format='%h'", $short_hash);
        $commit['hash']['short'] = $short_hash[0];

        exec("git --no-pager show -s --format='%H'", $long_hash);
        $commit['hash']['long'] = $long_hash[0];

        exec("git --no-pager show -s --format='%an' " . $commit['hash']['long'], $author_name);
        $commit['author']['name'] = $author_name[0];

        exec("git --no-pager show -s --format='%ae' " . $commit['hash']['long'], $author_email);
        $commit['author']['email'] = $author_email[0];

        exec("git --no-pager show -s --format='%s' " . $commit['hash']['long'], $message);
        $commit['message'] = $message[0];

        exec("git show --format='%ci' " . $commit['hash']['long'], $date);
        $commit['date'] = new \MongoDate(strtotime($date[0]));

        $this->getApplication()->db->tests->update(array(
            '_id' => new \MongoId($test_id),
            'project_id' => $project_id,
        ), array(
            '$set' => array(
                'commit' => $commit,
                'branch' => $get_test['branch'],
                'repo' => $project['repo']
            )
        ));

        if (count($project['commands']['setup']))
        {
            foreach ($project['commands']['setup'] as $setup)
            {
                $response = $this->getApplication()->executeAndLog($setup);
                if ($response['response'] !== '0')
                {
                    return $this->getApplication()->testFailed($response);
                }
            }
        }

        $output->writeln('<question>Running "test" commands</question>');

        if (count($project['commands']['test']))
        {
            foreach ($project['commands']['test'] as $test)
            {
                $response = $this->getApplication()->executeAndLog($test);
                if ($response['response'] !== '0')
                {
                    return $this->getApplication()->testFailed($response);
                }
            }
        }
        else
        {
            $output->writeln('No tests to run');
        }

        return $this->getApplication()->testPassed();
    }
}
