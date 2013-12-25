<?php

namespace Criterion\Exception\Hook\Config;

class RequiredOption extends \Criterion\Exception
{
    public function __construct($option, \Criterion\Hook $hook)
    {
        $message = sprintf(
            'Option "%s" must be defined for the Hook "%s"',
            $option,
            $hook->getName()
        );

        parent::__construct($message);
    }
}
