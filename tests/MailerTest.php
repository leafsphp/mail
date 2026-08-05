<?php

use Leaf\Mail;
use Leaf\Mail\Mailer;

function baseMail(array $overrides = []): Mail
{
    return Mail::create(array_merge([
        'subject' => 'Test subject',
        'body' => 'Test body',
        'recipientEmail' => 'to@example.com',
        'recipientName' => 'To Person',
        'senderEmail' => 'from@example.com',
        'senderName' => 'From Person',
    ], $overrides));
}

test('send without a connected mailer throws', function () {
    Mailer::send(baseMail());
})->throws(\Exception::class, 'No mail server connection');

describe('validate', function () {
    $required = [
        'recipientEmail' => 'recipient email is required',
        'senderEmail' => 'Sender email is required',
        'senderName' => 'Sender name is required',
        'recipientName' => 'recipient name is required',
        'subject' => 'Subject is required',
        'body' => 'Body is required',
    ];

    foreach ($required as $field => $message) {
        test("missing {$field} throws '{$message}'", function () use ($field, $message) {
            injectFakeMailer();

            expect(fn () => Mailer::send(baseMail([$field => ''])))
                ->toThrow(\Exception::class, $message);
        });
    }

    test('defaults from config satisfy requirements', function () {
        Mailer::connect([
            'host' => 'localhost',
            'port' => 25,
            'auth' => false,
            'defaults' => [
                'recipientEmail' => 'default-to@example.com',
                'recipientName' => 'Default To',
                'senderEmail' => 'default-from@example.com',
                'senderName' => 'Default From',
            ],
        ]);

        $fake = injectFakeMailer();

        $res = Mailer::send(Mail::create(['subject' => 's', 'body' => 'b']));

        expect($res)->toBeTrue();
        expect($fake->lastSendState['to'])->toHaveCount(1);
        expect($fake->lastSendState['to'][0][0])->toBe('default-to@example.com');
        expect($fake->lastSendState['to'][0][1])->toBe('Default To');
        expect($fake->lastSendState['from'])->toBe(['default-from@example.com', 'Default From']);
    });
});

