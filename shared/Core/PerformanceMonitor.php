<?php
/**
 * Performance Monitoring Script
 * Add this to your admin pages to monitor performance
 */

class PerformanceMonitor {
    private $startTime;
    private $startMemory;
    
    public function __construct() {
        $this->startTime = microtime(true);
        $this->startMemory = memory_get_usage();
    }
    
    public function getStats() {
        $endTime = microtime(true);
        $endMemory = memory_get_usage();
        
        return [
            "execution_time" => number_format($endTime - $this->startTime, 4),
            "memory_used" => number_format(($endMemory - $this->startMemory) / 1024 / 1024, 2),
            "peak_memory" => number_format(memory_get_peak_usage() / 1024 / 1024, 2),
            "queries_count" => $this->getQueryCount()
        ];
    }
    
    private function getQueryCount() {
        // This would require query logging to be enabled
        return "N/A";
    }
    
    public function displayStats() {
        $stats = $this->getStats();
        if (isset($_GET["debug"]) && $_GET["debug"] === "performance") {
            echo "<!-- Performance Stats: ";
            echo "Time: {$stats[\"execution_time\"]}s, ";
            echo "Memory: {$stats[\"memory_used\"]}MB, ";
            echo "Peak: {$stats[\"peak_memory\"]}MB -->";
        }
    }
}

// Usage: Add to the top of your admin pages
// $monitor = new PerformanceMonitor();
// At the end of the page: $monitor->displayStats();
?>