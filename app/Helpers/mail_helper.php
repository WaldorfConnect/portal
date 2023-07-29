<?php

namespace App\Helpers;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * @throws Exception
 */
function sendMail(string $recipient, string $subject, string $message): void
{
    $mailer = getMailer();
    $mailer->addAddress($recipient);
    $mailer->Subject = $subject;
    $mailer->Body = $message;
    $mailer->send();
}

/**
 * @throws Exception
 */
function getMailer(): PHPMailer
{
    $mailer = new PHPMailer();

    $mailer->isSMTP();
    $mailer->Host = getenv('mail.host');
    $mailer->SMTPAuth = true;
    $mailer->Username = getenv('mail.username');
    $mailer->Password = getenv('mail.password');
    $mailer->SMTPSecure = 'tls';
    $mailer->Port = 587;

    $mailer->setFrom(getenv('mail.from.address'), getenv('mail.from.name'));
    $mailer->isHTML();
    return $mailer;
}