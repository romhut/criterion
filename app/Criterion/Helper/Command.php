<?php
namespace Criterion\Helper;

class Command extends \Criterion\Helper
{
    public $success = false;
    public $response = null;
    public $command = null;
    public $output = null;

    public function __construct($project = false, $test = false)
    {
        parent::__construct();
        $this->project = $project;
        $this->test = $test;
    }

    /**
     * Execute a given command, and log it against the test that was
     * set in the __construct()
     * @param  string  $command       The command you wish to run
     * @param  boolean $internal      Should the command be logged as internal only (not shown in output)
     * @param  boolean $return_object Should we return the full command object?
     * @return object                 The command's object?
     */
    public function execute($command, $internal = false)
    {
        if (! $this->test || ! $this->project) {
            return false;
        }

        $this->command = str_replace('{path}', $this->test->path, $command);
        $log = $this->test->log($this->command, false, false, $internal);

        ob_start();
        passthru($this->command . ' 2>&1', $response);
        $this->output = ob_get_contents();
        ob_end_clean();

        $this->response = (string) $response;

        $this->output = trim($this->output);
        $this->output = str_replace(DATA_DIR, null, $this->output);
        $this->command = str_replace(DATA_DIR, null, $this->command);

        // Update the original log
        $log->command = $this->command;
        $log->response = $this->response;
        $log->output = $this->output;
        $log->status = '1';
        $log->save();

        if ($this->response === '0') {
            $this->success = true;
        } else {
            $this->success = false;
        }

        return $this;
    }


}
