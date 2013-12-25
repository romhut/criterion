<?php

namespace Criterion;

abstract class Service
{

    /**
     * Human readable name for Service
     * @var string
     */
    protected $name = null;

    /**
     * Holds the Job object
     * @var object
     */
    protected $job;

    /**
     * Holds the options array for this Service
     * @var array
     */
    protected $options;

    /**
     * Holds an array of default options
     * @var array
     */
    protected $defaultOptions = [];

    /**
     * Required options for this Service
     * @var array
     */
    protected $requiredOptions = [];

    /**
     * Setup the Service
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @param  array        $options
     * @param  \Criterion\Job $job
     */
    public function __construct(\Criterion\Model\Job $job, array $options = array())
    {
        $this->job = $job;
        $this->options = $options;

        foreach ($this->requiredOptions as $option) {
            if (! array_key_exists($option, $this->options)) {

                $this->job->event(\Criterion\Model\Job::EVENT_FAILURE);

                $this->job->log(
                    $this->getName(),
                    sprintf(
                        'Missing option "%s"',
                        $option
                    ),
                    1
                );

                $this->job->save();

                throw new \Criterion\Exception\Service\Config\RequiredOption(
                    $option,
                    $this
                );
            }
        }
    }

    /**
     * Get an option from the service
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @param  string $key
     * @return string|array|integer
     */
    public function __get($key)
    {
        // If this key does not exist in the options array, then either
        // return a default option, or null.
        if (! isset($this->options[$key])) {
            return isset($this->defaultOptions[$key]) ? $this->defaultOptions[$key] : null;
        }

        return $this->options[$key];
    }

    /**
     * Set an option on this service
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @param  string $key
     * @param  string $value
     */
    public function __set($key, $value)
    {
        $this->options[$key] = $value;
    }

    /**
     * Returns the class name of this Service
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @return string
     */
    public function getClass()
    {
        return get_called_class();
    }

    /**
     * Returns the human readable name for this
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @return string
     */
    public function getName()
    {
        if (! $this->name) {
            return $this->getClass();
        }

        return $this->name;
    }

    /**
     * Execute the command for this Service
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @return \Criterion\Command
     */
    public function execute()
    {
        $command = new \Criterion\Command(
            $this->job,
            $this->getCommand()
        );

        try {
            $command->execute();

        } catch (\Criterion\Exception\Command\Failed $e) {

        }

        return $command;
    }

    /**
     * Returns the final command to run for this service
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @return string
     */
    abstract public function getCommand();
}
