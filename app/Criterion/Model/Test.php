<?php
namespace Criterion\Model;

class Test extends \Criterion\Model
{
    public $collection = 'tests';

    public function __construct($query = null, $existing = null)
    {
        parent::__construct($query, $existing);

        if ( ! $this->exists)
        {
            $this->status = array(
                'code' => '4',
                'message' => 'Pending'
            );
            $this->started = new \MongoDate();
        }
    }

    public function getProject()
    {
        return new Project($this->project_id);
    }

    public function getLogs($internal = false)
    {
        $logs = $this->app->db->logs->find(array(
            'test_id' => new \MongoId($this->id),
            'internal' => $internal
        ))->sort(array(
            'time' => 1
        ));

        $log_models = array();
        foreach ($logs as $log)
        {
            $log_models[] = new Log(null, $log);
        }

        return $log_models;
    }
}