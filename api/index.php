<?php

use App\Controllers\NewsletterController;
use App\Controllers\CourseApplicationController;
use App\Controllers\ContactController;
use App\Controllers\EventRegistrationController;
use App\Core\Router;

require '../autoload.php';

$router = new Router();

// API Endpoints
$router->post('/api/subscriptions/newsletter', [NewsletterController::class, 'subscribe']);
$router->post('/api/applications/course', [CourseApplicationController::class, 'apply']);
$router->post('/api/contact', [ContactController::class, 'submit']);
$router->post('/api/registrations/event', [EventRegistrationController::class, 'register']);

$router->run();

