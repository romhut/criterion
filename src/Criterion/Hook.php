<?php

namespace Criterion;

abstract class Hook
{
    /**
     * Constants
     */
    const SUBSCRIBE = 'subscribe';

    /**
     * Human readable name
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
     * Required options for this Service
     * @var array
     */
    protected $requiredOptions = [];

    /**
     * Construct the Hook
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @param  CriterionModel\Job $job
     * @param  array              $options
     */
    public function __construct(\Criterion\Model\Job $job, array $options = array())
    {
        $this->job = $job;
        $this->options = $options;

        $this->requiredOptions = array_merge(
            $this->requiredOptions,
            [
                self::SUBSCRIBE
            ]
        );

        foreach ($this->requiredOptions as $option) {
            if (! array_key_exists($option, $this->options)) {
                throw new \Criterion\Exception\Hook\Config\RequiredOption(
                    $option,
                    $this
                );
            }
        }
    }

    /**
     * Get an option from the Hook
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
     * Set an option on this Hook
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
     * Returns a human readable name for this Hook
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @return string
     */
    public function getName()
    {
        return $this->name ?: $this->getClass();
    }

    /**
     * For each our of subscriptions in this Hook,
     * subscribe to the events on a job
     * @author Scott Robertson <scottymeuk@gmail.com>
     */
    public function subscribe()
    {
        foreach ($this->subscribe as $event) {
            $this->job->subscribe($event, $this);
        }
    }

    /**
     * Send Notify to Hook
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @param  string            $event
     * @param  CriterionModel\Job $job
     * @return void
     */
    abstract public function notify($event, \Criterion\Model\Job $job);
}
