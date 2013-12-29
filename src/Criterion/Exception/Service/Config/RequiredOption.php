<?php

namespace Criterion\Exception\Service\Config;

class RequiredOption extends \Criterion\Exception
{
    public function __construct($option, \Criterion\Service $service)
    {
        $message = sprintf(
            'Option "%s" must be defined for the Service "%s"',
            $option,
            $service->getName()
        );

        parent::__construct($message);
    }
}
