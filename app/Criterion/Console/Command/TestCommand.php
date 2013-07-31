<?php
namespace Criterion\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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

        // Get the project and make sure it exists
        $test = new \Criterion\Model\Test($test_id);
        if (! $test->exists) {
            $output->writeln('<error>Test could not be found.</error>');
            return false;
        }

        // Get the project linked to this test, if its not found, then remove the test
        $project = $test->getProject();
        if (! $project->exists) {
            $output->writeln('<error>Project could not be found, removing test.</error>');
            return false;
        }

        $test->source = $project->source;

        // Check to see if the current status is not already "running".
        // The reason for this is that the worker sets it to 3 atomically,
        // however, these tests can be run manually via the console.
        if ($test->status['code'] !== '3') {
            $test->status = array(
                'code' => '3',
                'message' => 'Running'
            );

            $test->save();
        }

        $output->writeln('Criterion test has started...');
        $output->writeln('     - Project: '. (string) $project->id);
        $output->writeln('     - test: '.  (string) $test->id);
        $output->writeln('');

        $project_folder = TEST_DIR . '/' . (string) $project->id;
        $test_folder = $project_folder . '/' . (string) $test->id;
        if (! is_dir($project_folder)) {
            mkdir($project_folder, 0777, true);
        }

        // Reset to master branch if there is no branch specified
        if (! $test->branch) {
            $test->branch = 'master';
        }

        $test->path = $test_folder;

        // Pass the test into the application for future use
        $command = new \Criterion\Helper\Command($project, $test);

        // Setup the enviroment variables from project config
        $test->setEnviromentVariables();

        // Switch to the project directory, and fetch the project source
        chdir($project_folder);

        // Fetch the test from the project source
        $test->fetch();

        // Switch into the test directory we just fetched into, so we can
        // run all future commands from here
        chdir($test_folder);

        // Fetch the commit info from the commit helper
        $commit = \Criterion\Helper\Commit::getInfo($project->source, $test->branch);
        $test->commit = $commit;

        // Check to see if the commit is testable
        if (! \Criterion\Helper\Commit::isValid($commit)) {
            $test->delete();
            return false;
        }

        // Detect the test type. E.G. if .criterion.yml file does
        // not exist, it may be a PHPUnit project
        $test->type = $test->getType();
        $test->log('Detecting test type', $test->type ?: 'Not Found', $test->type ? '0' : '1');

        // Update the current test with some details we just gathered
        // such as: repo, commit info, and test type

        $test->config = array(
            'path' => is_file(realpath($test_folder . '/.criterion.yml')) ? realpath($test_folder . '/.criterion.yml') : false
        );
        $test->save();

        return $test->run();
    }
}
