<?php

namespace Criterion\Model;

class Job extends \Criterion\Model
{
    /**
     * Event constants
     */
    const EVENT_STARTED     = 'started';
    const EVENT_FAILURE     = 'failure';
    const EVENT_SUCCESS     = 'success';
    const EVENT_FINISHED    = 'finished';

    /**
     * Having this here stops it from saving to MongoDB
     * @var array
     */
    public $subscriptions = [];

    /**
     * Collection name
     * @var string
     */
    protected $collection = 'job';

    /**
     * Return the Path of Job
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @return string
     */
    public function getPath()
    {
        return ROOT;
    }

    /**
     * Write to the log for this Job.
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @param  string   $command    The command that ran
     * @param  string   $output     The result of the command
     * @param  integer  $status     Did this command success?
     *                              -1  = Pending
     *                              0   = Success
     *                              1   = Failed
     * @param  object   $log_id     This allows us to "prelog"
     * @return array
     */
    public function log($command, $output = null, $status = -1, $log_id = null)
    {
        // Use the current log_id, or generate a new one?
        $log_id = $log_id ?: uniqid();

        $this->log = array_merge(
            $this->log ?: [],
            [
                (string) $log_id => [
                    'time' => new \MongoDate(),
                    'command' => $command,
                    'output' => $output,
                    'status' => $status
                ]
            ]
        );

        return $log_id;
    }

    /**
     * Throw an event and notify subscribed hooks
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @param  string $event
     * @return void
     */
    public function event($event)
    {
        $this->status = $event;

        $this->time = array_merge(
            $this->time ?: [],
            [
                $event => new \MongoDate()
            ]
        );

        foreach ($this->getSubscriptions($event) as $hook) {
            $notify = $hook->notify($event, $this);
            $notify->log($event);
        }
    }

    /**
     * Subscribe a Hook to events thrown by Services
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @param  string        $event
     * @param  Criterion\Hook $hook
     * @return void
     */
    public function subscribe($event, \Criterion\Hook $hook)
    {
        $this->subscriptions[$event][] = $hook;
    }

    /**
     * Subscribe to an array of Hooks
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @param  array $hooks
     */
    public function addSubscriptions(array $hooks)
    {
        foreach ($hooks as $hook) {
            $hook->subscribe();
        }
    }

    /**
     * Return a list of subscribed Hooks to a specific event
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @param  string $event
     * @return array
     */
    protected function getSubscriptions($event)
    {
        if (! isset($this->subscriptions[$event])) {
            return [];
        }

        return $this->subscriptions[$event];
    }

    /**
     * Return the MongoDate for the current status
     * For example, if the Job has a status of "finished"
     * then we return the "finished" time.
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @return \MongoDate
     */
    public function getTime()
    {
        return $this->time[$this->status];
    }
}
