<?php
require_once __DIR__ . '/../controllers/UsersController.php';

$controller = new UsersController();
$controller->index();
