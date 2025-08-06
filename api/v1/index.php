<?php
// Unified API Router for Version 1

require_once __DIR__ . '/../../client/includes/config.php';
require_once __DIR__ . '/../../shared/Core/Router.php';

$router = new Router();

// Authentication API Endpoints
$router->post('/chania/api/v1/auth/login', 'AuthController@login');
$router->post('/chania/api/v1/auth/validate', 'AuthController@validate');
$router->post('/chania/api/v1/auth/refresh', 'AuthController@refresh');
$router->post('/chania/api/v1/auth/logout', 'AuthController@logout');
$router->get('/chania/api/v1/auth/me', 'AuthController@me');

// Contacts API Endpoints
$router->post('/chania/api/v1/contacts', 'ContactsController@create');
$router->get('/chania/api/v1/contacts', 'ContactsController@index');
$router->get('/chania/api/v1/contacts/:id', 'ContactsController@show');
$router->put('/chania/api/v1/contacts/:id', 'ContactsController@update');
$router->delete('/chania/api/v1/contacts/:id', 'ContactsController@delete');

// Applications API Endpoints
$router->post('/chania/api/v1/applications', 'ApplicationsController@create');
$router->get('/chania/api/v1/applications', 'ApplicationsController@index');
$router->get('/chania/api/v1/applications/:id', 'ApplicationsController@show');
$router->put('/chania/api/v1/applications/:id', 'ApplicationsController@update');
$router->delete('/chania/api/v1/applications/:id', 'ApplicationsController@delete');

// Programs API Endpoints
$router->get('/chania/api/v1/programs', 'ProgramsController@index');
$router->get('/chania/api/v1/programs/:id', 'ProgramsController@show');
$router->post('/chania/api/v1/programs', 'ProgramsController@create');
$router->put('/chania/api/v1/programs/:id', 'ProgramsController@update');
$router->delete('/chania/api/v1/programs/:id', 'ProgramsController@delete');

// Events API Endpoints
$router->get('/chania/api/v1/events', 'EventsController@index');
$router->get('/chania/api/v1/events/:id', 'EventsController@show');
$router->post('/chania/api/v1/events/:id/register', 'EventsController@register');
$router->post('/chania/api/v1/events', 'EventsController@create');
$router->put('/chania/api/v1/events/:id', 'EventsController@update');
$router->delete('/chania/api/v1/events/:id', 'EventsController@delete');

// Newsletter API Endpoints
$router->post('/chania/api/v1/newsletter/subscribe', 'NewsletterController@subscribe');
$router->get('/chania/api/v1/newsletter/subscribers', 'NewsletterController@index');
$router->delete('/chania/api/v1/newsletter/subscribers/:id', 'NewsletterController@unsubscribe');

// Dashboard API Endpoints (ADMIN ONLY)
$router->get('/chania/api/v1/dashboard/stats', 'DashboardController@stats');
$router->get('/chania/api/v1/dashboard/recent', 'DashboardController@recent');

$router->run();
