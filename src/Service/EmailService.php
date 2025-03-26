<?php

namespace App\Service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMAILER\SMTP;

final class EmailService
{

    private PHPMailer $mailer;
    public function __construct(
        private readonly string $emailUsername,
        private readonly string $emailPassword,
        private readonly string $emailSmtp,
        private readonly int $emailPort
    ) {
        $this->mailer = new PHPMailer(true);
    }
    
    public function test() : string{
        return "Email : ". $this->emailUsername . " Serveur : " . $this->emailSmtp . " Port : " . $this->emailPort;
    }


    private function config() : void{

        // Configuration SMTP
        //$this->mailer->SMTPDebut = SMTP::DEBUG_OFF;
        $this->mailer->isSMTP();
        $this->mailer->Host = $this->emailSmtp;
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $this->emailUsername;
        $this->mailer->Password = $this->emailPassword;
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = $this->emailPort;
    }

    /**
     * Méthode pour envoyer des emails
     * @param string $to
     * @param string $subject
     * @param string $body
     * @return bool
     */
    public function sendEmail(string $to, string $subject, string $body): string
    {
        try {
            $this->config();
            $this->mailer->setFrom($this->emailUsername, 'Mon Service Mail');
            $this->mailer->addAddress($to);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            $this->mailer->isHTML(true);

            $this->mailer->send();
            return "Un mail de confirmation a été envoyé !";

        } catch (Exception $e) {
            return "Erreur d'envoi d'email : " . $this->mailer->ErrorInfo;
        }
    }
}