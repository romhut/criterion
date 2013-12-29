<?php

namespace Criterion;

class Command
{
    /**
     * The command to run
     * @var string
     */
    protected $command;

    /**
     * The Job object
     * @var jobect
     */
    protected $job;

    /**
     * The output of the command
     * @var string
     */
    protected $output;

    /**
     * What status is this Command currently in?
     * 0 = Success
     * 1 = Failure
     * 2 = Pending
     * @var integer
     */
    protected $status = 2;

    /**
     * Construct the Command
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @param  CriterionModel\Job $job
     * @param  string            $command
     */
    public function __construct(\Criterion\Model\Job $job, $command)
    {
        $this->job = $job;
        $this->command = $command;
    }

    /**
     * Execute the command
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @return object
     */
    public function execute()
    {
        $currentDirectory = getcwd();
        chdir($this->job->getPath());

        ob_start();
        passthru($this->command . ' 2>&1', $this->status);
        $this->output = trim(ob_get_contents());
        ob_end_clean();

        if ($this->status !== 0) {

            throw new \Criterion\Exception\Command\Failed(
                $this->command,
                $this->output,
                $this->status
            );
        }

        chdir($currentDirectory);
        return $this;
    }

    /**
     * Return the status of the Command
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @return integer
     */
    public function getStatus()
    {
        return (int) $this->status;
    }

    /**
     * Return the output of the Command
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Return the Command we ran
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Return the Job linked to this Command
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @return Criterion\Job
     */
    public function getJob()
    {
        return $this->job;
    }
}
