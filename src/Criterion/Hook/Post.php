<?php

namespace Criterion\Hook;

class Post extends \Criterion\Hook
{
    protected $name = 'post';

    protected $requiredOptions = [
        'url'
    ];

    public function notify($event, \Criterion\Model\Job $job)
    {
        $payloadArray = [
            'id' => (string) $job->id,
            'event' => $event,
            'url' => 'http://criterion.io/job/' . (string) $job->id,
            'log' => $job->log,
            'status' => $job->status,
            'time' => $job->getTime()->sec
        ];

        $payload = json_encode($payloadArray);

        $ch = curl_init($this->url);

        curl_setopt_array(
            $ch,
            [
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($payload)
                ]
            ]

        );

        $result = curl_exec($ch);

        var_dump($result);
    }
}
