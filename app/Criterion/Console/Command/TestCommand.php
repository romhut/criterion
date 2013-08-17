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
            ->setDefinition(
                array(
                    new InputArgument('test_id', InputArgument::REQUIRED, 'The test ID', null),
                )
            );
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

        // Setup the command helper
        $command = new \Criterion\Helper\Command($project, $test);

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

        // If the project path does not exist, then create it
        $project_path = TEST_DIR . '/' . (string) $project->id;
        if (! is_dir($project_path)) {
            mkdir($project_path, 0777, true);
        }

        $test->path = $project_path . '/' . (string) $test->id;
        if (! $test->branch) {
            $test->branch = 'master';
        }

        // Set any enviroment variables from the project config
        $test->setEnviromentVariables();

        // Switch to the project folder, and fetch the source
        chdir($project_path);
        if (! $test->fetch()) {
            return $test->failed();
        }

        // Switch to the test folder
        chdir($test->path);

        // Make sure the commit is vailid (No [skip ci] etc)
        $commit = \Criterion\Helper\Commit::getInfo($project->source, $test->branch);
        $test->commit = $commit;

        if (! \Criterion\Helper\Commit::isValid($test->commit)) {
            $test->log('Checking if commit is valid', 'Invalid', '1');
            $test->status = array(
                'code' => '1',
                'message' => 'Skipped'
            );
            $test->save();
            $test->removeFolder();
            return false;
        }

        // Detect the test type. Could be .criterion.yml, server or automatic
        $test->type = $test->getType();
        $test->log('Detecting test type', $test->type ?: 'Not Found', $test->type ? '0' : '1');

        // Set the config path for later use
        $test->config = array(
            'path' => is_file(realpath($test->path . '/.criterion.yml')) ? realpath($test->path . '/.criterion.yml') : false
        );

        // Finally, save the test and run it
        $test->save();
        return $test->run();
    }
}
