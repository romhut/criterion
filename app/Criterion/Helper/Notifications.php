<?php
namespace Criterion\Helper;

class Notifications extends \Criterion\Helper
{
    public static function failedEmail($test, $project)
    {
        $app = new \Criterion\Application();
        $config = $app->config;

        if ($project->email && isset($config['email'])) {
            $subject = '['.$project->repo.'] Tests Failed (' . $test . ')';
            $body = "This is a short email to let you know that the following project's tests are failing: \n\n";
            $body .= $config['url'] . '/test/' . $test . "\n\n";
            $body .= 'Thanks';

            return self::email($project->email, $subject, $body);
        }

        return false;
    }

    public static function email($to, $subject, $body)
    {
        $app = new \Criterion\Application();
        $config = $app->config;

        if (isset($config['email']['smtp'])) {
            $transport = \Swift_SmtpTransport::newInstance($config['email']['smtp']['server'], $config['email']['smtp']['port'])
                ->setUsername($config['email']['smtp']['username'])
                ->setPassword($config['email']['smtp']['password'])
                ;
        } else {
            $transport = \Swift_MailTransport::newInstance();
        }

        $mailer = \Swift_Mailer::newInstance($transport);
        $message = \Swift_Message::newInstance()
                    ->setSubject($subject)
                    ->setFrom(array($config['email']['address'] => $config['email']['name']))
                    ->setTo($to)
                    ->setBody($body)
                    ;

        try {
            return (bool) $mailer->send($message);
        } catch (\Swift_TransportException $e) {
            echo 'Could not connect to SMTP server.' . "\n";

            return false;
        }
    }
}
