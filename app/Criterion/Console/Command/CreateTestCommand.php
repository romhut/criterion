<?php
namespace Criterion\Console\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateTestCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('create_test')
            ->setDescription('Create a test')
             ->setDefinition(array(
                new InputArgument('project_id', InputArgument::REQUIRED, 'Project ID', null),
             ))
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $project_id = new \MongoId($input->getArgument('project_id'));
        $test_id = new \MongoId();

        $get_project = $this->getApplication()->db->projects->findOne(array(
            '_id' => $project_id
        ));

        if ( ! $get_project)
        {
            $output->writeln('<error>No Project Found</error>');
            return false;
        }

        $data = array(
            '_id' => $test_id,
            'project_id' => $project_id,
            'started' => new \MongoDate(),
            'status' => array(
                'code' => '4',
                'message' => 'Pending'
            )
        );

        $this->getApplication()->db->tests->save($data);

        $output->writeln((String)$test_id);
        return false;
    }
}