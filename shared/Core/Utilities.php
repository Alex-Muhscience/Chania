<?php
class Utilities {
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    public static function redirect($url) {
        // Handle relative URLs
        if (strpos($url, '/') === 0) {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
            $host = $_SERVER['HTTP_HOST'];
            $basePath = dirname($_SERVER['SCRIPT_NAME']);

            // Remove '/admin/public' from the script path to get the base path
            $basePath = str_replace('/admin/public', '', $basePath);
            $basePath = rtrim($basePath, '/');

            $url = $protocol . $host . $basePath . $url;
        }

        header('Location: ' . $url);
        exit;
    }

    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
            self::redirect('/admin/login.php');
        }
    }

    public static function requireRole($requiredRole) {
        self::requireLogin();

        if (!isset($_SESSION['role']) || $_SESSION['role'] !== $requiredRole) {
            self::redirect('/admin/');
        }
    }

    public static function sanitizeInput($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    public static function formatDate($date, $format = 'Y-m-d H:i:s') {
        return date($format, strtotime($date));
    }

    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }

    public static function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    // Added method to format file size from bytes to human-readable format
    public static function formatFileSize(int $bytes, int $decimals = 2): string {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $factor = 0;
        $size = $bytes;

        while ($size >= 1024 && $factor < count($units) - 1) {
            $size /= 1024;
            $factor++;
        }

        return sprintf("%.{$decimals}f %s", $size, $units[$factor]);
    }
}