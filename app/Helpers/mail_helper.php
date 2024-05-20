<?php

namespace App\Helpers;

use App\Entities\Mail;
use App\Models\MailModel;
use CodeIgniter\CLI\CLI;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use ReflectionException;

/**
 * Retrieve, send and delete mails in queue.
 *
 * @return void
 * @throws Exception
 */
function workMailQueue(): void
{
    $mailer = createMailer();

    foreach (getMails() as $mail) {
        $recipient = getUserById($mail->getRecipientId());
        if (!$recipient) {
            CLI::error("Invalid user id {$mail->getRecipientId()}");
        }

        $mailer->clearAddresses();
        $mailer->addAddress($recipient->getEmail());
        $mailer->Subject = $mail->getSubject();
        $mailer->Body = $mail->getBody();

        if ($mailer->send()) {
            deleteMail($mail->getId());
            CLI::write("Successfully sent mail {$mail->getId()} to {$recipient->getEmail()}");
        } else {
            CLI::error("Error sending mail {$mail->getId()} to {$recipient->getEmail()}: {$mailer->ErrorInfo}");
        }

        usleep(100_000); // sleep for 100ms
    }
}

/**
 * Add a mail to the queue.
 *
 * @throws ReflectionException
 */
function queueMail(int $recipientId, string $subject, string $body): string|int
{
    $mail = createMail($recipientId, $subject, $body);
    $model = getMailModel();
    $model->save($mail);
    return $model->getInsertID();
}

/**
 * Delete a specific mail from the queue.
 *
 * @param int $id
 * @return void
 */
function deleteMail(int $id): void
{
    getMailModel()->delete($id);
}

/**
 * Retrieve all mails in queue.
 *
 * @return Mail[]
 */
function getMails(): array
{
    return getMailModel()->findAll();
}

/**
 * Create mail object.
 *
 * @param int $recipientId
 * @param string $subject
 * @param string $body
 * @return Mail
 */
function createMail(int $recipientId, string $subject, string $body): Mail
{
    $mail = new Mail();
    $mail->setRecipientId($recipientId);
    $mail->setSubject($subject);
    $mail->setBody($body);
    return $mail;
}

/**
 * @return MailModel
 */
function getMailModel(): MailModel
{
    return new MailModel();
}

/**
 * Create a new mailer instance.
 *
 * @return PHPMailer
 * @throws Exception
 */
function createMailer(): PHPMailer
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
    $mailer->CharSet = PHPMailer::CHARSET_UTF8;

    return $mailer;
}