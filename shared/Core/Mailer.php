<?php
require_once __DIR__ . '/../../vendor/autoload.php';

class Mailer {
    private $mailer;

    public function __construct() {
        $this->mailer = new PHPMailer\PHPMailer\PHPMailer(true);

        // Server settings
        $this->mailer->isSMTP();
        $this->mailer->Host = 'smtp.gmail.com';
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = 'your_email@example.com';
        $this->mailer->Password = 'your_password';
        $this->mailer->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = 587;

        // Sender
        $this->mailer->setFrom('no-reply@skillsforafrica.org', 'Skills for Africa');
        $this->mailer->addReplyTo('info@skillsforafrica.org', 'Information');
    }

    public function sendApplicationConfirmation($toEmail, $toName, $applicationId, $programTitle) {
        try {
            // Recipient
            $this->mailer->addAddress($toEmail, $toName);

            // Content
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Application Received - Skills for Africa';

            $message = "
                <h2>Thank you for your application!</h2>
                <p>We've received your application for <strong>{$programTitle}</strong> and will review it shortly.</p>
                <p>Your application reference number is: <strong>APP-" . str_pad($applicationId, 6, '0', STR_PAD_LEFT) . "</strong></p>
                <p>We'll contact you via email once your application has been processed. This typically takes 3-5 business days.</p>
                <hr>
                <p>If you have any questions, please reply to this email or contact us at info@skillsforafrica.org</p>
            ";

            $this->mailer->Body = $message;
            $this->mailer->AltBody = strip_tags($message);

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Mailer Error: " . $this->mailer->ErrorInfo);
            return false;
        }
    }

    public function sendAdminNotification($applicationId, $applicantName, $programTitle) {
        try {
            // Recipient (admin)
            $this->mailer->addAddress('admin@skillsforafrica.org', 'Admin');

            // Content
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'New Application Received - Skills for Africa';

            $message = "
                <h2>New Application Received</h2>
                <p><strong>Applicant:</strong> {$applicantName}</p>
                <p><strong>Program:</strong> {$programTitle}</p>
                <p><strong>Application ID:</strong> APP-" . str_pad($applicationId, 6, '0', STR_PAD_LEFT) . "</p>
                <hr>
                <p>Please review this application in the admin panel.</p>
            ";

            $this->mailer->Body = $message;
            $this->mailer->AltBody = strip_tags($message);

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Mailer Error: " . $this->mailer->ErrorInfo);
            return false;
        }
    }
}
?>