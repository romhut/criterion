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
            ->setDescription('Run a test using the test ID.')
             ->setDefinition(array(
                new InputArgument('test_id', InputArgument::REQUIRED, 'The test ID', null),
             ))
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $test_id = new \MongoId($input->getArgument('test_id'));
        $test = new \Criterion\Model\Test($test_id);

        if ( ! $test->exists)
        {
            $output->writeln('<error>No test found</error>');
            return false;
        }

        $project_id = $test->project_id;
        $project = $test->getProject();

        if ( ! $project->exists)
        {
            $output->writeln('<error>No project found</error>');
            return false;
        }

        // Check to see if the current status is not already "running".
        // The reason for this is that the worker sets it to 3 atomically,
        // however, these tests can be run manually via the console.
        if ($test->status['code'] !== '3')
        {
            $test->status = array(
                'code' => '3',
                'message' => 'Running'
            );

            $test->save();
        }

        $output->writeln('Criterion test has started...');
        $output->writeln('     - Project: '. (string) $project_id);
        $output->writeln('     - test: '.  (string) $test_id);
        $output->writeln('');

        $project_folder = TEST_DIR . '/' . (string) $project_id;
        $test_folder = $project_folder . '/' . (string) $test_id;
        if ( ! is_dir($project_folder))
        {
            mkdir($project_folder, 0777, true);
        }

        // Reset to master branch if there is no branch specified
        if ( ! $test->branch)
        {
            $test->branch = 'master';
        }

        $test->path = $test_folder;

        // Pass the test into the application for future use
        $this->getApplication()->test = $test;

        // Pass the project and output variables into the application.
        // This allows for a consistant output, and makes it easier to
        // reference the project details
        $this->getApplication()->project = $project;
        $this->getApplication()->output = $output;

        if (is_array($project->enviroment_variables))
        {
            $set_env_variables = $this->getApplication()->preLog('Setting enviroment variables');

            $env_variables = array();
            foreach ($project->enviroment_variables as $env_var)
            {
                $env_variables[] = $env_var;
                putenv($env_var);
            }
            $this->getApplication()->log('Setting enviroment variables', implode(', ',$env_variables), 0, $set_env_variables);
        }

        // Switch to the project directory, and clone the repo into it.
        chdir($project_folder);

        // Add a fake "clone" log entry. This is a lot cleaner when outputing the logs.
        $prelog_clone = $this->getApplication()->preLog('Cloning ' . $project->repo);
        $clone_start = microtime(true);

        // Get a fully formatted clone command, and then run it.
        $clone_command = \Criterion\Helper\Repo::cloneCommand($test, $project);
        $git_clone = $this->getApplication()->executeAndLog($clone_command, true);

        $clone_end = microtime(true);
        $clone_output = 'Failed';
        if ($git_clone->response === '0')
        {
            $clone_output = 'Cloned in ' . number_format($clone_end - $clone_start) . ' seconds';
        }

        // Update fake log command with the response
        $this->getApplication()->log('Cloning ' . $project->repo, $clone_output, $git_clone->response, $prelog_clone);
        if ($git_clone->response != 0)
        {
            return $this->getApplication()->testFailed();
        }

        // Switch into the test directory we just cloned, so we can
        // run all future commands from here
        chdir($test_folder);

        // Fetch the commit info from the commit helper
        $commit = \Criterion\Helper\Commit::getInfo($project->repo, $test->branch);

        // Check to see if the commit is testable
        if ( ! \Criterion\Helper\Commit::isValid($commit))
        {
            $test->delete();
            return false;
        }

        // Detect the test type. E.G. if .criterion.yml file does
        // not exist, it may be a PHPUnit project
        $test_type = \Criterion\Helper\Test::detectType($test_folder);
        $this->getApplication()->log('Detecting test type', $test_type ?: 'Not Found', $test_type ? '0' : '1');

        // Update the current test with some details we just gathered
        // such as: repo, commit info, and test type
        $test->commit = $commit;
        $test->repo = $project->repo;
        $test->type = $test_type;
        $test->save();

        // Push pending status to github
        if ($project->provider === 'github' && $project->github['token'])
        {
            $github_status = \Criterion\Helper\Github::updateStatus('pending', $test, $project);
            $this->getApplication()->log('Posting "running" status to Github', $github_status ? 'Success' : 'Failed');
        }

        $config_file = realpath($test_folder . '/.criterion.yml');
        $criterion = $this->getApplication()->parseConfig($config_file);

        if ($test_type === 'criterion')
        {
            // Check the config file
            if ( ! $criterion)
            {
                return $this->getApplication()->testFailed();
            }

            // Run any setup commands that we have
            $output->writeln('<question>Running "setup" commands</question>');
            if (count($criterion['setup']))
            {
                foreach ($criterion['setup'] as $setup)
                {
                    $response = $this->getApplication()->executeAndLog($setup);
                    if ($response->response !== '0')
                    {
                        return $this->getApplication()->testFailed();
                    }
                }
            }

            // Run any script commands we have
            $output->writeln('<question>Running "script" commands</question>');
            if (count($criterion['script']))
            {
                foreach ($criterion['script'] as $script)
                {
                    $response = $this->getApplication()->executeAndLog($script);
                    if ($response->response !== '0')
                    {
                        return $this->getApplication()->testFailed();
                    }
                }
            }
        }
        elseif ($test_type === 'phpunit')
        {
            // Check to see if a composer.json file exists, if it does then
            // we need to run "composer install" to get all dependancies
            $is_composer = \Criterion\Helper\Test::isComposer($test_folder);
            if ($is_composer)
            {
                $response = $this->getApplication()->executeAndLog('composer install');
                if ($response->response !== '0')
                {
                    return $this->getApplication()->testFailed();
                }
            }

            // Because there are a few ways of running phpunit, we need to
            // check them. First we check the vendor dir incase composer
            // has installed it.
            if (file_exists($test_folder . '/vendor/bin/phpunit'))
            {
                $response = $this->getApplication()->executeAndLog('vendor/bin/phpunit');
                if ($response->response !== '0')
                {
                    return $this->getApplication()->testFailed();
                }
            }
            // If composer has not installed phpunit, then we can run the bin
            // command instead.
            else
            {
                $response = $this->getApplication()->executeAndLog('phpunit');
                if ($response->response !== '0')
                {
                    return $this->getApplication()->testFailed();
                }
            }
        }
        else
        {
            return $this->getApplication()->testFailed();
        }

        // The test has passed, update the test status, and project status
        return $this->getApplication()->testPassed();
    }
}
