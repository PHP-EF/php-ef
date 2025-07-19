<?php
use PHPMailer\PHPMailer\PHPMailer;
trait SMTP {
    private $smtpConfig;

    // Initializes the SMTP configuration from the core config
    private function initializeSMTPConfig() {
        $this->smtpConfig = $this->core->config->get('SMTP');
    }

    // Allows overriding of SMTP config
    public function setSMTPConfig(array $config) {
        $this->smtpConfig = array_merge($this->smtpConfig, $config);
    }

    // Sends an email using the configured SMTP settings
    public function sendSmtpEmail(string $to, string $subject, string $body, array $attachments = []): bool {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();

            $mail->Host = $this->smtpConfig['host'];
            // SMTP Auth
            if (isset($this->smtpConfig['auth']) && $this->smtpConfig['auth']) {
                $mail->SMTPAuth = true;
                $mail->Username = $this->smtpConfig['username'];
                $mail->Password = $this->smtpConfig['password'];
            } else {
                $mail->SMTPAuth = false;
            }
            // $mail->SMTPSecure = $config['secure'] ?? PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $this->smtpConfig['port'];

            // Recipients
            $mail->setFrom($this->smtpConfig['from_email'], $this->smtpConfig['from_name']);
            $mail->addAddress($to);

            // Attachments
            foreach ($attachments as $filePath => $fileName) {
                $mail->addAttachment($filePath, $fileName);
            }

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("SMTP Error: {$mail->ErrorInfo}");
            return false;
        }
    }

}