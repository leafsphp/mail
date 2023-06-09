<?php

namespace Leaf;

class Mail
{
    public function __construct()
    {
        // 
    }

    public function getMail()
    {
        return [
            'subject' => 'Leaf Mail',
            'isHtml' => true,
            'body' => 'This is a test mail from Leaf Mail',
            'altBody' => 'This is a test mail from Leaf Mail',
            'recepientName' => 'Mike',
            'recepientEmail' => 'mickdd22@gmail.com',
            'senderName' => 'Mychi',
            'senderEmail' => 'mychi.darko@gmail.com',
            'attachment' => '',
            'cc' => '',
            'bcc' => '',
        ];
    }
}
