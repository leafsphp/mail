<?php

use PHPMailer\PHPMailer\PHPMailer;

class FakePHPMailer extends PHPMailer
{
    /** @var bool */
    public $sendResult;

    /** @var \Throwable|null */
    public $sendThrows = null;

    /** @var int */
    public $sendCalls = 0;

    /** @var array snapshot of state at the moment send() was called */
    public $lastSendState = [];

    public function __construct(bool $sendResult = true)
    {
        parent::__construct();
        $this->sendResult = $sendResult;
    }

    public function send()
    {
        $this->sendCalls++;
        $this->lastSendState = [
            'to' => $this->getToAddresses(),
            'cc' => $this->getCcAddresses(),
            'bcc' => $this->getBccAddresses(),
            'replyTo' => $this->getReplyToAddresses(),
            'attachments' => $this->getAttachments(),
            'from' => [$this->From, $this->FromName],
            'subject' => $this->Subject,
            'body' => $this->Body,
            'altBody' => $this->AltBody,
        ];

        if ($this->sendThrows) {
            throw $this->sendThrows;
        }

        if (!$this->sendResult) {
            $this->ErrorInfo = 'fake send failure';
        }

        return $this->sendResult;
    }
}
