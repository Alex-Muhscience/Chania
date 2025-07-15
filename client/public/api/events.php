<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../src/Services/EventService.php';
require_once __DIR__ . '/../../src/Utils/ResponseHandler.php';

try {
    $eventService = new EventService($db);

    // Get events based on query parameters
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

    $result = $eventService->getPaginatedEvents($page);
    ResponseHandler::successResponse($result);

} catch (Exception $e) {
    ResponseHandler::errorResponse($e->getMessage());
}
?>