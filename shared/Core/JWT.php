<?php
/**
 * Simple JWT Implementation for API Authentication
 */
require_once __DIR__ . '/Environment.php';
class JWT {
    private static $algorithm = 'HS256';
    
    /**
     * Get JWT secret from environment
     */
    private static function getSecretKey() {
        return Environment::getJwtSecret();
    }
    
    /**
     * Generate JWT token
     */
    public static function encode($payload, $key = null) {
        $key = $key ?: self::getSecretKey();
        
        $header = json_encode(['typ' => 'JWT', 'alg' => self::$algorithm]);
        $payload = json_encode($payload);
        
        $base64_header = self::base64UrlEncode($header);
        $base64_payload = self::base64UrlEncode($payload);
        
        $signature = hash_hmac('sha256', $base64_header . "." . $base64_payload, $key, true);
        $base64_signature = self::base64UrlEncode($signature);
        
        return $base64_header . "." . $base64_payload . "." . $base64_signature;
    }
    
    /**
     * Decode and validate JWT token
     */
    public static function decode($jwt, $key = null) {
        $key = $key ?: self::getSecretKey();
        
        $tokenParts = explode('.', $jwt);
        if (count($tokenParts) !== 3) {
            throw new Exception('Invalid token structure');
        }
        
        list($base64_header, $base64_payload, $base64_signature) = $tokenParts;
        
        $header = json_decode(self::base64UrlDecode($base64_header), true);
        $payload = json_decode(self::base64UrlDecode($base64_payload), true);
        
        if (!$header || !$payload) {
            throw new Exception('Invalid token data');
        }
        
        // Verify signature
        $signature = self::base64UrlDecode($base64_signature);
        $expected_signature = hash_hmac('sha256', $base64_header . "." . $base64_payload, $key, true);
        
        if (!hash_equals($expected_signature, $signature)) {
            throw new Exception('Invalid signature');
        }
        
        // Check expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new Exception('Token expired');
        }
        
        return $payload;
    }
    
    /**
     * Base64 URL safe encode
     */
    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Base64 URL safe decode
     */
    private static function base64UrlDecode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
    
    /**
     * Generate token payload for user
     */
    public static function createUserPayload($user, $expiresIn = null) {
        $expiresIn = $expiresIn ?: Environment::getJwtExpiry();
        return [
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role'] ?? 'user',
            'iat' => time(), // issued at
            'exp' => time() + $expiresIn // expires
        ];
    }
    
    /**
     * Extract user ID from token
     */
    public static function getUserId($token) {
        try {
            $payload = self::decode($token);
            return $payload['user_id'] ?? null;
        } catch (Exception $e) {
            return null;
        }
    }
}
?>
