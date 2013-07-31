<?php
namespace Criterion\Helper;

class Command extends \Criterion\Helper
{
    public $response = null;
    public $command = null;
    public $output = null;

    public function __construct($project = false, $test = false)
    {
        parent::__construct();
        $this->project = $project;
        $this->test = $test;
    }

    public function execute($command, $internal = false, $return_object = false)
    {
        $this->command = str_replace('{path}', $this->test->path, $command);
        $log_id = $this->prelog($this->command, $internal);

        ob_start();
        passthru($this->command . ' 2>&1', $response);
        $this->output = ob_get_contents();
        ob_end_clean();

        $this->response = (string) $response;

        $this->output = trim($this->output);
        $this->output = str_replace(DATA_DIR, null, $this->output);
        $this->command = str_replace(DATA_DIR, null, $this->command);

        $this->log($this->command, $this->output, $this->response, $log_id, $internal);

        if ($return_object) {
            return $this;
        }

        return $this->response === '0';
    }

    public function preLog($command, $internal = false)
    {
        $command = str_replace(DATA_DIR, null, $command);

        $log = new \Criterion\Model\Log();
        $log->output = 'Running...';
        $log->response = false;
        $log->command = $command;
        $log->test_id = $this->test->id;
        $log->time = new \MongoDate();
        $log->status = '0';
        $log->internal = $internal;
        $log->save();

        return $log->id;
    }

    public function log($command, $output, $response = '0', $log_id = null, $internal = false)
    {
        $command = str_replace(DATA_DIR, null, $command);
        $output = str_replace(DATA_DIR, null, $output);

        $log = new \Criterion\Model\Log($log_id);


        $log->output = $output;
        $log->response = (string) $response;
        $log->command = $command;
        $log->test_id = $this->test->id;
        $log->project_id = $this->project->id;
        $log->time = new \MongoDate();
        $log->status = '1';
        $log->internal = $internal;
        $log->save();

        return $log;
    }
}
