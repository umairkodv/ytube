<?php
// This file contains specialized functions to bypass YouTube rate limits
// Include this file in your index.php

// Function to bypass YouTube rate limits using a proxy rotation approach
function bypass_youtube_rate_limit($url, $format_key, $config) {
    debug_log("Starting YouTube bypass download for URL: $url, Format: $format_key", $config);
    
    // Get video info to get the title
    $video_info = get_youtube_video_info($url, $config);
    
    if (!$video_info['success']) {
        debug_log("Failed to get YouTube video info for download", $config);
        header('HTTP/1.0 400 Bad Request');
        echo json_encode([
            'success' => false,
            'message' => 'Failed to get YouTube video information'
        ]);
        exit;
    }
    
    $info = $video_info['info'];
    $title = $info['sanitized_title'];
    $format = $config['format_options'][$format_key];
    $ext = $format['ext'];
    
    // Create a temporary file
    $temp_file = $config['temp_dir'] . uniqid('download_') . '.' . $ext;
    debug_log("Temp file: $temp_file", $config);
    
    // Extract video ID
    $video_id = $info['ext_id'];
    debug_log("Video ID: $video_id", $config);
    
    // Try to get direct video URL using a different approach
    $success = false;
    
    // Approach 1: Try using youtube-dl with specific options to avoid rate limits
    debug_log("Trying approach 1: youtube-dl with specific options", $config);
    $cmd1 = "{$config['ytdlp_path']} --no-check-certificate --no-warnings " .
           "--extractor-args \"youtube:player_client=android\" " .
           "--user-agent \"Mozilla/5.0 (Android 11; Mobile; rv:68.0) Gecko/68.0 Firefox/88.0\" " .
           "--geo-bypass --geo-bypass-country US " .
           "--output \"$temp_file\" " . escapeshellarg($url) . " 2>&1";
    
    debug_log("Executing command: $cmd1", $config);
    exec($cmd1, $output1, $return_var1);
    
    if ($return_var1 === 0 && file_exists($temp_file) && filesize($temp_file) > 1000) {
        debug_log("Approach 1 successful", $config);
        $success = true;
    } else {
        debug_log("Approach 1 failed: " . implode("\n", $output1), $config);
        
        // Approach 2: Try using a different format string
        debug_log("Trying approach 2: Different format string", $config);
        $cmd2 = "{$config['ytdlp_path']} --no-check-certificate --no-warnings " .
               "-f \"best[ext=mp4]/best\" " .
               "--user-agent \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36\" " .
               "--output \"$temp_file\" " . escapeshellarg($url) . " 2>&1";
        
        debug_log("Executing command: $cmd2", $config);
        exec($cmd2, $output2, $return_var2);
        
        if ($return_var2 === 0 && file_exists($temp_file) && filesize($temp_file) > 1000) {
            debug_log("Approach 2 successful", $config);
            $success = true;
        } else {
            debug_log("Approach 2 failed: " . implode("\n", $output2), $config);
            
            // Approach 3: Try using a direct API call to get the video URL
            debug_log("Trying approach 3: Direct API call", $config);
            
            // Get direct URL using yt-dlp's --get-url option
            $cmd3 = "{$config['ytdlp_path']} --no-check-certificate --no-warnings " .
                   "--get-url " . escapeshellarg($url) . " 2>&1";
            
            debug_log("Executing command: $cmd3", $config);
            exec($cmd3, $output3, $return_var3);
            
            if ($return_var3 === 0 && !empty($output3[0]) && filter_var($output3[0], FILTER_VALIDATE_URL)) {
                $direct_url = $output3[0];
                debug_log("Got direct URL: $direct_url", $config);
                
                // Download using curl
                if (function_exists('curl_init')) {
                    debug_log("Downloading with curl", $config);
                    $ch = curl_init($direct_url);
                    $fp = fopen($temp_file, 'wb');
                    curl_setopt($ch, CURLOPT_FILE, $fp);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 300);
                    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36");
                    curl_exec($ch);
                    curl_close($ch);
                    fclose($fp);
                    
                    if (file_exists($temp_file) && filesize($temp_file) > 1000) {
                        debug_log("Approach 3 successful", $config);
                        $success = true;
                    } else {
                        debug_log("Approach 3 failed: curl download failed", $config);
                    }
                } else {
                    debug_log("Curl not available", $config);
                }
            } else {
                debug_log("Approach 3 failed: Could not get direct URL", $config);
            }
            
            // Approach 4: Try using a public API proxy
            if (!$success) {
                debug_log("Trying approach 4: Public API proxy", $config);
                
                // Use invidious API to get video info
                $invidious_instances = [
                    'https://invidious.snopyta.org',
                    'https://yewtu.be',
                    'https://invidious.kavin.rocks',
                    'https://vid.puffyan.us'
                ];
                
                foreach ($invidious_instances as $instance) {
                    $api_url = "$instance/api/v1/videos/$video_id";
                    debug_log("Trying Invidious instance: $api_url", $config);
                    
                    $context = stream_context_create([
                        'http' => [
                            'method' => 'GET',
                            'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36\r\n"
                        ]
                    ]);
                    
                    $response = @file_get_contents($api_url, false, $context);
                    
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
                                if (function_exists('curl_init')) {
                                    debug_log("Downloading with curl", $config);
                                    $ch = curl_init($direct_url);
                                    $fp = fopen($temp_file, 'wb');
                                    curl_setopt($ch, CURLOPT_FILE, $fp);
                                    curl_setopt($ch, CURLOPT_HEADER, 0);
                                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                                    curl_setopt($ch, CURLOPT_TIMEOUT, 300);
                                    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36");
                                    curl_exec($ch);
                                    curl_close($ch);
                                    fclose($fp);
                                    
                                    if (file_exists($temp_file) && filesize($temp_file) > 1000) {
                                        debug_log("Approach 4 successful", $config);
                                        $success = true;
                                        break;
                                    } else {
                                        debug_log("Approach 4 failed: curl download failed", $config);
                                    }
                                } else {
                                    debug_log("Curl not available", $config);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    
    // Check if any approach was successful
    if ($success) {
        debug_log("YouTube bypass download successful, preparing to serve file: " . filesize($temp_file) . " bytes", $config);
        
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
        debug_log("All YouTube bypass approaches failed", $config);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Failed to download YouTube video due to rate limiting. Please try again later or try a different video.'
        ]);
        
        // Clean up
        if (file_exists($temp_file)) {
            unlink($temp_file);
        }
    }
    
    exit;
}
?>
