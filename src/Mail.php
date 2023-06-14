<?php

namespace Leaf;

use Leaf\Mail\Mailer;

class Mail
{
    /**@var array */
    protected $mail = [];

    public function __construct($mail = null)
    {
        unset($mail['attachments']);
        $this->mail = $mail;
    }

    public static function create($mail)
    {
        return new static($mail);
    }

    public function getMail()
    {
        return [
            'subject' => $this->mail['subject'],
            'isHtml' => $this->mail['isHtml'] ?? true,
            'body' => $this->mail['body'],
            'altBody' => $this->mail['altBody'] ?? $this->mail['body'],
            'recipientName' => $this->mail['recipientName'] ?? null,
            'recipientEmail' => $this->mail['recipientEmail'] ?? null,
            'senderName' => $this->mail['senderName'] ?? null,
            'senderEmail' => $this->mail['senderEmail'] ?? null,
            'replyToName' => $this->mail['replyToName'] ?? null,
            'replyToEmail' => $this->mail['replyToEmail'] ?? null,
            'attachments' => $this->mail['attachments'] ?? null,
            'cc' => $this->mail['cc'] ?? '',
            'bcc' => $this->mail['bcc'] ?? '',
        ];
    }

    /**
     * Add attachments to your mail from your file system
     * 
     * @throws Exception
     * @return Mail
     */
    public function attach(
        $path,
        $name = "",
        $encoding = \PHPMailer\PHPMailer\PHPMailer::ENCODING_BASE64,
        $type = "",
        $disposition = "attachment"
    ) {
        if (is_array($path)) {
            foreach ($path as $attachment) {
                $this->mail['attachments'][] = [
                    'path' => $attachment,
                    'name' => $name,
                    'encoding' => $encoding,
                    'type' => $type,
                    'disposition' => $disposition,
                ];
            }
        } else {
            $this->mail['attachments'][] = [
                'path' => $path,
                'name' => $name,
                'encoding' => $encoding,
                'type' => $type,
                'disposition' => $disposition
            ];
        }

        return $this;
    }

    /**
     * Send your crafted email
     */
    public function send()
    {
        return Mailer::send($this);
    }
}
