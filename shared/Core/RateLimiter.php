<?php
/**
 * Simple Rate Limiter for API endpoints
 */
require_once __DIR__ . '/Environment.php';

class RateLimiter {
    private $db;
    private $rateLimit;
    private $timeWindow;

    public function __construct($db, $customLimit = null, $customWindow = null) {
        $this->db = $db;
        $this->rateLimit = $customLimit ?: Environment::getRateLimitRequests();
        $this->timeWindow = $customWindow ?: Environment::getRateLimitWindow();
    }

    /**
     * Check if request is allowed
     */
    public function check($ipAddress, $endpoint) {
        $currentTime = time();
        $windowStart = $currentTime - $this->timeWindow;

        $stmt = $this->db->prepare("SELECT COUNT(*) as request_count FROM rate_limits WHERE ip_address = ? AND endpoint = ? AND timestamp >= ?");
        $stmt->execute([$ipAddress, $endpoint, $windowStart]);
        $result = $stmt->fetch();

        if ($result['request_count'] >= $this->rateLimit) {
            return false;
        }

        $this->logRequest($ipAddress, $endpoint);
        return true;
    }

    /**
     * Log the request
     */
    private function logRequest($ipAddress, $endpoint) {
        $stmt = $this->db->prepare("INSERT INTO rate_limits (ip_address, endpoint, timestamp) VALUES (?, ?, NOW())");
        $stmt->execute([$ipAddress, $endpoint]);
    }
}
?>
