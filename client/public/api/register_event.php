<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../src/Services/EventService.php';
require_once __DIR__ . '/../../src/Utils/ResponseHandler.php';

header('Content-Type: application/json');

try {
    // Verify CSRF token
    if (!FormValidator::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        ResponseHandler::errorResponse('Invalid CSRF token', 403);
    }

    $eventService = new EventService($db);
    $success = $eventService->registerForEvent($_POST['event_id'], $_POST);

    if ($success) {
        ResponseHandler::successResponse(null, 'Registration successful');
    } else {
        ResponseHandler::errorResponse('Failed to register for event');
    }

} catch (Exception $e) {
    ResponseHandler::errorResponse($e->getMessage());
}
?>