<?php
// This file contains platform-specific functions for different video platforms
// Include this file in your main index.php

// YouTube-specific video info function
function get_youtube_video_info($url, $config) {
    debug_log("Getting YouTube video info for URL: $url", $config);
    $ytdlp_path = $config['ytdlp_path'];
    $escaped_url = escapeshellarg($url);
    $output_file = $config['temp_dir'] . 'info_' . md5(uniqid() . $url) . '.json';
    
    // Get a random user agent
    $user_agent = get_random_user_agent($config);
    $user_agent_arg = escapeshellarg($user_agent);
    
    // Check if this is a YouTube Shorts URL
    $is_shorts = (strpos($url, 'youtube.com/shorts') !== false || strpos($url, 'youtu.be/') !== false);
    
    // First try: Use YouTube-specific options with android client
    $cmd = "$ytdlp_path --no-check-certificate --no-warnings --ignore-errors " .
           "--extractor-args \"youtube:player_client=android\" " .
           "--user-agent $user_agent_arg " .
           "--dump-json $escaped_url > \"$output_file\" 2>&1";
    
    debug_log("Executing YouTube command: $cmd", $config);
    exec($cmd, $output, $return_var);
    debug_log("YouTube command returned: $return_var", $config);
    
    // Check if the output file exists and has valid content
    if (file_exists($output_file) && filesize($output_file) > 10) {
        $info_content = file_get_contents($output_file);
        $info = json_decode($info_content, true);
        
        if ($info) {
            debug_log("Successfully parsed YouTube video info", $config);
            @unlink($output_file);
            
            // Sanitize filename for download
            $title = $info['title'] ?? 'video';
            $sanitized_title = preg_replace('/[^\w\s\-]/u', '', $title);
            $sanitized_title = str_replace(' ', '_', $sanitized_title);
            $sanitized_title = substr($sanitized_title, 0, 100);
            if (empty($sanitized_title)) {
                $sanitized_title = 'video_' . time();
            }
            
            return [
                'success' => true,
                'info' => [
                    'title' => $title,
                    'uploader' => $info['uploader'] ?? 'Unknown Uploader',
                    'duration' => isset($info['duration']) ? gmdate("H:i:s", $info['duration']) : 'Unknown',
                    'upload_date' => isset($info['upload_date']) ? 
                        date("Y-m-d", strtotime($info['upload_date'])) : 'Unknown',
                    'view_count' => $info['view_count'] ?? 'Unknown',
                    'like_count' => $info['like_count'] ?? 'Unknown',
                    'thumbnail' => $info['thumbnail'] ?? '',
                    'sanitized_title' => $sanitized_title,
                    'ext_id' => $info['id'] ?? md5($url),
                    'is_shorts' => $is_shorts,
                    'is_youtube' => true
                ]
            ];
        }
    }
    
    // Second try: Use simpler approach
    debug_log("First YouTube approach failed, trying alternative method", $config);
    $alt_cmd = "$ytdlp_path --no-check-certificate --no-warnings --ignore-errors " .
               "--user-agent $user_agent_arg " .
               "--print-json $escaped_url > \"$output_file\" 2>&1";
    
    debug_log("Executing alternative YouTube command: $alt_cmd", $config);
    exec($alt_cmd, $alt_output, $alt_return_var);
    
    if (file_exists($output_file) && filesize($output_file) > 10) {
        $info_content = file_get_contents($output_file);
        $info = json_decode($info_content, true);
        
        if ($info) {
            debug_log("Successfully parsed YouTube video info with alternative method", $config);
            @unlink($output_file);
            
            // Sanitize filename for download
            $title = $info['title'] ?? 'video';
            $sanitized_title = preg_replace('/[^\w\s\-]/u', '', $title);
            $sanitized_title = str_replace(' ', '_', $sanitized_title);
            $sanitized_title = substr($sanitized_title, 0, 100);
            if (empty($sanitized_title)) {
                $sanitized_title = 'video_' . time();
            }
            
            return [
                'success' => true,
                'info' => [
                    'title' => $title,
                    'uploader' => $info['uploader'] ?? 'Unknown Uploader',
                    'duration' => isset($info['duration']) ? gmdate("H:i:s", $info['duration']) : 'Unknown',
                    'upload_date' => isset($info['upload_date']) ? 
                        date("Y-m-d", strtotime($info['upload_date'])) : 'Unknown',
                    'view_count' => $info['view_count'] ?? 'Unknown',
                    'like_count' => $info['like_count'] ?? 'Unknown',
                    'thumbnail' => $info['thumbnail'] ?? '',
                    'sanitized_title' => $sanitized_title,
                    'ext_id' => $info['id'] ?? md5($url),
                    'is_shorts' => $is_shorts,
                    'is_youtube' => true
                ]
            ];
        }
    }
    
    // Third try: Extract video ID and create basic info
    debug_log("Both YouTube approaches failed, using fallback method", $config);
    
    // Extract video ID from URL
    $video_id = '';
    if (strpos($url, 'youtube.com/watch') !== false) {
        parse_str(parse_url($url, PHP_URL_QUERY), $query_params);
        $video_id = $query_params['v'] ?? '';
    } else if (strpos($url, 'youtube.com/shorts/') !== false) {
        $parts = explode('/', parse_url($url, PHP_URL_PATH));
        $video_id = end($parts);
    } else if (strpos($url, 'youtu.be/') !== false) {
        $parts = explode('/', parse_url($url, PHP_URL_PATH));
        $video_id = end($parts);
    }
    
    if (!empty($video_id)) {
        debug_log("Extracted YouTube video ID: $video_id", $config);
        
        // Create basic info manually
        $title = "YouTube Video $video_id";
        $sanitized_title = "YouTube_Video_$video_id";
        $thumbnail = "https://i.ytimg.com/vi/$video_id/hqdefault.jpg";
        
        return [
            'success' => true,
            'info' => [
                'title' => $title,
                'uploader' => 'YouTube Creator',
                'duration' => 'Unknown',
                'upload_date' => date("Y-m-d"),
                'view_count' => 'Unknown',
                'like_count' => 'Unknown',
                'thumbnail' => $thumbnail,
                'sanitized_title' => $sanitized_title,
                'ext_id' => $video_id,
                'is_shorts' => $is_shorts,
                'is_youtube' => true
            ]
        ];
    }
    
    // If all approaches fail
    debug_log("All YouTube approaches failed", $config);
    return [
        'success' => false,
        'message' => 'Failed to retrieve YouTube video information'
    ];
}

