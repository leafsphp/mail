<?php

namespace Leaf;

use Leaf\Mail\Mailer;

class Mail
{
    /**@var array */
    protected $mail = [];

    public function __construct($mail = null)
    {
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
            'attachment' => $this->mail['attachment'] ?? '',
            'cc' => $this->mail['cc'] ?? '',
            'bcc' => $this->mail['bcc'] ?? '',
        ];
    }

    /**
     * Send your crafted email
     */
    public function send()
    {
        return Mailer::send($this);
    }
}
