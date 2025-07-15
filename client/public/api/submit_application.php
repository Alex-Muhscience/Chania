<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../src/Services/ApplicationService.php';
require_once __DIR__ . '/../../src/Utils/ResponseHandler.php';

header('Content-Type: application/json');

try {
    // Verify CSRF token
    if (!FormValidator::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        ResponseHandler::errorResponse('Invalid CSRF token', 403);
    }

    $applicationService = new ApplicationService($db);
    $applicationId = $applicationService->submitApplication($_POST);

    ResponseHandler::successResponse([
        'referenceId' => 'APP-' . str_pad($applicationId, 6, '0', STR_PAD_LEFT)
    ], 'Application submitted successfully');

} catch (Exception $e) {
    ResponseHandler::errorResponse($e->getMessage());
}
?>