<?php
// This file provides a direct method to bypass YouTube restrictions
// Include this file in your index.php

// Function to directly bypass YouTube restrictions
function youtube_direct_bypass($url, $format_key, $config) {
    debug_log("Starting YouTube direct bypass for URL: $url, Format: $format_key", $config);
    
    // Extract video ID from URL
    $video_id = '';
    if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches)) {
        $video_id = $matches[1];
    } else {
        debug_log("Failed to extract YouTube video ID", $config);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Invalid YouTube URL'
        ]);
        exit;
    }
    
    debug_log("Extracted video ID: $video_id", $config);
    
    // Get basic video info
    $title = "YouTube_Video_$video_id";
    $format = $config['format_options'][$format_key];
    $ext = $format['ext'];
    
    // Create a temporary file
    $temp_file = $config['temp_dir'] . uniqid('download_') . '.' . $ext;
    debug_log("Temp file: $temp_file", $config);
    
    // Try alternative methods to download the video
    $success = false;
    
    // Method 1: Try using a third-party API service
    $api_services = [
        "https://api.vevioz.com/api/button/mp4/$video_id",
        "https://api.vevioz.com/api/button/mp3/$video_id"
    ];
    
    foreach ($api_services as $api_url) {
        debug_log("Trying API service: $api_url", $config);
        
        // Use curl to get the API response
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $api_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36");
            $response = curl_exec($ch);
            curl_close($ch);
            
            // Try to extract direct download links from the response
            if ($response) {
                debug_log("Got API response, length: " . strlen($response), $config);
                
                // Look for direct download links in the response
                if (preg_match_all('/(https?:\/\/[^\s"\']+\.(?:mp4|mp3)[^\s"\']*)/i', $response, $matches)) {
                    foreach ($matches[1] as $direct_url) {
                        debug_log("Found potential direct URL: $direct_url", $config);
                        
                        // Try to download the file
                        $ch = curl_init($direct_url);
                        $fp = fopen($temp_file, 'wb');
                        curl_setopt($ch, CURLOPT_FILE, $fp);
                        curl_setopt($ch, CURLOPT_HEADER, 0);
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
                        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36");
                        curl_exec($ch);
                        $curl_error = curl_error($ch);
                        curl_close($ch);
                        fclose($fp);
                        
                        if (empty($curl_error) && file_exists($temp_file) && filesize($temp_file) > 1000) {
                            debug_log("Successfully downloaded from direct URL, size: " . filesize($temp_file), $config);
                            $success = true;
                            break 2; // Break out of both loops
                        } else {
                            debug_log("Failed to download from direct URL: $curl_error", $config);
                            if (file_exists($temp_file)) {
                                unlink($temp_file);
                            }
                        }
                    }
                }
            }
        }
    }
    
    // Method 2: Try using a different YouTube frontend
    if (!$success) {
        $invidious_instances = [
            "https://invidious.snopyta.org",
            "https://yewtu.be",
            "https://invidious.kavin.rocks",
            "https://vid.puffyan.us"
        ];
        
        foreach ($invidious_instances as $instance) {
            debug_log("Trying Invidious instance: $instance", $config);
            
            $api_url = "$instance/api/v1/videos/$video_id";
            
            // Use curl to get the API response
            if (function_exists('curl_init')) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $api_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36");
                $response = curl_exec($ch);
                curl_close($ch);
                
                if ($response) {
                    $data = json_decode($response, true);
                    
                    if ($data && isset($data['adaptiveFormats'])) {
                        debug_log("Got video data from Invidious", $config);
                        
                        // Find the best format
                        $best_format = null;
                        $best_quality = 0;
                        
                        foreach ($data['adaptiveFormats'] as $format_data) {
                            if (isset($format_data['url']) && isset($format_data['quality'])) {
                                if ($format_data['quality'] > $best_quality) {
                                    $best_format = $format_data;
                                    $best_quality = $format_data['quality'];
                                }
                            }
                        }
                        
                        if ($best_format && isset($best_format['url'])) {
                            $direct_url = $best_format['url'];
                            debug_log("Got direct URL from Invidious: $direct_url", $config);
                            
                            // Download using curl
                            $ch = curl_init($direct_url);
                            $fp = fopen($temp_file, 'wb');
                            curl_setopt($ch, CURLOPT_FILE, $fp);
                            curl_setopt($ch, CURLOPT_HEADER, 0);
                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                            curl_setopt($ch, CURLOPT_TIMEOUT, 300);
                            curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36");
                            curl_exec($ch);
                            $curl_error = curl_error($ch);
                            curl_close($ch);
                            fclose($fp);
                            
                            if (empty($curl_error) && file_exists($temp_file) && filesize($temp_file) > 1000) {
                                debug_log("Successfully downloaded from Invidious, size: " . filesize($temp_file), $config);
                                $success = true;
                                break;
                            } else {
                                debug_log("Failed to download from Invidious: $curl_error", $config);
                                if (file_exists($temp_file)) {
                                    unlink($temp_file);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    
    // Method 3: Try using a YouTube downloader service API
    if (!$success) {
        $y2mate_url = "https://www.y2mate.com/mates/analyzeV2/ajax";
        $y2mate_data = [
            'k_query' => "https://www.youtube.com/watch?v=$video_id",
            'k_page' => 'home',
            'hl' => 'en',
            'q_auto' => 0
        ];
        
        debug_log("Trying Y2Mate API", $config);
        
        // Use curl to get the API response
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $y2mate_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($y2mate_data));
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36");
            $response = curl_exec($ch);
            curl_close($ch);
            
            if ($response) {
                $data = json_decode($response, true);
                
                if ($data && isset($data['links']) && isset($data['links']['mp4'])) {
                    debug_log("Got video data from Y2Mate", $config);
                    
                    // Find the best format
                    $best_format = null;
                    $best_quality = 0;
                    
                    foreach ($data['links']['mp4'] as $format_data) {
                        if (isset($format_data['url']) && isset($format_data['q'])) {
                            $quality = (int)str_replace('p', '', $format_data['q']);
                            if ($quality > $best_quality) {
                                $best_format = $format_data;
                                $best_quality = $quality;
                            }
                        }
                    }
                    
                    if ($best_format && isset($best_format['url'])) {
                        $direct_url = $best_format['url'];
                        debug_log("Got direct URL from Y2Mate: $direct_url", $config);
                        
                        // Download using curl
                        $ch = curl_init($direct_url);
                        $fp = fopen($temp_file, 'wb');
                        curl_setopt($ch, CURLOPT_FILE, $fp);
                        curl_setopt($ch, CURLOPT_HEADER, 0);
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
                        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36");
                        curl_exec($ch);
                        $curl_error = curl_error($ch);
                        curl_close($ch);
                        fclose($fp);
                        
                        if (empty($curl_error) && file_exists($temp_file) && filesize($temp_file) > 1000) {
                            debug_log("Successfully downloaded from Y2Mate, size: " . filesize($temp_file), $config);
                            $success = true;
                        } else {
                            debug_log("Failed to download from Y2Mate: $curl_error", $config);
                            if (file_exists($temp_file)) {
                                unlink($temp_file);
                            }
                        }
                    }
                }
            }
        }
    }
    
    // Check if any method was successful
    if ($success) {
        debug_log("YouTube direct bypass successful, preparing to serve file: " . filesize($temp_file) . " bytes", $config);
        
        // Return success with file info
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'file' => [
                'name' => $title . '.' . $ext,
                'path' => $temp_file,
                'size' => filesize($temp_file)
            ]
        ]);
    } else {
        debug_log("All YouTube direct bypass methods failed", $config);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Failed to download YouTube video. YouTube is blocking our server. Please try a different video or platform.'
        ]);
        
        // Clean up
        if (file_exists($temp_file)) {
            unlink($temp_file);
        }
    }
    
    exit;
}
?>
