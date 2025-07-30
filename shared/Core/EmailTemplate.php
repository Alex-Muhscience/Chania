<?php

class EmailTemplate {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM email_templates ORDER BY name ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM email_templates WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getByName($name) {
        $stmt = $this->db->prepare("SELECT * FROM email_templates WHERE name = ? AND is_active = 1");
        $stmt->execute([$name]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO email_templates (name, subject, body, variables, is_active) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $data['name'],
            $data['subject'],
            $data['body'],
            json_encode($data['variables'] ?? []),
            $data['is_active'] ?? 1
        ]);
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE email_templates 
            SET name = ?, subject = ?, body = ?, variables = ?, is_active = ?
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['name'],
            $data['subject'],
            $data['body'],
            json_encode($data['variables'] ?? []),
            $data['is_active'] ?? 1,
            $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM email_templates WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function activate($id) {
        $stmt = $this->db->prepare("UPDATE email_templates SET is_active = 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function deactivate($id) {
        $stmt = $this->db->prepare("UPDATE email_templates SET is_active = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function renderTemplate($templateName, $variables = []) {
        $template = $this->getByName($templateName);
        if (!$template) {
            throw new Exception("Email template '{$templateName}' not found");
        }

        $subject = $template['subject'];
        $body = $template['body'];

        // Replace variables in subject and body
        foreach ($variables as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $subject = str_replace($placeholder, $value, $subject);
            $body = str_replace($placeholder, $value, $body);
        }

        return [
            'subject' => $subject,
            'body' => $body,
            'template' => $template
        ];
    }

    public function sendEmail($to, $templateName, $variables = [], $fromEmail = null, $fromName = null) {
        try {
            $rendered = $this->renderTemplate($templateName, $variables);
            
            // Set default sender if not provided
            if (!$fromEmail) {
                $fromEmail = 'noreply@digitalempowermentnetwork.org';
            }
            if (!$fromName) {
                $fromName = 'Digital Empowerment Network';
            }

            // Create email headers
            $headers = [
                'MIME-Version: 1.0',
                'Content-type: text/html; charset=UTF-8',
                'From: ' . $fromName . ' <' . $fromEmail . '>',
                'Reply-To: ' . $fromEmail,
                'X-Mailer: PHP/' . phpversion()
            ];

            // Send email using PHP's mail function
            $success = mail(
                $to,
                $rendered['subject'],
                $rendered['body'],
                implode("\r\n", $headers)
            );

            if ($success) {
                $this->logEmail($to, $templateName, $rendered['subject'], 'sent');
                return true;
            } else {
                $this->logEmail($to, $templateName, $rendered['subject'], 'failed');
                return false;
            }
        } catch (Exception $e) {
            $this->logEmail($to, $templateName, '', 'error', $e->getMessage());
            throw $e;
        }
    }

    private function logEmail($to, $template, $subject, $status, $error = null) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO admin_logs (user_id, action, entity_type, entity_id, details, ip_address) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $details = json_encode([
                'to' => $to,
                'template' => $template,
                'subject' => $subject,
                'status' => $status,
                'error' => $error
            ]);

            $stmt->execute([
                $_SESSION['user_id'] ?? null,
                'email_sent',
                'email',
                null,
                $details,
                $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
        } catch (Exception $e) {
            // Silent failure for logging
            error_log("Failed to log email: " . $e->getMessage());
        }
    }

    public function getDefaultTemplates() {
        return [
            'application_received' => [
                'name' => 'Application Received',
                'subject' => 'Application Received - {{program_title}}',
                'body' => '<h2>Thank you for your application!</h2>
                          <p>Dear {{applicant_name}},</p>
                          <p>We have received your application for <strong>{{program_title}}</strong>.</p>
                          <p><strong>Application Details:</strong></p>
                          <ul>
                              <li>Application ID: {{application_id}}</li>
                              <li>Program: {{program_title}}</li>
                              <li>Submitted: {{submission_date}}</li>
                          </ul>
                          <p>We will review your application and get back to you within 5-7 business days.</p>
                          <p>Best regards,<br>Digital Empowerment Network Team</p>',
                'variables' => ['applicant_name', 'program_title', 'application_id', 'submission_date']
            ],
            'application_approved' => [
                'name' => 'Application Approved',
                'subject' => 'Congratulations! Your application has been approved - {{program_title}}',
                'body' => '<h2>Congratulations!</h2>
                          <p>Dear {{applicant_name}},</p>
                          <p>We are pleased to inform you that your application for <strong>{{program_title}}</strong> has been approved!</p>
                          <p><strong>Next Steps:</strong></p>
                          <p>{{next_steps}}</p>
                          <p>If you have any questions, please don\'t hesitate to contact us.</p>
                          <p>Welcome to our program!</p>
                          <p>Best regards,<br>Digital Empowerment Network Team</p>',
                'variables' => ['applicant_name', 'program_title', 'next_steps']
            ],
            'event_registration' => [
                'name' => 'Event Registration Confirmation',
                'subject' => 'Event Registration Confirmed - {{event_title}}',
                'body' => '<h2>Registration Confirmed!</h2>
                          <p>Dear {{participant_name}},</p>
                          <p>Thank you for registering for <strong>{{event_title}}</strong>.</p>
                          <p><strong>Event Details:</strong></p>
                          <ul>
                              <li>Date: {{event_date}}</li>
                              <li>Time: {{event_time}}</li>
                              <li>Location: {{event_location}}</li>
                          </ul>
                          <p>We look forward to seeing you there!</p>
                          <p>Best regards,<br>Digital Empowerment Network Team</p>',
                'variables' => ['participant_name', 'event_title', 'event_date', 'event_time', 'event_location']
            ],
            'contact_response' => [
                'name' => 'Contact Form Response',
                'subject' => 'Thank you for contacting us',
                'body' => '<h2>Thank you for reaching out!</h2>
                          <p>Dear {{contact_name}},</p>
                          <p>We have received your message and will get back to you within 24-48 hours.</p>
                          <p><strong>Your Message:</strong></p>
                          <blockquote>{{message}}</blockquote>
                          <p>Best regards,<br>Digital Empowerment Network Team</p>',
                'variables' => ['contact_name', 'message']
            ]
        ];
    }

    public function createDefaultTemplates() {
        $defaultTemplates = $this->getDefaultTemplates();
        $created = 0;

        foreach ($defaultTemplates as $key => $template) {
            // Check if template already exists
            $existing = $this->getByName($key);
            if (!$existing) {
                $data = [
                    'name' => $key,
                    'subject' => $template['subject'],
                    'body' => $template['body'],
                    'variables' => $template['variables'],
                    'is_active' => 1
                ];
                
                if ($this->create($data)) {
                    $created++;
                }
            }
        }

        return $created;
    }
}

?>
