<?php

namespace Criterion\Service;

class Phpunit extends \Criterion\Service
{
    /**
     * Human readable name
     * @var string
     */
    protected $name = 'phpunit';

    /**
     * Required options for this Service
     * @var array
     */
    protected $requiredOptions = [
        'config'
    ];

    /**
     * Return the final command to run
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @return string
     */
    public function getCommand()
    {
        return sprintf(
            'phpunit --configuration %s',
            $this->config
        );
    }
}