// Facebook-specific video info function
function get_facebook_video_info($url, $config) {
    debug_log("Getting Facebook video info for URL: $url", $config);
    $ytdlp_path = $config['ytdlp_path'];
    $escaped_url = escapeshellarg($url);
    $output_file = $config['temp_dir'] . 'info_' . md5(uniqid() . $url) . '.json';
    
    // Get a random user agent
    $user_agent = get_random_user_agent($config);
    $user_agent_arg = escapeshellarg($user_agent);
    
    // Facebook-specific command with user agent
    $cmd = "$ytdlp_path --no-check-certificate --no-warnings --ignore-errors " .
           "--user-agent $user_agent_arg " .
           "--dump-json $escaped_url > \"$output_file\" 2>&1";
    
    debug_log("Executing Facebook command: $cmd", $config);
    exec($cmd, $output, $return_var);
    debug_log("Facebook command returned: $return_var", $config);
    
    // Check if the output file exists and has valid content
    if (file_exists($output_file) && filesize($output_file) > 10) {
        $info_content = file_get_contents($output_file);
        $info = json_decode($info_content, true);
        
        if ($info) {
            debug_log("Successfully parsed Facebook video info", $config);
            @unlink($output_file);
            
            // Sanitize filename for download
            $title = $info['title'] ?? 'Facebook Video';
            $sanitized_title = preg_replace('/[^\w\s\-]/u', '', $title);
            $sanitized_title = str_replace(' ', '_', $sanitized_title);
            $sanitized_title = substr($sanitized_title, 0, 100);
            if (empty($sanitized_title)) {
                $sanitized_title = 'Facebook_Video_' . time();
            }
            
            return [
                'success' => true,
                'info' => [
                    'title' => $title,
                    'uploader' => $info['uploader'] ?? 'Facebook User',
                    'duration' => isset($info['duration']) ? gmdate("H:i:s", $info['duration']) : 'Unknown',
                    'upload_date' => isset($info['upload_date']) ? 
                        date("Y-m-d", strtotime($info['upload_date'])) : 'Unknown',
                    'view_count' => $info['view_count'] ?? 'Unknown',
                    'like_count' => $info['like_count'] ?? 'Unknown',
                    'thumbnail' => $info['thumbnail'] ?? '',
                    'sanitized_title' => $sanitized_title,
                    'ext_id' => $info['id'] ?? md5($url),
                    'is_facebook' => true
                ]
            ];
        }
    }
    
    // Try alternative approach for Facebook
    debug_log("First Facebook approach failed, trying alternative method", $config);
    $alt_cmd = "$ytdlp_path --no-check-certificate --no-warnings --ignore-errors " .
               "--user-agent \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36\" " .
               "-J $escaped_url > \"$output_file\" 2>&1";
    
    debug_log("Executing alternative Facebook command: $alt_cmd", $config);
    exec($alt_cmd, $alt_output, $alt_return_var);
    
    if (file_exists($output_file) && filesize($output_file) > 10) {
        $info_content = file_get_contents($output_file);
        $info = json_decode($info_content, true);
        
        if ($info) {
            debug_log("Successfully parsed Facebook video info with alternative method", $config);
            @unlink($output_file);
            
            // Sanitize filename for download
            $title = $info['title'] ?? 'Facebook Video';
            $sanitized_title = preg_replace('/[^\w\s\-]/u', '', $title);
            $sanitized_title = str_replace(' ', '_', $sanitized_title);
            $sanitized_title = substr($sanitized_title, 0, 100);
            if (empty($sanitized_title)) {
                $sanitized_title = 'Facebook_Video_' . time();
            }
            
            return [
                'success' => true,
                'info' => [
                    'title' => $title,
                    'uploader' => $info['uploader'] ?? 'Facebook User',
                    'duration' => isset($info['duration']) ? gmdate("H:i:s", $info['duration']) : 'Unknown',
                    'upload_date' => isset($info['upload_date']) ? 
                        date("Y-m-d", strtotime($info['upload_date'])) : 'Unknown',
                    'view_count' => $info['view_count'] ?? 'Unknown',
                    'like_count' => $info['like_count'] ?? 'Unknown',
                    'thumbnail' => $info['thumbnail'] ?? '',
                    'sanitized_title' => $sanitized_title,
                    'ext_id' => $info['id'] ?? md5($url),
                    'is_facebook' => true
                ]
            ];
        }
    }
    
    // Extract video ID from URL for basic info
    $video_id = '';
    if (preg_match('/\/videos\/(\d+)/', $url, $matches)) {
        $video_id = $matches[1];
    } else if (preg_match('/\/watch\/\?v=(\d+)/', $url, $matches)) {
        $video_id = $matches[1];
    }
    
    if (!empty($video_id)) {
        debug_log("Extracted Facebook video ID: $video_id", $config);
        
        // Create basic info manually
        $title = "Facebook Video $video_id";
        $sanitized_title = "Facebook_Video_$video_id";
        
        return [
            'success' => true,
            'info' => [
                'title' => $title,
                'uploader' => 'Facebook User',
                'duration' => 'Unknown',
                'upload_date' => date("Y-m-d"),
                'view_count' => 'Unknown',
                'like_count' => 'Unknown',
                'thumbnail' => '',
                'sanitized_title' => $sanitized_title,
                'ext_id' => $video_id,
                'is_facebook' => true
            ]
        ];
    }
    
    // If all approaches fail
    debug_log("All Facebook approaches failed", $config);
    return [
        'success' => false,
        'message' => 'Failed to retrieve Facebook video information'
    ];
}

