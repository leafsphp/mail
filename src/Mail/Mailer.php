<?php

namespace Leaf\Mail;

use Leaf\Mail;
use PHPMailer\PHPMailer\Exception;
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

    /**
     * Errors
     */
    protected static $errors = [];

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
    public static function connect($connection)
    {
        if (!static::$mailer) {
            static::$mailer = new PHPMailer();
        }

        static::$mailer->isSMTP();
        static::$mailer->Host = $connection['host'];
        static::$mailer->Port = $connection['port'];

        if (isset($connection['keepAlive']) || isset(static::$config['keepAlive'])) {
            static::$mailer->SMTPKeepAlive = $connection['keepAlive'] ?? static::$config['keepAlive'];
        }

        if (!isset($connection['auth']) || $connection['auth'] === false) {
            static::$mailer->SMTPAuth = false;
        } else {
            static::$mailer->SMTPAuth = true;

            if ($connection['auth'] instanceof OAuth) {
                static::useOAuth($connection['auth']);
            } else {
                if (isset($connection['auth']['username'])) {
                    static::$mailer->Username = $connection['auth']['username'];
                }

                if (isset($connection['auth']['password'])) {
                    static::$mailer->Password = $connection['auth']['password'];
                }
            }
        }

        if (!isset($connection['security']) || $connection['security'] === 'STARTTLS') {
            static::$mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } else {
            static::$mailer->SMTPSecure = $connection['security'];
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

        if (empty($mail['recipientEmail']) && empty(static::$config['defaults']['recipientEmail'] ?? '')) {
            throw new \Exception('recipient email is required');
        }

        if (empty($mail['senderEmail']) && empty(static::$config['defaults']['senderEmail'] ?? '')) {
            throw new \Exception('Sender email is required');
        }

        if (empty($mail['senderName']) && empty(static::$config['defaults']['senderName'] ?? '')) {
            throw new \Exception('Sender name is required');
        }

        if (empty($mail['recipientName']) && empty(static::$config['defaults']['recipientName'] ?? '')) {
            throw new \Exception('recipient name is required');
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

            static::$mailer->addAddress(
                $mail['recipientEmail'] ?? static::$config['defaults']['recipientEmail'] ?? '',
                $mail['recipientName'] ?? static::$config['defaults']['recipientName'] ?? ''
            );

            static::$mailer->setFrom(
                $mail['senderEmail'] ?? static::$config['defaults']['senderEmail'] ?? '',
                $mail['senderName'] ?? static::$config['defaults']['senderName'] ?? ''
            );

            if (!empty($mail['replyToEmail']) && !empty(static::$config['defaults']['replyToEmail'] ?? '')) {
                static::$mailer->addReplyTo(
                    $mail['replyToEmail'] ?? static::$config['defaults']['replyToEmail'] ?? '',
                    $mail['replyToName'] ?? static::$config['defaults']['replyToName'] ?? ''
                );
            }

            if (!empty($mail['attachments'])) {
                foreach ($mail['attachments'] as $attachment) {
                    static::$mailer->addAttachment(
                        $attachment['path'],
                        $attachment['name'],
                        $attachment['encoding'],
                        $attachment['type'],
                        $attachment['disposition']
                    );
                }
            }

            if (!empty($mail['cc'])) {
                static::$mailer->addCC($mail['cc']);
            }

            if (!empty($mail['bcc'])) {
                static::$mailer->addBCC($mail['bcc']);
            }

            try {
                ob_start();
                $res = static::$mailer->send();
                $debug = ob_get_clean();

                if (!$res) {
                    static::$errors[] = static::$mailer->ErrorInfo;
                }

                if (!empty($debug) && (static::$config['debug'] ?? false)) {
                    $res = $debug;
                }

                return $res;
            } catch (Exception $e) {
                static::$errors[] = $e->getMessage();
            } catch (\Exception $e) {
                static::$errors[] = $e->getMessage();
            }
        }
    }

    /**
     * Return all errors
     */
    public static function errors()
    {
        return static::$errors;
    }
}
