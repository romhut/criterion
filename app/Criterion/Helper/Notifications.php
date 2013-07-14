<?php
namespace Criterion\Helper;

class Notifications extends \Criterion\Helper
{
    public static function failedEmail($test, $project)
    {
        $config = json_decode(file_get_contents(CONFIG_FILE), true);
        if (isset($project['email']) && $project['email'] && isset($config['email']))
        {
            $subject = '['.$project['repo'].'] Tests Failed (' . $test . ')';
            $body = "This is a short email to let you know that the following project's tests are failing: \n\n";
            $body .= $config['url'] . '/test/' . $test . "\n\n";
            $body .= 'Thanks';

            return self::email($project['email'], $config['email'], $subject, $body);
        }
        return false;
    }

    private static function email($to, $from, $subject, $body)
    {
        if (isset($from['smtp']))
        {
            $transport = \Swift_SmtpTransport::newInstance($from['smtp']['server'], $from['smtp']['port'])
                ->setUsername($from['smtp']['username'])
                ->setPassword($from['smtp']['password'])
                ;
        }
        else
        {
            $transport = \Swift_MailTransport::newInstance();
        }

        $mailer = \Swift_Mailer::newInstance($transport);
        $message = \Swift_Message::newInstance()
                    ->setSubject($subject)
                    ->setFrom(array($from['address'] => $from['name']))
                    ->setTo($to)
                    ->setBody($body)
                    ;

        try
        {
            return $mailer->send($message);
        }
        catch (\Swift_TransportException $e)
        {
            echo 'Could not connect to SMTP server.' . "\n";
            return false;
        }
    }
}