// Instagram-specific video info function
function get_instagram_video_info($url, $config) {
    debug_log("Getting Instagram video info for URL: $url", $config);
    $ytdlp_path = $config['ytdlp_path'];
    $escaped_url = escapeshellarg($url);
    $output_file = $config['temp_dir'] . 'info_' . md5(uniqid() . $url) . '.json';
    
    // Get a random user agent
    $user_agent = get_random_user_agent($config);
    $user_agent_arg = escapeshellarg($user_agent);
    
    // Instagram-specific command with user agent
    $cmd = "$ytdlp_path --no-check-certificate --no-warnings --ignore-errors " .
           "--user-agent $user_agent_arg " .
           "--dump-json $escaped_url > \"$output_file\" 2>&1";
    
    debug_log("Executing Instagram command: $cmd", $config);
    exec($cmd, $output, $return_var);
    debug_log("Instagram command returned: $return_var", $config);
    
    // Check if the output file exists and has valid content
    if (file_exists($output_file) && filesize($output_file) > 10) {
        $info_content = file_get_contents($output_file);
        $info = json_decode($info_content, true);
        
        if ($info) {
            debug_log("Successfully parsed Instagram video info", $config);
            @unlink($output_file);
            
            // Sanitize filename for download
            $title = $info['title'] ?? 'Instagram Video';
            $sanitized_title = preg_replace('/[^\w\s\-]/u', '', $title);
            $sanitized_title = str_replace(' ', '_', $sanitized_title);
            $sanitized_title = substr($sanitized_title, 0, 100);
            if (empty($sanitized_title)) {
                $sanitized_title = 'Instagram_Video_' . time();
            }
            
            return [
                'success' => true,
                'info' => [
                    'title' => $title,
                    'uploader' => $info['uploader'] ?? 'Instagram User',
                    'duration' => isset($info['duration']) ? gmdate("H:i:s", $info['duration']) : 'Unknown',
                    'upload_date' => isset($info['upload_date']) ? 
                        date("Y-m-d", strtotime($info['upload_date'])) : 'Unknown',
                    'view_count' => $info['view_count'] ?? 'Unknown',
                    'like_count' => $info['like_count'] ?? 'Unknown',
                    'thumbnail' => $info['thumbnail'] ?? '',
                    'sanitized_title' => $sanitized_title,
                    'ext_id' => $info['id'] ?? md5($url),
                    'is_instagram' => true
                ]
            ];
        }
    }
    
    // Try alternative approach for Instagram
    debug_log("First Instagram approach failed, trying alternative method", $config);
    $alt_cmd = "$ytdlp_path --no-check-certificate --no-warnings --ignore-errors " .
               "--user-agent \"Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1\" " .
               "-J $escaped_url > \"$output_file\" 2>&1";
    
    debug_log("Executing alternative Instagram command: $alt_cmd", $config);
    exec($alt_cmd, $alt_output, $alt_return_var);
    
    if (file_exists($output_file) && filesize($output_file) > 10) {
        $info_content = file_get_contents($output_file);
        $info = json_decode($info_content, true);
        
        if ($info) {
            debug_log("Successfully parsed Instagram video info with alternative method", $config);
            @unlink($output_file);
            
            // Sanitize filename for download
            $title = $info['title'] ?? 'Instagram Video';
            $sanitized_title = preg_replace('/[^\w\s\-]/u', '', $title);
            $sanitized_title = str_replace(' ', '_', $sanitized_title);
            $sanitized_title = substr($sanitized_title, 0, 100);
            if (empty($sanitized_title)) {
                $sanitized_title = 'Instagram_Video_' . time();
            }
            
            return [
                'success' => true,
                'info' => [
                    'title' => $title,
                    'uploader' => $info['uploader'] ?? 'Instagram User',
                    'duration' => isset($info['duration']) ? gmdate("H:i:s", $info['duration']) : 'Unknown',
                    'upload_date' => isset($info['upload_date']) ? 
                        date("Y-m-d", strtotime($info['upload_date'])) : 'Unknown',
                    'view_count' => $info['view_count'] ?? 'Unknown',
                    'like_count' => $info['like_count'] ?? 'Unknown',
                    'thumbnail' => $info['thumbnail'] ?? '',
                    'sanitized_title' => $sanitized_title,
                    'ext_id' => $info['id'] ?? md5($url),
                    'is_instagram' => true
                ]
            ];
        }
    }
    
    // Extract post ID from URL for basic info
    $post_id = '';
    if (preg_match('/\/p\/([^\/\?]+)/', $url, $matches)) {
        $post_id = $matches[1];
    } else if (preg_match('/\/reel\/([^\/\?]+)/', $url, $matches)) {
        $post_id = $matches[1];
    }
    
    if (!empty($post_id)) {
        debug_log("Extracted Instagram post ID: $post_id", $config);
        
        // Create basic info manually
        $title = "Instagram Post $post_id";
        $sanitized_title = "Instagram_Post_$post_id";
        
        return [
            'success' => true,
            'info' => [
                'title' => $title,
                'uploader' => 'Instagram User',
                'duration' => 'Unknown',
                'upload_date' => date("Y-m-d"),
                'view_count' => 'Unknown',
                'like_count' => 'Unknown',
                'thumbnail' => '',
                'sanitized_title' => $sanitized_title,
                'ext_id' => $post_id,
                'is_instagram' => true
            ]
        ];
    }
    
    // If all approaches fail
    debug_log("All Instagram approaches failed", $config);
    return [
        'success' => false,
        'message' => 'Failed to retrieve Instagram video information'
    ];
}

