<!-- markdownlint-disable no-inline-html -->
<p align="center">
  <br><br>
  <img src="https://leafphp.netlify.app/assets/img/leaf3-logo.png" height="100"/>
  <h1 align="center">Leaf Mail Module</h1>
  <br><br>
</p>

# Leaf Mail v2

[![Latest Stable Version](https://poser.pugx.org/leafs/mail/v/stable)](https://packagist.org/packages/leafs/mail)
[![Total Downloads](https://poser.pugx.org/leafs/mail/downloads)](https://packagist.org/packages/leafs/mail)
[![License](https://poser.pugx.org/leafs/mail/license)](https://packagist.org/packages/leafs/mail)

Mailing in PHP apps has always been seen as a daunting task. Leaf Mail provides a simple, straightforward and efficient email API that is built on the widely used PHPMailer Library component.

With Leaf Mail, you can easily send emails using various drivers and services such as SMTP, Mailgun, SendGrid, Amazon SES, and sendmail. This flexibility enables you to swiftly begin sending emails through a preferred local or cloud-based service.

## Installation

You can install leaf mail using the leaf cli:

```bash
leaf install mail
```

or with composer:

```bash
composer require leafs/mail
```

## Basic Usage

Leaf Mail provides a Mailer class that is responsible for validating and sending emails. This class handles the connection to your mail server, the configuration for how to send your emails and the actual sending of emails.

It also provides a mailer() method that is responsible for creating and formatting emails. Most of the time, you'll be using the mailer() method to create and send emails.

Note that you need to setup the connection to your mail server using the Leaf\Mail\Mailer class before sending your emails.

### Configure your mailer

```php
use Leaf\Mail\Mailer;
use PHPMailer\PHPMailer\PHPMailer;

...

Mailer::connect([
  'host' => 'smtp.mailtrap.io',
  'port' => 2525,
  'security' => PHPMailer::ENCRYPTION_STARTTLS,
  'auth' => [
    'username' => 'MAILTRAP_USERNAME',
    'password' => 'MAILTRAP_PASSWORD'
  ]
]);
```

### Send your mails

```php
mailer()
  ->create([
    'subject' => 'Leaf Mail Test',
    'body' => 'This is a test mail from Leaf Mail using gmail',
    
    // next couple of lines can be skipped if you
    // set defaults in the Mailer config
    'recipientEmail' => 'name@mail.com',
    'recipientName' => 'First Last',
    'senderName' => 'Leaf Mail',
    'senderEmail' => 'mychi@leafphp.dev',
  ])
  ->send();
```

**v2 is still WIP, we aim to release it soon. You can still use it by running `composer require leafs/leaf:dev-next`**