describe('send happy path', function () {
    test('single string recipient, setFrom, subject and bodies applied', function () {
        $fake = injectFakeMailer();

        $res = Mailer::send(baseMail());

        expect($res)->toBeTrue();
        expect($fake->sendCalls)->toBe(1);
        expect($fake->lastSendState['to'])->toBe([['to@example.com', 'To Person']]);
        expect($fake->lastSendState['from'])->toBe(['from@example.com', 'From Person']);
        expect($fake->lastSendState['subject'])->toBe('Test subject');
        expect($fake->lastSendState['body'])->toBe('Test body');
        expect($fake->lastSendState['altBody'])->toBe('Test body');
    });

    test('array of recipients with array of names', function () {
        $fake = injectFakeMailer();

        Mailer::send(baseMail([
            'recipientEmail' => ['a@example.com', 'b@example.com'],
            'recipientName' => ['Person A', 'Person B'],
        ]));

        expect($fake->lastSendState['to'])->toBe([
            ['a@example.com', 'Person A'],
            ['b@example.com', 'Person B'],
        ]);
    });

    test('array of recipients with a single string name', function () {
        $fake = injectFakeMailer();

        Mailer::send(baseMail([
            'recipientEmail' => ['a@example.com', 'b@example.com'],
            'recipientName' => 'Everyone',
        ]));

        expect($fake->lastSendState['to'])->toBe([
            ['a@example.com', 'Everyone'],
            ['b@example.com', 'Everyone'],
        ]);
    });

    test('replyTo added when provided on the mail', function () {
        $fake = injectFakeMailer();

        Mailer::send(baseMail([
            'replyToEmail' => 'reply@example.com',
            'replyToName' => 'Reply Person',
        ]));

        expect($fake->lastSendState['replyTo'])->toHaveKey('reply@example.com');
        expect($fake->lastSendState['replyTo']['reply@example.com'][1])->toBe('Reply Person');
    });

    test('replyTo added when only present in defaults', function () {
        setMailerStatic('config', [
            'defaults' => [
                'replyToEmail' => 'default-reply@example.com',
                'replyToName' => 'Default Reply',
            ],
        ]);
        $fake = injectFakeMailer();

        Mailer::send(baseMail());

        expect($fake->lastSendState['replyTo'])->toHaveKey('default-reply@example.com');
        expect($fake->lastSendState['replyTo']['default-reply@example.com'][1])->toBe('Default Reply');
    });

    test('cc and bcc accept a single string', function () {
        $fake = injectFakeMailer();

        Mailer::send(baseMail([
            'cc' => 'cc@example.com',
            'bcc' => 'bcc@example.com',
        ]));

        expect($fake->lastSendState['cc'])->toBe([['cc@example.com', '']]);
        expect($fake->lastSendState['bcc'])->toBe([['bcc@example.com', '']]);
    });

    test('cc and bcc accept arrays', function () {
        $fake = injectFakeMailer();

        Mailer::send(baseMail([
            'cc' => ['cc1@example.com', 'cc2@example.com'],
            'bcc' => ['bcc1@example.com', 'bcc2@example.com'],
        ]));

        expect($fake->lastSendState['cc'])->toBe([
            ['cc1@example.com', ''],
            ['cc2@example.com', ''],
        ]);
        expect($fake->lastSendState['bcc'])->toBe([
            ['bcc1@example.com', ''],
            ['bcc2@example.com', ''],
        ]);
    });

    test('attachments are forwarded to PHPMailer', function () {
        $file = tempnam(sys_get_temp_dir(), 'leafmail');
        file_put_contents($file, 'attachment content');

        $fake = injectFakeMailer();

        Mailer::send(baseMail()->attach($file, 'custom-name.txt'));

        expect($fake->lastSendState['attachments'])->toHaveCount(1);
        expect($fake->lastSendState['attachments'][0][0])->toBe($file);
        expect($fake->lastSendState['attachments'][0][2])->toBe('custom-name.txt');

        unlink($file);
    });
});

describe('state reset between sends', function () {
    test('second send does not inherit recipients, attachments or replyTos', function () {
        $file = tempnam(sys_get_temp_dir(), 'leafmail');
        file_put_contents($file, 'attachment content');

        $fake = injectFakeMailer();

        Mailer::send(baseMail([
            'recipientEmail' => 'a@x.com',
            'replyToEmail' => 'reply-a@x.com',
        ])->attach($file));

        Mailer::send(baseMail(['recipientEmail' => 'b@x.com']));

        expect($fake->sendCalls)->toBe(2);
        expect($fake->lastSendState['to'])->toBe([['b@x.com', 'To Person']]);
        expect($fake->lastSendState['attachments'])->toBeEmpty();
        expect($fake->lastSendState['replyTo'])->toBeEmpty();

        // and the mailer itself is clean after the last send
        expect($fake->getToAddresses())->toBeEmpty();
        expect($fake->getAttachments())->toBeEmpty();
        expect($fake->getReplyToAddresses())->toBeEmpty();

        unlink($file);
    });
});

describe('failures', function () {
    test('send returns false and records ErrorInfo when PHPMailer send fails', function () {
        injectFakeMailer(false);

        $res = Mailer::send(baseMail());

        expect($res)->toBeFalse();
        expect(Mailer::errors())->toContain('fake send failure');
    });

    test('a throwing send results in false and message in errors', function () {
        $fake = injectFakeMailer();
        $fake->sendThrows = new \PHPMailer\PHPMailer\Exception('SMTP boom');

        $res = Mailer::send(baseMail());

        expect($res)->toBeFalse();
        expect(Mailer::errors())->toContain('SMTP boom');
    });

    test('a generic exception from send is also caught', function () {
        $fake = injectFakeMailer();
        $fake->sendThrows = new \RuntimeException('generic boom');

        $res = Mailer::send(baseMail());

        expect($res)->toBeFalse();
        expect(Mailer::errors())->toContain('generic boom');
    });
});