// Function to get a random user agent
function get_random_user_agent($config) {
    $user_agents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.107 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0',
        'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1',
        'Mozilla/5.0 (iPad; CPU OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1',
        'Mozilla/5.0 (Android 11; Mobile; rv:68.0) Gecko/68.0 Firefox/88.0',
        'Mozilla/5.0 (Android 11; Mobile; LG-M255; rv:88.0) Gecko/88.0 Firefox/88.0',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36 Edg/91.0.864.59'
    ];
    
    return $user_agents[array_rand($user_agents)];
}

// Function to download YouTube video
function download_youtube_video($url, $format_key, $config) {
    debug_log("Starting YouTube download for URL: $url, Format: $format_key", $config);
    
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
    $format_str = $format['format'];
    $audio_only = isset($format['audio_only']) && $format['audio_only'];
    $is_shorts = $info['is_shorts'] ?? false;
    
    // Create a temporary file
    $temp_file = $config['temp_dir'] . uniqid('download_') . '.' . $ext;
    debug_log("Temp file: $temp_file", $config);
    
    // Get a random user agent
    $user_agent = get_random_user_agent($config);
    $user_agent_arg = escapeshellarg($user_agent);
    
    // Build the command
    $ytdlp_path = $config['ytdlp_path'];
    $escaped_url = escapeshellarg($url);
    
    // Command to download to a temporary file
    if ($audio_only) {
        $cmd = "$ytdlp_path -f $format_str -x --audio-format $ext --audio-quality 0 " .
               "--no-check-certificate --no-warnings --ignore-errors " .
               "--extractor-args \"youtube:player_client=android\" " .
               "--user-agent $user_agent_arg " .
               "-o \"$temp_file\" $escaped_url";
    } else {
        if ($is_shorts) {
            // Special handling for YouTube Shorts
            $cmd = "$ytdlp_path -f $format_str --merge-output-format $ext " .
                   "--no-check-certificate --no-warnings --ignore-errors " .
                   "--extractor-args \"youtube:player_client=android\" " .
                   "--user-agent $user_agent_arg " .
                   "-o \"$temp_file\" $escaped_url";
        } else {
            $cmd = "$ytdlp_path -f $format_str --merge-output-format $ext " .
                   "--no-check-certificate --no-warnings --ignore-errors " .
                   "--extractor-args \"youtube:player_client=android\" " .
                   "--user-agent $user_agent_arg " .
                   "-o \"$temp_file\" $escaped_url";
        }
    }
    
    // Log the command
    debug_log("Executing YouTube download command: $cmd", $config);
    
    // Execute the command
    exec($cmd, $output, $return_var);
    
    // Log the result
    debug_log("YouTube download command returned: $return_var", $config);
    
    // Check if the file exists and has content
    if ($return_var !== 0 || !file_exists($temp_file) || filesize($temp_file) < 1000) { // File should be at least 1KB
        debug_log("YouTube download failed or file too small. Return code: $return_var", $config);
        
        // Try a simpler approach
        debug_log("Trying simpler YouTube download approach", $config);
        
        $simple_cmd = "$ytdlp_path --no-check-certificate --no-warnings --ignore-errors " .
                     "--user-agent $user_agent_arg " .
                     "-o \"$temp_file\" $escaped_url";
        
        debug_log("Executing simple YouTube download command: $simple_cmd", $config);
        exec($simple_cmd, $simple_output, $simple_return_var);
        debug_log("Simple YouTube download command returned: $simple_return_var", $config);
        
        if ($simple_return_var !== 0 || !file_exists($temp_file) || filesize($temp_file) < 1000) {
            debug_log("Simple YouTube download approach failed. Return code: $simple_return_var", $config);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Failed to download YouTube video'
            ]);
            
            // Clean up
            if (file_exists($temp_file)) {
                unlink($temp_file);
            }
            
            exit;
        }
    }
    
    // File exists and has content, prepare for download
    debug_log("YouTube download successful, preparing to serve file: " . filesize($temp_file) . " bytes", $config);
    
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
    exit;
}

