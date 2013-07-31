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
        $log_id = $this->test->prelog($this->command, $internal);

        ob_start();
        passthru($this->command . ' 2>&1', $response);
        $this->output = ob_get_contents();
        ob_end_clean();

        $this->response = (string) $response;

        $this->output = trim($this->output);
        $this->output = str_replace(DATA_DIR, null, $this->output);
        $this->command = str_replace(DATA_DIR, null, $this->command);

        $this->test->log($this->command, $this->output, $this->response, $log_id, $internal);

        if ($return_object) {
            return $this;
        }

        return $this->response === '0';
    }


}
