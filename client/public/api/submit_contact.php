<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../src/Services/ContactService.php';
require_once __DIR__ . '/../../src/Utils/ResponseHandler.php';

header('Content-Type: application/json');

try {
    // Verify CSRF token
    if (!FormValidator::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        ResponseHandler::errorResponse('Invalid CSRF token', 403);
    }

    $contactService = new ContactService($db);
    $success = $contactService->submitContactForm($_POST);

    if ($success) {
        ResponseHandler::successResponse(null, 'Thank you for your message! We will get back to you soon.');
    } else {
        ResponseHandler::errorResponse('Failed to send your message');
    }

} catch (Exception $e) {
    ResponseHandler::errorResponse($e->getMessage());
}
?>