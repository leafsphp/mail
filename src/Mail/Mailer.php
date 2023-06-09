<?php

namespace Leaf\Mail;

use Leaf\Mail;
use PHPMailer\PHPMailer\OAuth;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class Mailer 
{
    /**@var array */
    protected static $config = [];

    /**@var \PHPMailer\PHPMailer\PHPMailer */
    protected static $mailer;

    protected static $auth = 'SMTP';

    public static function useOAuth(OAuth $oauth)
    {
        if (!static::$mailer) {
            static::$mailer = new PHPMailer();
        }

        static::$mailer->AuthType = 'XOAUTH2';
        static::$mailer->setOAuth($oauth);
    }

    /**
     * SMTP Connection
     */
    public static function connect($host, $port, $auth = [], $security = 'STARTTLS')
    {
        if (!static::$mailer) {
            static::$mailer = new PHPMailer();
        }

        static::$mailer->isSMTP();
        static::$mailer->Host = $host;
        static::$mailer->Port = $port;

        if ($auth === false) {
            static::$mailer->SMTPAuth = false;
        } else {
            static::$mailer->SMTPAuth = true;

            if (isset($auth['username']) && isset($auth['password'])) {
                static::$mailer->Username = $auth['username'];
                static::$mailer->Password = $auth['password'];
            }
        }

        if ($security === 'STARTTLS') {
            static::$mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } else {
            static::$mailer->SMTPSecure = $security;
        }
    }

    public static function config($config = [])
    {
        if (empty($config)) {
            return static::$config;
        }

        static::$config = $config;
    }

    protected static function validate(Mail $mail)
    {
        $mail = $mail->getMail();

        if (empty($mail['recepientEmail'])) {
            throw new \Exception('Recepient email is required');
        }

        if (empty($mail['senderEmail'])) {
            throw new \Exception('Sender email is required');
        }

        if (empty($mail['senderName'])) {
            throw new \Exception('Sender name is required');
        }

        if (empty($mail['recepientName'])) {
            throw new \Exception('Recepient name is required');
        }

        if (empty($mail['subject'])) {
            throw new \Exception('Subject is required');
        }

        if (empty($mail['body'])) {
            throw new \Exception('Body is required');
        }

        return true;
    }

    /**
     * Send
     */
    public static function send(Mail $mail)
    {
        if (static::$config['debug'] === "SERVER") {
            static::$mailer->SMTPDebug = SMTP::DEBUG_SERVER;
        } else {
            static::$mailer->SMTPDebug = static::$config['debug'] ?? SMTP::DEBUG_OFF;
        }

        if (static::validate($mail)) {
            $mail = $mail->getMail();

            static::$mailer->Subject = $mail['subject'];
            static::$mailer->isHTML($mail['isHtml'] ?? true);
            static::$mailer->Body = $mail['body'];
            static::$mailer->AltBody = $mail['altBody'] ?? '';
            static::$mailer->addAddress($mail['recepientEmail'], $mail['recepientName']);
            static::$mailer->setFrom($mail['senderEmail'], $mail['senderName']);

            if (!empty($mail['attachment'])) {
                static::$mailer->addAttachment($mail['attachment']);
            }

            if (!empty($mail['cc'])) {
                static::$mailer->addCC($mail['cc']);
            }

            if (!empty($mail['bcc'])) {
                static::$mailer->addBCC($mail['bcc']);
            }

            try {
                return static::$mailer->send();
            } catch (\Throwable $th) {
                echo $th->getMessage();
            }
        }
    }
}
