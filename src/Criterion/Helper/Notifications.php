<?php
namespace Criterion\Helper;

class Notifications
{
    public static function failedEmail($test, $project)
    {
        $config = json_decode(file_get_contents(CONFIG_FILE), true);
        if (isset($project['email']) && $project['email'] && isset($config['email']))
        {
            $subject = '['.$project['repo'].'] Tests Failed (' . $test . ')';
            $message = "This is a short email to let you know that the following project's tests are failing: \n\n";
            $message .= $config['url'] . '/test/' . $test . "\n\n";
            $message .= 'Thanks';

            return self::email($project['email'], $config['email'], $subject, $message);
        }
        return false;
    }

    private static function email($to, $from, $subject, $message)
    {
        $headers = "From:" . $from;
        return mail($to, $subject, $message, $headers);
    }
}