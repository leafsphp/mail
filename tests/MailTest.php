<?php

use Leaf\Mail;

test('create returns a Mail instance with defaults in getMail', function () {
    $mail = Mail::create([
        'subject' => 'Hello',
        'body' => '<b>Hi there</b>',
    ]);

    expect($mail)->toBeInstanceOf(Mail::class);

    $data = $mail->getMail();

    expect($data['subject'])->toBe('Hello');
    expect($data['isHtml'])->toBeTrue();
    expect($data['altBody'])->toBe('<b>Hi there</b>');
    expect($data['cc'])->toBe('');
    expect($data['bcc'])->toBe('');
    expect($data['recipientEmail'])->toBeNull();
    expect($data['senderEmail'])->toBeNull();
    expect($data['attachments'])->toBeNull();
});

test('explicit altBody and isHtml are preserved', function () {
    $data = Mail::create([
        'subject' => 'Hello',
        'body' => '<b>Hi</b>',
        'altBody' => 'Hi plain',
        'isHtml' => false,
    ])->getMail();

    expect($data['altBody'])->toBe('Hi plain');
    expect($data['isHtml'])->toBeFalse();
});

test('attach adds a single attachment and is chainable', function () {
    $mail = Mail::create(['subject' => 's', 'body' => 'b']);

    $result = $mail->attach('/tmp/file.txt', 'file.txt');

    expect($result)->toBe($mail);

    $attachments = $mail->getMail()['attachments'];
    expect($attachments)->toHaveCount(1);
    expect($attachments[0]['path'])->toBe('/tmp/file.txt');
    expect($attachments[0]['name'])->toBe('file.txt');
    expect($attachments[0]['disposition'])->toBe('attachment');
});

test('attach accepts an array of paths', function () {
    $attachments = Mail::create(['subject' => 's', 'body' => 'b'])
        ->attach(['/tmp/a.txt', '/tmp/b.txt'])
        ->getMail()['attachments'];

    expect($attachments)->toHaveCount(2);
    expect($attachments[0]['path'])->toBe('/tmp/a.txt');
    expect($attachments[1]['path'])->toBe('/tmp/b.txt');
});

test('constructor attachments key routes through attach()', function () {
    $attachments = Mail::create([
        'subject' => 's',
        'body' => 'b',
        'attachments' => ['/tmp/a.txt', '/tmp/b.txt'],
    ])->getMail()['attachments'];

    expect($attachments)->toHaveCount(2);
    expect($attachments[0])->toHaveKeys(['path', 'name', 'encoding', 'type', 'disposition']);
    expect($attachments[0]['path'])->toBe('/tmp/a.txt');
    expect($attachments[1]['path'])->toBe('/tmp/b.txt');
});

test('mailer helper returns a Mail instance', function () {
    expect(mailer(['subject' => 's', 'body' => 'b']))->toBeInstanceOf(Mail::class);
});

test('errors are readable from the mail instance', function () {
    expect(mailer(['subject' => 'x'])->errors())->toBeArray();
    expect(mailer(['subject' => 'x'])->errors())->toBe(\Leaf\Mail\Mailer::errors());
});
