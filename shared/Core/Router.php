<?php
/**
 * Simple API Router for handling RESTful endpoints
 */
class Router {
    private $routes = [];
    private $middlewares = [];
    
    public function __construct() {
        // Set JSON response header by default for API routes
        header('Content-Type: application/json');
    }
    
    /**
     * Add GET route
     */
    public function get($path, $controller) {
        $this->addRoute('GET', $path, $controller);
    }
    
    /**
     * Add POST route
     */
    public function post($path, $controller) {
        $this->addRoute('POST', $path, $controller);
    }
    
    /**
     * Add PUT route
     */
    public function put($path, $controller) {
        $this->addRoute('PUT', $path, $controller);
    }
    
    /**
     * Add DELETE route
     */
    public function delete($path, $controller) {
        $this->addRoute('DELETE', $path, $controller);
    }
    
    /**
     * Add route to routes array
     */
    private function addRoute($method, $path, $controller) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'controller' => $controller
        ];
    }
    
    /**
     * Add middleware
     */
    public function middleware($middleware) {
        $this->middlewares[] = $middleware;
    }
    
    /**
     * Run the router
     */
    public function run() {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove query parameters
        $requestUri = strtok($requestUri, '?');
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && $this->matchPath($route['path'], $requestUri)) {
                // Extract parameters from URL
                $params = $this->extractParams($route['path'], $requestUri);
                
                // Run middlewares
                foreach ($this->middlewares as $middleware) {
                    if (is_callable($middleware)) {
                        $middleware();
                    }
                }
                
                // Execute controller
                $this->executeController($route['controller'], $params);
                return;
            }
        }
        
        // Route not found
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'Route not found',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Check if path matches route pattern
     */
    private function matchPath($routePath, $requestPath) {
        // Convert route path to regex pattern
        $pattern = preg_replace('/:[^\/]+/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';
        
        return preg_match($pattern, $requestPath);
    }
    
    /**
     * Extract parameters from URL
     */
    private function extractParams($routePath, $requestPath) {
        $params = [];
        
        // Get parameter names from route path
        preg_match_all('/:([^\/]+)/', $routePath, $paramNames);
        
        // Get parameter values from request path
        $pattern = preg_replace('/:[^\/]+/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';
        
        if (preg_match($pattern, $requestPath, $matches)) {
            array_shift($matches); // Remove full match
            
            foreach ($paramNames[1] as $index => $paramName) {
                if (isset($matches[$index])) {
                    $params[$paramName] = $matches[$index];
                }
            }
        }
        
        return $params;
    }
    
    /**
     * Execute controller method
     */
    private function executeController($controllerAction, $params = []) {
        if (strpos($controllerAction, '@') !== false) {
            list($controllerName, $method) = explode('@', $controllerAction);
            
            // Include controller file
            $controllerFile = __DIR__ . "/../../api/v1/controllers/{$controllerName}.php";
            
            if (file_exists($controllerFile)) {
                require_once $controllerFile;
                
                if (class_exists($controllerName)) {
                    $controller = new $controllerName();
                    
                    if (method_exists($controller, $method)) {
                        // Pass parameters to controller method
                        call_user_func_array([$controller, $method], [$params]);
                    } else {
                        http_response_code(500);
                        echo json_encode([
                            'status' => 'error',
                            'message' => "Method {$method} not found in {$controllerName}",
                            'timestamp' => date('Y-m-d H:i:s')
                        ]);
                    }
                } else {
                    http_response_code(500);
                    echo json_encode([
                        'status' => 'error',
                        'message' => "Controller {$controllerName} not found",
                        'timestamp' => date('Y-m-d H:i:s')
                    ]);
                }
            } else {
                http_response_code(500);
                echo json_encode([
                    'status' => 'error',
                    'message' => "Controller file not found: {$controllerFile}",
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            }
        } else {
            // Direct function call
            if (is_callable($controllerAction)) {
                call_user_func_array($controllerAction, [$params]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Invalid controller action',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            }
        }
    }
}
?>
