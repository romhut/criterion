<?php
namespace Criterion\Model;

class Test extends \Criterion\Model
{
    public $collection = 'tests';
    public $project = false;

    public function __construct($query = null, $existing = null)
    {
        parent::__construct($query, $existing);

        if (! $this->exists) {
            $this->status = array(
                'code' => '4',
                'message' => 'Pending'
            );
            $this->started = new \MongoDate();
        }
    }

    public function getProject()
    {
        if (! $this->project) {
            $this->project = new Project($this->project_id);
        }

        return $this->project;
    }

    public function getType()
    {
        if (file_exists($this->path . '/.criterion.yml') || $this->getProject()->hasServerConfig()) {
            return 'criterion';
        } elseif (file_exists($this->path . '/phpunit.xml') || file_exists($this->path . '/phpunit.xml.dist')) {
            return 'phpunit';
        } else {
            return false;
        }
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
        foreach ($logs as $log) {
            $log_models[] = new Log(null, $log);
        }

        return $log_models;
    }
}
