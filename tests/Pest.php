<?php

require_once __DIR__ . '/FakePHPMailer.php';

function setMailerStatic(string $property, $value): void
{
    $ref = new ReflectionClass(\Leaf\Mail\Mailer::class);
    $prop = $ref->getProperty($property);
    $prop->setAccessible(true);
    $prop->setValue(null, $value);
}

function getMailerStatic(string $property)
{
    $ref = new ReflectionClass(\Leaf\Mail\Mailer::class);
    $prop = $ref->getProperty($property);
    $prop->setAccessible(true);

    return $prop->getValue();
}

function injectFakeMailer(bool $sendResult = true): FakePHPMailer
{
    $fake = new FakePHPMailer($sendResult);
    setMailerStatic('mailer', $fake);

    return $fake;
}

uses()->beforeEach(function () {
    setMailerStatic('mailer', null);
    setMailerStatic('config', []);
    setMailerStatic('errors', []);
})->in(__DIR__);
