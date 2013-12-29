<?php

namespace Criterion\Hook;

class Github extends \Criterion\Hook
{
    protected $name = 'github';

    public function notify($event, \Criterion\Model\Job $job)
    {
        // Send notification
    }
}
