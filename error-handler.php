<?php
// This file sets up proper error handling to prevent HTML errors from breaking JSON responses
// Include this file at the very top of your index.php

// Function to check if the current request expects a JSON response
function is_json_request() {
    if (isset($_GET['action']) || 
        (isset($_GET['download']) && isset($_GET['url']) && isset($_GET['format']))) {
        return true;
    }
    return false;
}

// Custom error handler for JSON requests
function json_error_handler($errno, $errstr, $errfile, $errline) {
    if (is_json_request()) {
        $error_message = "PHP Error: [$errno] $errstr in $errfile on line $errline";
        
        // Log the error
        error_log($error_message);
        
        // Return a proper JSON error response
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Server error occurred',
            'debug' => $error_message
        ]);
        exit;
    } else {
        // For non-JSON requests, use the default error handler
        return false;
    }
}

// Set the custom error handler
if (is_json_request()) {
    set_error_handler('json_error_handler');
}

// Prevent any output before headers are sent for JSON requests
if (is_json_request()) {
    ob_start();
}
?>
