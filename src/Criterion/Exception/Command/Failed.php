<?php

namespace Criterion\Exception\Command;

class Failed extends \Criterion\Exception
{
    public function __construct($command, $output, $response) {

        $message = sprintf(
            'Command "%s" failed, with %s',
            $command,
            $output
        );

        parent::__construct($message);
    }
}
