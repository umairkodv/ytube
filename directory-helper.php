<?php
// This file provides safer directory handling functions
// Include this file in your index.php

// Function to safely create a directory without using chmod
function safe_mkdir($dir) {
    if (!file_exists($dir)) {
        // Try to create the directory without specifying permissions
        // The server will use default permissions
        if (!@mkdir($dir, 0777, true)) {
            // If creation fails, log the error
            error_log("Failed to create directory: $dir");
            return false;
        }
    }
    return true;
}

// Function to check if a directory is writable and try to make it writable
function ensure_writable_dir($dir) {
    if (!file_exists($dir)) {
        return safe_mkdir($dir);
    }
    
    if (!is_writable($dir)) {
        // Log that the directory is not writable
        error_log("Directory is not writable: $dir");
        
        // We won't try to chmod as it's likely to fail
        // Just return false to indicate the problem
        return false;
    }
    
    return true;
}
?>
