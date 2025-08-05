<?php
/**
 * Contact Submission Endpoint (Legacy - Redirects to new API)
 * This endpoint redirects requests to the new API system
 */

// Redirect to new API endpoint
header('Location: /api/contact');
exit;
