<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService
{
    public function enviarEmail($to, $subject, $html)
    {
        $mail = new PHPMailer(true);
        $username = (string) config('mail.mailers.smtp.username', '');
        $password = (string) config('mail.mailers.smtp.password', '');

        if ($username === '' || $password === '') {
            throw new \Exception('Configuracion SMTP incompleta: revisa MAIL_USERNAME y MAIL_PASSWORD en .env');
        }

        try {
            $mail->isSMTP();
            $mail->Host = (string) config('mail.mailers.smtp.host', 'smtp.gmail.com');
            $mail->SMTPAuth = true;
            $mail->Username = $username;
            $mail->Password = $password;
            $mail->SMTPSecure = (string) config('mail.mailers.smtp.scheme', 'tls');
            $mail->Port = (int) config('mail.mailers.smtp.port', 587);
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';

            $mail->setFrom((string) config('mail.from.address', 'planit.10000@gmail.com'), (string) config('mail.from.name', 'Planit'));

            $mail->addAddress($to);

            $mail->isHTML(true);

            $mail->Subject = (string) $subject;
            $mail->Body = (string) $html;

            $mail->send();
        } catch (Exception $e) {
            throw new \Exception("Error al enviar email: " . $mail->ErrorInfo);
        }
    }
}