// Function to download Facebook video
function download_facebook_video($url, $format_key, $config) {
    debug_log("Starting Facebook download for URL: $url, Format: $format_key", $config);
    
    // Get video info to get the title
    $video_info = get_facebook_video_info($url, $config);
    
    if (!$video_info['success']) {
        debug_log("Failed to get Facebook video info for download", $config);
        header('HTTP/1.0 400 Bad Request');
        echo json_encode([
            'success' => false,
            'message' => 'Failed to get Facebook video information'
        ]);
        exit;
    }
    
    $info = $video_info['info'];
    $title = $info['sanitized_title'];
    $format = $config['format_options'][$format_key];
    $ext = $format['ext'];
    $format_str = $format['format'];
    $audio_only = isset($format['audio_only']) && $format['audio_only'];
    
    // Create a temporary file
    $temp_file = $config['temp_dir'] . uniqid('download_') . '.' . $ext;
    debug_log("Temp file: $temp_file", $config);
    
    // Get a random user agent
    $user_agent = get_random_user_agent($config);
    $user_agent_arg = escapeshellarg($user_agent);
    
    // Build the command
    $ytdlp_path = $config['ytdlp_path'];
    $escaped_url = escapeshellarg($url);
    
    // Command to download to a temporary file
    if ($audio_only) {
        $cmd = "$ytdlp_path -f $format_str -x --audio-format $ext --audio-quality 0 " .
               "--no-check-certificate --no-warnings --ignore-errors " .
               "--user-agent $user_agent_arg " .
               "-o \"$temp_file\" $escaped_url";
    } else {
        $cmd = "$ytdlp_path -f $format_str --merge-output-format $ext " .
               "--no-check-certificate --no-warnings --ignore-errors " .
               "--user-agent $user_agent_arg " .
               "-o \"$temp_file\" $escaped_url";
    }
    
    // Log the command
    debug_log("Executing Facebook download command: $cmd", $config);
    
    // Execute the command
    exec($cmd, $output, $return_var);
    
    // Log the result
    debug_log("Facebook download command returned: $return_var", $config);
    
    // Check if the file exists and has content
    if ($return_var !== 0 || !file_exists($temp_file) || filesize($temp_file) < 1000) { // File should be at least 1KB
        debug_log("Facebook download failed or file too small. Return code: $return_var", $config);
        
        // Try a simpler approach
        debug_log("Trying simpler Facebook download approach", $config);
        
        $simple_cmd = "$ytdlp_path --no-check-certificate --no-warnings --ignore-errors " .
                     "--user-agent \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36\" " .
                     "-o \"$temp_file\" $escaped_url";
        
        debug_log("Executing simple Facebook download command: $simple_cmd", $config);
        exec($simple_cmd, $simple_output, $simple_return_var);
        debug_log("Simple Facebook download command returned: $simple_return_var", $config);
        
        if ($simple_return_var !== 0 || !file_exists($temp_file) || filesize($temp_file) < 1000) {
            debug_log("Simple Facebook download approach failed. Return code: $simple_return_var", $config);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Failed to download Facebook video'
            ]);
            
            // Clean up
            if (file_exists($temp_file)) {
                unlink($temp_file);
            }
            
            exit;
        }
    }
    
    // File exists and has content, prepare for download
    debug_log("Facebook download successful, preparing to serve file: " . filesize($temp_file) . " bytes", $config);
    
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
    exit;
}

