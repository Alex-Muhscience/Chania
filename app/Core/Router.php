<?php

namespace App\Core;

class Router
{
    private $routes = [];

    public function post($path, $handler)
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function get($path, $handler)
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function run()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove base path if exists
        $basePath = '/chania';
        if (strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
        }

        if (isset($this->routes[$method][$path])) {
            $handler = $this->routes[$method][$path];
            
            if (is_array($handler)) {
                $controller = new $handler[0]();
                $action = $handler[1];
                $controller->$action();
            } else {
                call_user_func($handler);
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Route not found', 'path' => $path, 'method' => $method]);
        }
    }
}
