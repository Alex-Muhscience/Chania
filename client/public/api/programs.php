<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../src/Services/ProgramService.php';
require_once __DIR__ . '/../../src/Utils/ResponseHandler.php';

try {
    $programService = new ProgramService($db);

    // Get programs based on query parameters
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $category = isset($_GET['category']) ? Database::sanitize($_GET['category']) : null;

    $result = $programService->getPaginatedPrograms($page, 6, $category);
    ResponseHandler::successResponse($result);

} catch (Exception $e) {
    ResponseHandler::errorResponse($e->getMessage());
}
?>