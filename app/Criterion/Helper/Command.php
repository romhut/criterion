<?php
namespace Criterion\Helper;

class Command extends \Criterion\Helper
{
    public $response = null;
    public $command = null;
    public $output = null;

    public function execute($command)
    {
        $this->command = $command;

        ob_start();
        passthru($this->command . ' 2>&1', $response);
        $this->output = ob_get_contents();
        ob_end_clean();

        $this->response = (string) $response;

        $this->output = trim($this->output);
        $this->output = str_replace(DATA_DIR, null, $this->output);
        $this->command = str_replace(DATA_DIR, null, $this->command);

        return $this->response === '0';
    }
}