// Function to download Instagram video
function download_instagram_video($url, $format_key, $config) {
    debug_log("Starting Instagram download for URL: $url, Format: $format_key", $config);
    
    // Get video info to get the title
    $video_info = get_instagram_video_info($url, $config);
    
    if (!$video_info['success']) {
        debug_log("Failed to get Instagram video info for download", $config);
        header('HTTP/1.0 400 Bad Request');
        echo json_encode([
            'success' => false,
            'message' => 'Failed to get Instagram video information'
        ]);
        exit;
    }
    
    $info = $video_info['info'];
    $title = $info['sanitized_title'];
    $format = $config['format_options'][$format_key];
    $ext = $format['ext'];
    $format_str = $format['format'];
    $audio_only = isset($format['audio_only']) && $format['audio_only'];
    
    // Create a temporary file
    $temp_file = $config['temp_dir'] . uniqid('download_') . '.' . $ext;
    debug_log("Temp file: $temp_file", $config);
    
    // Get a random user agent
    $user_agent = get_random_user_agent($config);
    $user_agent_arg = escapeshellarg($user_agent);
    
    // Build the command
    $ytdlp_path = $config['ytdlp_path'];
    $escaped_url = escapeshellarg($url);
    
    // Command to download to a temporary file
    if ($audio_only) {
        $cmd = "$ytdlp_path -f $format_str -x --audio-format $ext --audio-quality 0 " .
               "--no-check-certificate --no-warnings --ignore-errors " .
               "--user-agent $user_agent_arg " .
               "-o \"$temp_file\" $escaped_url";
    } else {
        $cmd = "$ytdlp_path -f $format_str --merge-output-format $ext " .
               "--no-check-certificate --no-warnings --ignore-errors " .
               "--user-agent $user_agent_arg " .
               "-o \"$temp_file\" $escaped_url";
    }
    
    // Log the command
    debug_log("Executing Instagram download command: $cmd", $config);
    
    // Execute the command
    exec($cmd, $output, $return_var);
    
    // Log the result
    debug_log("Instagram download command returned: $return_var", $config);
    
    // Check if the file exists and has content
    if ($return_var !== 0 || !file_exists($temp_file) || filesize($temp_file) < 1000) { // File should be at least 1KB
        debug_log("Instagram download failed or file too small. Return code: $return_var", $config);
        
        // Try a simpler approach
        debug_log("Trying simpler Instagram download approach", $config);
        
        $simple_cmd = "$ytdlp_path --no-check-certificate --no-warnings --ignore-errors " .
                     "--user-agent \"Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1\" " .
                     "-o \"$temp_file\" $escaped_url";
        
        debug_log("Executing simple Instagram download command: $simple_cmd", $config);
        exec($simple_cmd, $simple_output, $simple_return_var);
        debug_log("Simple Instagram download command returned: $simple_return_var", $config);
        
        if ($simple_return_var !== 0 || !file_exists($temp_file) || filesize($temp_file) < 1000) {
            debug_log("Simple Instagram download approach failed. Return code: $simple_return_var", $config);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Failed to download Instagram video'
            ]);
            
            // Clean up
            if (file_exists($temp_file)) {
                unlink($temp_file);
            }
            
            exit;
        }
    }
    
    // File exists and has content, prepare for download
    debug_log("Instagram download successful, preparing to serve file: " . filesize($temp_file) . " bytes", $config);
    
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
    exit;
}
?>
