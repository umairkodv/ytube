<?php
// This is an updated version of fix-download.php that handles YouTube rate limits
// Include this file in your index.php

// Function to fix the download process
function fix_download_process($url, $format_key, $config) {
    // Log the start of the download process
    debug_log("Starting fixed download process for URL: $url, Format: $format_key", $config);
    
    // Check if this is a YouTube URL
    $is_youtube = (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false);
    
    // If it's a YouTube URL, use the specialized bypass function
    if ($is_youtube) {
        require_once('youtube-bypass.php');
        bypass_youtube_rate_limit($url, $format_key, $config);
        exit;
    }
    
    // Get video info to get the title
    $video_info = get_video_info($url, $config);
    
    if (!$video_info['success']) {
        debug_log("Failed to get video info for download", $config);
        header('HTTP/1.0 400 Bad Request');
        echo json_encode([
            'success' => false,
            'message' => 'Failed to get video information'
        ]);
        exit;
    }
    
    $info = $video_info['info'];
    $title = $info['sanitized_title'];
    $format = $config['format_options'][$format_key];
    $ext = $format['ext'];
    
    // Create a temporary file with unique name
    $temp_file = $config['temp_dir'] . uniqid('download_') . '.' . $ext;
    debug_log("Temp file: $temp_file", $config);
    
    // Get a random user agent
    $user_agents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.107 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0',
        'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1'
    ];
    $user_agent = $user_agents[array_rand($user_agents)];
    
    // Determine platform
    $is_facebook = (strpos($url, 'facebook.com') !== false || strpos($url, 'fb.watch') !== false);
    $is_instagram = (strpos($url, 'instagram.com') !== false);
    $is_tiktok = (strpos($url, 'tiktok.com') !== false);
    
    // Build the command - using a simplified approach that's more likely to work
    $ytdlp_path = $config['ytdlp_path'];
    $escaped_url = escapeshellarg($url);
    
    // Try multiple approaches, from simplest to more complex
    $download_success = false;
    $error_message = '';
    
    // Approach 1: Simplest possible command
    debug_log("Trying simplest download approach", $config);
    $simple_cmd = "$ytdlp_path --no-check-certificate -o \"$temp_file\" $escaped_url";
    debug_log("Executing command: $simple_cmd", $config);
    
    exec($simple_cmd . " 2>&1", $output1, $return_var1);
    debug_log("Command returned: $return_var1", $config);
    
    if ($return_var1 === 0 && file_exists($temp_file) && filesize($temp_file) > 1000) {
        $download_success = true;
        debug_log("Simple download successful", $config);
    } else {
        $error_message = implode("\n", $output1);
        debug_log("Simple download failed: $error_message", $config);
        
        // Approach 2: With user agent
        debug_log("Trying download with user agent", $config);
        $ua_cmd = "$ytdlp_path --no-check-certificate --user-agent " . escapeshellarg($user_agent) . " -o \"$temp_file\" $escaped_url";
        debug_log("Executing command: $ua_cmd", $config);
        
        exec($ua_cmd . " 2>&1", $output2, $return_var2);
        debug_log("Command returned: $return_var2", $config);
        
        if ($return_var2 === 0 && file_exists($temp_file) && filesize($temp_file) > 1000) {
            $download_success = true;
            debug_log("User agent download successful", $config);
        } else {
            $error_message = implode("\n", $output2);
            debug_log("User agent download failed: $error_message", $config);
            
            // Approach 3: Platform-specific approach
            debug_log("Trying platform-specific download", $config);
            
            if ($is_facebook) {
                $platform_cmd = "$ytdlp_path --no-check-certificate --user-agent " . 
                               escapeshellarg('Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1') . 
                               " -o \"$temp_file\" $escaped_url";
            } elseif ($is_instagram) {
                $platform_cmd = "$ytdlp_path --no-check-certificate --user-agent " . 
                               escapeshellarg('Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1') . 
                               " -o \"$temp_file\" $escaped_url";
            } else {
                $platform_cmd = "$ytdlp_path --no-check-certificate --user-agent " . 
                               escapeshellarg($user_agent) . " -o \"$temp_file\" $escaped_url";
            }
            
            debug_log("Executing command: $platform_cmd", $config);
            exec($platform_cmd . " 2>&1", $output3, $return_var3);
            debug_log("Command returned: $return_var3", $config);
            
            if ($return_var3 === 0 && file_exists($temp_file) && filesize($temp_file) > 1000) {
                $download_success = true;
                debug_log("Platform-specific download successful", $config);
            } else {
                $error_message = implode("\n", $output3);
                debug_log("Platform-specific download failed: $error_message", $config);
                
                // Approach 4: Last resort - direct URL extraction
                debug_log("Trying direct URL extraction", $config);
                $extract_cmd = "$ytdlp_path --no-check-certificate --get-url $escaped_url";
                debug_log("Executing command: $extract_cmd", $config);
                
                exec($extract_cmd . " 2>&1", $output4, $return_var4);
                debug_log("Command returned: $return_var4", $config);
                
                if ($return_var4 === 0 && !empty($output4[0]) && filter_var($output4[0], FILTER_VALIDATE_URL)) {
                    $direct_url = $output4[0];
                    debug_log("Got direct URL: $direct_url", $config);
                    
                    // Download using PHP's file_get_contents or curl
                    if (function_exists('curl_init')) {
                        debug_log("Downloading with curl", $config);
                        $ch = curl_init($direct_url);
                        $fp = fopen($temp_file, 'wb');
                        curl_setopt($ch, CURLOPT_FILE, $fp);
                        curl_setopt($ch, CURLOPT_HEADER, 0);
                        curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
                        curl_exec($ch);
                        curl_close($ch);
                        fclose($fp);
                    } else {
                        debug_log("Downloading with file_get_contents", $config);
                        $context = stream_context_create([
                            'http' => [
                                'header' => "User-Agent: $user_agent\r\n"
                            ]
                        ]);
                        $video_data = file_get_contents($direct_url, false, $context);
                        file_put_contents($temp_file, $video_data);
                    }
                    
                    if (file_exists($temp_file) && filesize($temp_file) > 1000) {
                        $download_success = true;
                        debug_log("Direct URL download successful", $config);
                    } else {
                        $error_message = "Failed to download from direct URL";
                        debug_log("Direct URL download failed", $config);
                    }
                } else {
                    $error_message = implode("\n", $output4);
                    debug_log("Direct URL extraction failed: $error_message", $config);
                }
            }
        }
    }
    
    // Check if any download method succeeded
    if ($download_success) {
        debug_log("Download successful, preparing to serve file: " . filesize($temp_file) . " bytes", $config);
        
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
        debug_log("All download methods failed", $config);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Failed to download video',
            'details' => $error_message
        ]);
        
        // Clean up
        if (file_exists($temp_file)) {
            unlink($temp_file);
        }
    }
    
    exit;
}
?>
