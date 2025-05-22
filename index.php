<?php

// Include error handler first
require_once('error-handler.php');

// Include directory helper
require_once('directory-helper.php');

// Include platform-specific functions
require_once('platform-specific.php');

// Include YouTube bypass functions
require_once('youtube-direct-bypass.php');

// Include proxy configuration
require_once('proxy-config.php');

// Include the updated download fix
require_once('fix-download-updated-v2.php');

// Set cache directory for yt-dlp
$cache_dir = '/tmp/yt-dlp-cache/';
if (!file_exists($cache_dir)) {
    safe_mkdir($cache_dir);
}
putenv("XDG_CACHE_HOME={$cache_dir}");


// Create cache directory with proper permissions
$cache_dir = '/tmp/yt-dlp-cache/';
if (!file_exists($cache_dir)) {
    mkdir($cache_dir, 0777, true);
}
putenv("XDG_CACHE_HOME={$cache_dir}");
// Error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session for tracking downloads
session_start();

// Configuration
$config = [
    'temp_dir' => 'temp/',
    'log_dir' => 'logs/',
    'ytdlp_path' => 'yt-dlp', // Use system path
    'ffmpeg_path' => 'ffmpeg', // Use system path
    'max_execution_time' => 600, // 10 minutes
    'download_timeout' => 180, // 3 minutes
    'rate_limit' => [
        'enabled' => true,
        'max_per_ip' => 50,
        'time_window' => 3600, // 1 hour
    ],
    'supported_sites' => [
        'youtube.com', 'youtu.be',
        'facebook.com', 'fb.watch',
        'instagram.com',
        'tiktok.com',
        'twitter.com', 'x.com',
        'vimeo.com',
        'dailymotion.com',
        'reddit.com',
    ],
    // Simplified format options
    'format_options' => [
        'best' => [
            'label' => 'Best Quality (MP4)',
            'format' => 'bestvideo[ext=mp4]+bestaudio[ext=m4a]/best[ext=mp4]/best',
            'ext' => 'mp4'
        ],
        'medium' => [
            'label' => 'Medium Quality (MP4 720p)',
            'format' => 'bestvideo[height<=720][ext=mp4]+bestaudio[ext=m4a]/best[height<=720][ext=mp4]/best[height<=720]',
            'ext' => 'mp4'
        ],
        'low' => [
            'label' => 'Low Quality (MP4 480p)',
            'format' => 'bestvideo[height<=480][ext=mp4]+bestaudio[ext=m4a]/best[height<=480][ext=mp4]/best[height<=480]',
            'ext' => 'mp4'
        ],
        'audio' => [
            'label' => 'Audio Only (MP3)',
            'format' => 'bestaudio/best',
            'ext' => 'mp3',
            'audio_only' => true
        ]
    ],
    // Debug mode
    'debug' => true
];

// Set max execution time
ini_set('max_execution_time', $config['max_execution_time']);

// Create necessary directories
foreach ([$config['temp_dir'], $config['log_dir'], '/tmp/yt-dlp-cache'] as $dir) {
    ensure_writable_dir($dir);
}

// Debug log function
function debug_log($message, $config) {
    if ($config['debug']) {
        $log_file = $config['log_dir'] . 'debug_log.txt';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
    }
}

// Function to check if yt-dlp and ffmpeg are installed
function check_dependencies($config) {
    debug_log("Checking dependencies", $config);
    $ytdlp_path = $config['ytdlp_path'];
    $ffmpeg_path = $config['ffmpeg_path'];
    
    // Check yt-dlp
    exec($ytdlp_path . ' --version 2>&1', $ytdlp_output, $ytdlp_return);
    debug_log("yt-dlp check result: $ytdlp_return", $config);
    
    // Check ffmpeg
    exec($ffmpeg_path . ' -version 2>&1', $ffmpeg_output, $ffmpeg_return);
    debug_log("ffmpeg check result: $ffmpeg_return", $config);
    
    $result = [
        'ytdlp' => [
            'installed' => ($ytdlp_return === 0),
            'version' => ($ytdlp_return === 0) ? $ytdlp_output[0] : 'Not installed'
        ],
        'ffmpeg' => [
            'installed' => ($ffmpeg_return === 0),
            'version' => ($ffmpeg_return === 0) ? (isset($ffmpeg_output[0]) ? $ffmpeg_output[0] : 'Installed') : 'Not installed'
        ]
    ];
    
    return $result;
}

// Function to sanitize URL
function sanitize_url($url) {
    return filter_var($url, FILTER_SANITIZE_URL);
}

// Function to validate URL
function validate_url($url, $config) {
    if (empty($url)) {
        return false;
    }
    
    $url = filter_var($url, FILTER_VALIDATE_URL);
    if (!$url) {
        return false;
    }
    
    $host = parse_url($url, PHP_URL_HOST);
    if (!$host) {
        return false;
    }
    
    // Check if the domain is in our supported sites list
    foreach ($config['supported_sites'] as $site) {
        if (strpos($host, $site) !== false) {
            return true;
        }
    }
    
    return false;
}

// Function to check rate limit
function check_rate_limit($config) {
    if (!$config['rate_limit']['enabled']) {
        return true;
    }
    
    $ip = $_SERVER['REMOTE_ADDR'];
    $current_time = time();
    $log_file = $config['log_dir'] . 'rate_limit.json';
    
    // Initialize or load rate limit data
    if (file_exists($log_file)) {
        $rate_data = json_decode(file_get_contents($log_file), true);
    } else {
        $rate_data = [];
    }
    
    // Clean up old entries
    foreach ($rate_data as $check_ip => $times) {
        foreach ($times as $key => $time) {
            if ($current_time - $time > $config['rate_limit']['time_window']) {
                unset($rate_data[$check_ip][$key]);
            }
        }
        if (empty($rate_data[$check_ip])) {
            unset($rate_data[$check_ip]);
        }
    }
    
    // Check current IP
    if (!isset($rate_data[$ip])) {
        $rate_data[$ip] = [$current_time];
    } else {
        // Check if limit exceeded
        if (count($rate_data[$ip]) >= $config['rate_limit']['max_per_ip']) {
            return false;
        }
        $rate_data[$ip][] = $current_time;
    }
    
    // Save updated rate data
    file_put_contents($log_file, json_encode($rate_data));
    return true;
}

// Function to get video info
function get_video_info($url, $config) {
    // Determine which platform the URL is from
    $is_youtube = (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false);
    $is_facebook = (strpos($url, 'facebook.com') !== false || strpos($url, 'fb.watch') !== false);
    $is_instagram = (strpos($url, 'instagram.com') !== false);
    
    // Use platform-specific handlers
    if ($is_youtube) {
        return get_youtube_video_info($url, $config);
    } else if ($is_facebook) {
        return get_facebook_video_info($url, $config);
    } else if ($is_instagram) {
        return get_instagram_video_info($url, $config);
    }
    
    debug_log("Getting video info for URL: $url", $config);
    $ytdlp_path = $config['ytdlp_path'];
    $url = escapeshellarg($url);
    $output_file = $config['temp_dir'] . 'info_' . md5(uniqid() . $url) . '.json';
    
    // Check if this is a YouTube Shorts URL
    $is_youtube_shorts = (strpos($url, 'youtube.com/shorts') !== false || strpos($url, 'youtu.be/') !== false);
    
    // Get basic info
    if ($is_youtube_shorts) {
        $cmd = $ytdlp_path . ' -J --no-check-certificate --no-cache-dir --extractor-args "youtube:player_client=web" ' . $url . ' > "' . $output_file . '" 2>&1';
    } else {
        $cmd = $ytdlp_path . ' -J --no-check-certificate --no-cache-dir ' . $url . ' > "' . $output_file . '" 2>&1';
    }
    
    debug_log("Executing command: $cmd", $config);
    exec($cmd, $output, $return_var);
    debug_log("Command returned: $return_var", $config);
    
    if ($return_var !== 0 || !file_exists($output_file)) {
        debug_log("Failed to get video info. Output: " . implode("\n", $output), $config);
        return [
            'success' => false,
            'message' => 'Failed to retrieve video information'
        ];
    }
    
    $info_content = file_get_contents($output_file);
    debug_log("Info file size: " . strlen($info_content) . " bytes", $config);
    $info = json_decode($info_content, true);
    unlink($output_file);
    
    if (!$info) {
        debug_log("Failed to parse video info JSON", $config);
        return [
            'success' => false,
            'message' => 'Failed to parse video information'
        ];
    }
    
    // Sanitize filename for download
    $title = $info['title'] ?? 'video';
    $sanitized_title = preg_replace('/[^\w\s\-]/u', '', $title);
    $sanitized_title = str_replace(' ', '_', $sanitized_title);
    $sanitized_title = substr($sanitized_title, 0, 100);
    if (empty($sanitized_title)) {
        $sanitized_title = 'video_' . time();
    }
    
    debug_log("Video info retrieved successfully", $config);
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
            'is_shorts' => $is_youtube_shorts
        ]
    ];
}

// Function to download video directly (no iframe)
function download_video($url, $format_key, $config) {
    debug_log("Starting direct download for URL: $url, Format: $format_key", $config);

    // Determine which platform the URL is from
    $is_youtube = (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false);
    $is_facebook = (strpos($url, 'facebook.com') !== false || strpos($url, 'fb.watch') !== false);
    $is_instagram = (strpos($url, 'instagram.com') !== false);
    
    // Use platform-specific handlers
    if ($is_youtube) {
        return download_youtube_video($url, $format_key, $config);
    } else if ($is_facebook) {
        return download_facebook_video($url, $format_key, $config);
    } else if ($is_instagram) {
        return download_instagram_video($url, $format_key, $config);
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
    $format_str = $format['format'];
    $audio_only = isset($format['audio_only']) && $format['audio_only'];
    $is_shorts = $info['is_shorts'] ?? false;
    
    // Create a temporary file
    $temp_file = $config['temp_dir'] . uniqid('download_') . '.' . $ext;
    debug_log("Temp file: $temp_file", $config);
    
    // Build the command
    $ytdlp_path = $config['ytdlp_path'];
    $url = escapeshellarg($url);
    
    // Command to download to a temporary file
    if ($audio_only) {
        $cmd = $ytdlp_path . ' -f ' . escapeshellarg($format_str) . ' -x --audio-format ' . $ext . ' --audio-quality 0 --no-check-certificate --no-cache-dir -o "' . $temp_file . '" ' . $url;
    } else {
        if ($is_shorts) {
            // Special handling for YouTube Shorts
            $cmd = $ytdlp_path . ' -f ' . escapeshellarg($format_str) . ' --merge-output-format ' . $ext . ' --no-check-certificate --no-cache-dir --extractor-args "youtube:player_client=web" -o "' . $temp_file . '" ' . $url;
        } else {
            $cmd = $ytdlp_path . ' -f ' . escapeshellarg($format_str) . ' --merge-output-format ' . $ext . ' --no-check-certificate --no-cache-dir -o "' . $temp_file . '" ' . $url;
        }
    }
    
    // Log the command
    debug_log("Executing download command: $cmd", $config);
    
    // Set a timeout for the command execution
    $descriptorspec = [
        0 => ["pipe", "r"],  // stdin
        1 => ["pipe", "w"],  // stdout
        2 => ["pipe", "w"]   // stderr
    ];
    
    $process = proc_open($cmd, $descriptorspec, $pipes);
    
    if (is_resource($process)) {
        // Set pipes to non-blocking mode
        stream_set_blocking($pipes[1], 0);
        stream_set_blocking($pipes[2], 0);
        
        $output = '';
        $error = '';
        $start_time = time();
        
        // Check process status with timeout
        while (true) {
            $status = proc_get_status($process);
            
            // Read from stdout and stderr
            $output .= stream_get_contents($pipes[1]);
            $error .= stream_get_contents($pipes[2]);
            
            // Check if process has ended
            if (!$status['running']) {
                break;
            }
            
            // Check for timeout
            if (time() - $start_time > $config['download_timeout']) {
                debug_log("Download process timed out after {$config['download_timeout']} seconds", $config);
                proc_terminate($process);
                
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Download process timed out. Try a different format or video.'
                ]);
                exit;
            }
            
            // Sleep briefly to prevent CPU hogging
            usleep(100000); // 0.1 seconds
        }
        
        // Close pipes
        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        
        // Close process
        $return_var = proc_close($process);
    } else {
        debug_log("Failed to start download process", $config);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Failed to start download process'
        ]);
        exit;
    }
    
    debug_log("Download command returned: $return_var", $config);
    debug_log("Command output: $output", $config);
    debug_log("Command error: $error", $config);
    
    if ($return_var !== 0 || !file_exists($temp_file) || filesize($temp_file) < 1000) { // File should be at least 1KB
        debug_log("Download failed or file too small", $config);
        header('Content-Type: application/json');
        
        $error_message = 'Failed to download video';
        
        // Check for common errors
        if (strpos($error, 'This video is unavailable') !== false) {
            $error_message = 'This video is unavailable or restricted';
        } else if (strpos($error, 'Sign in to confirm your age') !== false) {
            $error_message = 'Age-restricted video requires sign-in';
        } else if (strpos($error, 'ERROR: Unable to extract') !== false) {
            $error_message = 'Unable to extract video data. Format may not be supported';
        }
        
        echo json_encode([
            'success' => false,
            'message' => $error_message
        ]);
        
        // Clean up
        if (file_exists($temp_file)) {
            unlink($temp_file);
        }
        
        exit;
    }
    
    // File exists and has content, prepare for download
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
    exit;
}

// Function to serve a downloaded file
function serve_file($file_path, $file_name, $config) {
    debug_log("Serving file: $file_path as $file_name", $config);
    
    if (!file_exists($file_path)) {
        debug_log("File not found: $file_path", $config);
        header('HTTP/1.0 404 Not Found');
        echo 'File not found';
        exit;
    }
    
    // Set headers for download
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $file_name . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file_path));
    
    // Clear output buffer to ensure headers are sent
    ob_clean();
    flush();
    
    // Read the file and output it to the browser
    readfile($file_path);
    
    // Delete the temporary file
    unlink($file_path);
    exit;
}

// Handle download request
if (isset($_GET['download']) && isset($_GET['url']) && isset($_GET['format'])) {
    $url = sanitize_url($_GET['url']);
    $format = $_GET['format'];
    
    debug_log("Download request received for URL: $url, Format: $format", $config);
    
    if (!validate_url($url, $config)) {
        debug_log("Invalid URL: $url", $config);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Invalid or unsupported URL'
        ]);
        exit;
    }
    
    if (!isset($config['format_options'][$format])) {
        debug_log("Invalid format: $format", $config);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Invalid format'
        ]);
        exit;
    }
    
    if (!check_rate_limit($config)) {
        debug_log("Rate limit exceeded for IP: " . $_SERVER['REMOTE_ADDR'], $config);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Rate limit exceeded. Please try again later.'
        ]);
        exit;
    }
    
    // Download the video
    // download_video($url, $format, $config);
    fix_download_process($url, $format, $config);
    exit;
}

// Handle file serving request
if (isset($_GET['serve']) && isset($_GET['file']) && isset($_GET['name'])) {
    $file_path = $_GET['file'];
    $file_name = $_GET['name'];
    
    // Security check - make sure the file is in our temp directory
    if (strpos($file_path, $config['temp_dir']) !== 0) {
        debug_log("Security violation: Attempted to access file outside temp directory", $config);
        header('HTTP/1.0 403 Forbidden');
        echo 'Access denied';
        exit;
    }
    
    serve_file($file_path, $file_name, $config);
    exit;
}

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'check_dependencies':
            echo json_encode(check_dependencies($config));
            break;
            
        case 'get_info':
            if (!isset($_POST['url'])) {
                echo json_encode(['success' => false, 'message' => 'URL is required']);
                break;
            }
            
            $url = sanitize_url($_POST['url']);
            
            if (!validate_url($url, $config)) {
                echo json_encode(['success' => false, 'message' => 'Invalid or unsupported URL']);
                break;
            }
            
            if (!check_rate_limit($config)) {
                echo json_encode(['success' => false, 'message' => 'Rate limit exceeded. Please try again later.']);
                break;
            }
            
            $info = get_video_info($url, $config);
            $info['formats'] = $config['format_options'];
            echo json_encode($info);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
    exit;
}

// Check dependencies
$dependencies = check_dependencies($config);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Direct Video Downloader</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --success-color: #4cc9f0;
            --danger-color: #f72585;
            --warning-color: #f8961e;
            --info-color: #4895ef;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --gray-color: #6c757d;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--dark-color);
            background-color: #f0f2f5;
            padding: 0;
            margin: 0;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            background-color: var(--primary-color);
            color: white;
            padding: 1rem 0;
            text-align: center;
            margin-bottom: 2rem;
            box-shadow: var(--box-shadow);
        }

        header h1 {
            margin: 0;
            font-size: 2.5rem;
        }

        .card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 1.5rem;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            padding: 1rem;
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            font-weight: bold;
        }

        .card-body {
            padding: 1.5rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        input[type="text"], select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ced4da;
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        input[type="text"]:focus, select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
        }

        .btn {
            display: inline-block;
            font-weight: 500;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            user-select: none;
            border: 1px solid transparent;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: var(--border-radius);
            transition: var(--transition);
            cursor: pointer;
        }

        .btn-primary {
            color: #fff;
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .btn-success {
            color: #fff;
            background-color: var(--success-color);
            border-color: var(--success-color);
        }

        .btn-success:hover {
            background-color: #3aa0c1;
            border-color: #3aa0c1;
        }

        .btn-block {
            display: block;
            width: 100%;
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
            border-radius: var(--border-radius);
        }

        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        .alert-warning {
            color: #856404;
            background-color: #fff3cd;
            border-color: #ffeeba;
        }

        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        .alert-info {
            color: #0c5460;
            background-color: #d1ecf1;
            border-color: #bee5eb;
        }

        .hidden {
            display: none !important;
        }

        .video-info {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
        }

        .video-thumbnail {
            flex: 0 0 200px;
            margin-right: 1.5rem;
            margin-bottom: 1rem;
        }

        .video-thumbnail img {
            max-width: 100%;
            height: auto;
            border-radius: var(--border-radius);
        }

        .video-details {
            flex: 1;
            min-width: 300px;
        }

        .video-title {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }

        .video-meta {
            margin-bottom: 1rem;
            color: var(--gray-color);
        }

        .video-meta span {
            margin-right: 1rem;
        }

        .format-options {
            margin-top: 1.5rem;
        }

        .format-list {
            list-style: none;
            padding: 0;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
        }

        .format-item {
            padding: 1rem;
            border: 1px solid #dee2e6;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease;
            text-align: center;
        }

        .format-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            background-color: #f0f7ff;
        }

        .format-item:active {
            transform: translateY(-1px);
        }

        .format-item.selected {
            background-color: #e8f4ff;
            border-color: var(--primary-color);
        }

        .format-item-header {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .format-item-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }

        .spinner {
            display: inline-block;
            width: 1.5rem;
            height: 1.5rem;
            border: 0.25rem solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s linear infinite;
            margin-right: 0.5rem;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .footer {
            text-align: center;
            margin-top: 2rem;
            padding: 1rem;
            color: var(--gray-color);
            font-size: 0.9rem;
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.7);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            color: white;
            backdrop-filter: blur(3px);
            transition: opacity 0.3s ease;
        }

        .loading-spinner {
            width: 5rem;
            height: 5rem;
            border: 0.5rem solid rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s linear infinite;
            margin-bottom: 1.5rem;
        }

        .loading-text {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }

        .loading-subtext {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .url-input-container {
            position: relative;
        }

        .url-input-spinner {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            width: 1.5rem;
            height: 1.5rem;
            border: 0.25rem solid rgba(67, 97, 238, 0.3);
            border-radius: 50%;
            border-top-color: var(--primary-color);
            animation: spin 1s linear infinite;
        }

        .format-item.downloading {
            pointer-events: none;
            opacity: 0.7;
        }

        .format-item .download-indicator {
            display: none;
        }

        .format-item.downloading .download-indicator {
            display: inline-block;
            width: 1rem;
            height: 1rem;
            border: 0.2rem solid rgba(67, 97, 238, 0.3);
            border-radius: 50%;
            border-top-color: var(--primary-color);
            animation: spin 1s linear infinite;
            margin-left: 0.5rem;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .video-info {
                flex-direction: column;
            }
            
            .video-thumbnail {
                margin-right: 0;
                margin-bottom: 1rem;
                flex: 0 0 100%;
            }
            
            .format-list {
                grid-template-columns: 1fr;
            }
        }

        .notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: var(--dark-color);
            color: white;
            padding: 12px 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            z-index: 1001;
            transform: translateY(100px);
            opacity: 0;
            transition: transform 0.3s ease, opacity 0.3s ease;
            max-width: 350px;
        }

        .notification.show {
            transform: translateY(0);
            opacity: 1;
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
            100% {
                transform: scale(1);
            }
        }

        /* Progress bar animation for loading overlay */
        .progress-bar-container {
            width: 300px;
            height: 4px;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 2px;
            margin-top: 20px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            width: 0;
            background-color: white;
            animation: progress 30s linear forwards;
        }

        @keyframes progress {
            0% { width: 0; }
            20% { width: 20%; }
            50% { width: 50%; }
            80% { width: 80%; }
            100% { width: 95%; }
        }

        /* Debug panel */
        #debug-panel {
            position: fixed;
            bottom: 10px;
            left: 10px;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 10px;
            border-radius: 5px;
            max-width: 80%;
            max-height: 200px;
            overflow: auto;
            z-index: 1000;
        }

        /* Download status */
        .download-status {
            margin-top: 20px;
            padding: 15px;
            border-radius: var(--border-radius);
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }

        .download-status h4 {
            margin-top: 0;
            margin-bottom: 10px;
        }

        .download-status-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .download-status-item:last-child {
            border-bottom: none;
        }

        .download-status-name {
            font-weight: 500;
        }

        .download-status-progress {
            color: var(--primary-color);
        }

        /* YouTube Shorts badge */
        .shorts-badge {
            display: inline-block;
            background-color: var(--danger-color);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            margin-left: 8px;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1><i class="fas fa-download"></i> Direct Video Downloader</h1>
        </div>
    </header>

    <div class="container">
        <?php if (!$dependencies['ytdlp']['installed'] || !$dependencies['ffmpeg']['installed']): ?>
            <div class="alert alert-danger">
                <h3><i class="fas fa-exclamation-triangle"></i> Missing Dependencies</h3>
                <p>The following dependencies are required but not installed:</p>
                <ul>
                    <?php if (!$dependencies['ytdlp']['installed']): ?>
                        <li><strong>yt-dlp:</strong> Not found. Please install yt-dlp and update the path in the configuration.</li>
                    <?php endif; ?>
                    <?php if (!$dependencies['ffmpeg']['installed']): ?>
                        <li><strong>FFmpeg:</strong> Not found. Please install FFmpeg and update the path in the configuration.</li>
                    <?php endif; ?>
                </ul>
                <p>Please install the missing dependencies and update the paths in the configuration at the top of index.php.</p>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <p><strong>yt-dlp version:</strong> <?php echo $dependencies['ytdlp']['version']; ?></p>
                <p><strong>FFmpeg:</strong> Installed</p>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <i class="fas fa-link"></i> Enter Video URL
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="video-url">Video URL (YouTube, Facebook, TikTok, etc.)</label>
                    <div class="url-input-container">
                        <input type="text" id="video-url" placeholder="https://www.youtube.com/watch?v=..." autocomplete="off">
                        <div id="url-input-spinner" class="url-input-spinner hidden"></div>
                    </div>
                </div>
            </div>
        </div>

        <div id="error-container" class="alert alert-danger hidden"></div>
        <div id="success-container" class="alert alert-success hidden"></div>

        <div id="video-info-container" class="card hidden">
            <div class="card-header">
                <i class="fas fa-film"></i> Video Information
            </div>
            <div class="card-body">
                <div id="video-info" class="video-info">
                    <!-- Video info will be populated here -->
                </div>

                <div class="format-options">
                    <h3>Download Options</h3>
                    <ul id="format-list" class="format-list">
                        <!-- Format options will be populated here -->
                    </ul>
                </div>
            </div>
        </div>

        <div id="download-status-container" class="download-status hidden">
            <h4>Download Status</h4>
            <div id="download-status-content">
                <!-- Download status will be populated here -->
            </div>
        </div>

        <div id="loading-overlay" class="loading-overlay hidden">
            <div class="loading-spinner"></div>
            <div class="loading-text">Preparing your download...</div>
            <div class="loading-subtext">This may take a moment. Please don't close this page.</div>
            <div class="progress-bar-container">
                <div class="progress-bar"></div>
            </div>
        </div>

        <div class="footer">
            <p>Direct Video Downloader | Supports YouTube, Facebook, TikTok, Instagram, Twitter, and more</p>
            <p><small>Note: This tool is for personal use only. Please respect copyright laws and terms of service for all platforms.</small></p>
        </div>
    </div>

    <div id="debug-panel" class="hidden">
        <h4>Debug Information</h4>
        <pre id="debug-content"></pre>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Elements
            const videoUrlInput = document.getElementById('video-url');
            const urlInputSpinner = document.getElementById('url-input-spinner');
            const errorContainer = document.getElementById('error-container');
            const successContainer = document.getElementById('success-container');
            const videoInfoContainer = document.getElementById('video-info-container');
            const videoInfoElement = document.getElementById('video-info');
            const formatListElement = document.getElementById('format-list');
            const loadingOverlay = document.getElementById('loading-overlay');
            const downloadStatusContainer = document.getElementById('download-status-container');
            const downloadStatusContent = document.getElementById('download-status-content');
            
            // Variables
            let videoInfo = null;
            let currentUrl = '';
            let typingTimer;
            const doneTypingInterval = 800; // 0.8 seconds
            let activeDownloads = 0;
            let downloadTimeouts = {};
            
            // Make sure loading overlay is hidden on page load
            loadingOverlay.classList.add('hidden');
            
            // Update loading overlay text function
            function updateLoadingText(text, subtext) {
                document.querySelector('.loading-text').textContent = text || 'Processing...';
                document.querySelector('.loading-subtext').textContent = subtext || 'This may take a moment. Please don\'t close this page.';
            }
            
            // Event listeners
            videoUrlInput.addEventListener('keyup', function() {
                clearTimeout(typingTimer);
                if (videoUrlInput.value) {
                    typingTimer = setTimeout(doneTyping, doneTypingInterval);
                }
            });
            
            videoUrlInput.addEventListener('keydown', function() {
                clearTimeout(typingTimer);
            });
            
            // Functions
            function doneTyping() {
                const url = videoUrlInput.value.trim();
                if (url && url !== currentUrl) {
                    currentUrl = url;
                    getVideoInfo(url);
                }
            }
            
            function showError(message) {
                errorContainer.textContent = message;
                errorContainer.classList.remove('hidden');
                
                // Auto-hide error after 5 seconds
                setTimeout(() => {
                    errorContainer.classList.add('hidden');
                }, 5000);
            }
            
            function showSuccess(message) {
                successContainer.textContent = message;
                successContainer.classList.remove('hidden');
                
                // Auto-hide success after 5 seconds
                setTimeout(() => {
                    successContainer.classList.add('hidden');
                }, 5000);
            }
            
            function hideError() {
                errorContainer.classList.add('hidden');
            }
            
            function getVideoInfo(url) {
                hideError();
                videoInfoContainer.classList.add('hidden');
                
                // Show full-screen loading overlay for fetching video info
                updateLoadingText('Fetching video information...', 'Please wait while we analyze the video.');
                loadingOverlay.classList.remove('hidden');
                
                fetch('?action=get_info', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `url=${encodeURIComponent(url)}`
                })
                .then(response => response.json())
                .then(data => {
                    // Hide loading overlay
                    loadingOverlay.classList.add('hidden');
                    
                    if (!data.success) {
                        showError(data.message || 'Failed to get video information');
                        return;
                    }
                    
                    videoInfo = data;
                    displayVideoInfo(data);
                })
                .catch(error => {
                    // Hide loading overlay
                    loadingOverlay.classList.add('hidden');
                    showError('Error: ' + error.message);
                });
            }
            
            function displayVideoInfo(data) {
                const info = data.info;
                const isShorts = info.is_shorts || false;
                
                // Display video info
                videoInfoElement.innerHTML = `
                    <div class="video-thumbnail">
                        <img src="${info.thumbnail || '/placeholder.svg?height=200&width=300'}" alt="Thumbnail" onerror="this.src='/placeholder.svg?height=200&width=300'">
                    </div>
                    <div class="video-details">
                        <div class="video-title">
                            ${info.title}
                            ${isShorts ? '<span class="shorts-badge">Shorts</span>' : ''}
                        </div>
                        <div class="video-meta">
                            <span><i class="fas fa-user"></i> ${info.uploader}</span>
                            <span><i class="fas fa-clock"></i> ${info.duration}</span>
                        </div>
                        <div class="video-meta">
                            <span><i class="fas fa-eye"></i> ${formatNumber(info.view_count)} views</span>
                            <span><i class="fas fa-thumbs-up"></i> ${formatNumber(info.like_count)} likes</span>
                        </div>
                        ${isShorts ? '<div class="alert alert-info" style="margin-top: 10px;"><i class="fas fa-info-circle"></i> YouTube Shorts may take longer to download.</div>' : ''}
                    </div>
                `;
                
                // Display format options
                formatListElement.innerHTML = '';
                
                // Add video formats
                const videoFormats = [
                    { key: 'best', icon: 'fas fa-film', description: 'Best quality video with audio' },
                    { key: 'medium', icon: 'fas fa-video', description: '720p video with audio' },
                    { key: 'low', icon: 'fas fa-compress', description: '480p video with audio' },
                    { key: 'audio', icon: 'fas fa-music', description: 'Audio only (MP3)' }
                ];
                
                videoFormats.forEach(format => {
                    const formatOption = data.formats[format.key];
                    if (formatOption) {
                        const li = document.createElement('li');
                        li.className = 'format-item';
                        li.setAttribute('data-format', format.key);
                        li.innerHTML = `
                            <div class="format-item-icon">
                                <i class="${format.icon}"></i>
                            </div>
                            <div class="format-item-header">
                                ${formatOption.label}
                                <span class="download-indicator"></span>
                            </div>
                            <div class="format-item-details">${format.description}</div>
                        `;
                        li.addEventListener('click', function() {
                            if (!li.classList.contains('downloading')) {
                                startDownload(currentUrl, format.key, li);
                            }
                        });
                        formatListElement.appendChild(li);
                    }
                });
                
                // Show video info container with animation
                videoInfoContainer.style.opacity = '0';
                videoInfoContainer.classList.remove('hidden');
                setTimeout(() => {
                    videoInfoContainer.style.opacity = '1';
                }, 10);
            }
            
            function startDownload(url, format, formatElement) {
                // Mark this format as downloading
                formatElement.classList.add('downloading');
                activeDownloads++;
                
                // Generate a unique ID for this download
                const downloadId = Date.now();
                
                // Show loading overlay with download message
                updateLoadingText('Preparing your download...', 'This may take a moment. Please don\'t close this page.');
                loadingOverlay.classList.remove('hidden');
                
                // Reset progress bar animation
                const progressBar = document.querySelector('.progress-bar');
                progressBar.style.animation = 'none';
                setTimeout(() => {
                    progressBar.style.animation = 'progress 120s linear forwards';
                }, 10);
                
                // Check if this is a YouTube Shorts URL
                const isYoutubeShorts = url.includes('youtube.com/shorts') || (url.includes('youtu.be/') && url.length < 30);
                if (isYoutubeShorts) {
                    updateLoadingText('Preparing YouTube Shorts download...', 'Shorts may take longer to process. Please be patient.');
                }
                
                // Make the AJAX request to download the video
                fetch(`?download=1&url=${encodeURIComponent(url)}&format=${format}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Download was successful, now serve the file
                            showSuccess('Download prepared successfully! Starting download...');
                            
                            // Create a download link and trigger it
                            const downloadLink = document.createElement('a');
                            downloadLink.href = `?serve=1&file=${encodeURIComponent(data.file.path)}&name=${encodeURIComponent(data.file.name)}`;
                            downloadLink.download = data.file.name;
                            document.body.appendChild(downloadLink);
                            downloadLink.click();
                            document.body.removeChild(downloadLink);
                            
                            // Complete the download process
                            completeDownload(downloadId, formatElement);
                        } else {
                            // Download failed
                            showError(data.message || 'Download failed');
                            completeDownload(downloadId, formatElement);
                        }
                    })
                    .catch(error => {
                        showError('Error: ' + (error.details || error.message || 'Unknown error'));
                        completeDownload(downloadId, formatElement);
                    });
                
                // Set a timeout for the download
                downloadTimeouts[downloadId] = setTimeout(() => {
                    if (activeDownloads > 0) {
                        showError('Download process timed out. Please try again or try a different format.');
                        completeDownload(downloadId, formatElement);
                    }
                }, 180000); // 3 minutes timeout
            }
            
            function completeDownload(downloadId, formatElement) {
                // Clear the timeout for this download
                if (downloadTimeouts[downloadId]) {
                    clearTimeout(downloadTimeouts[downloadId]);
                    delete downloadTimeouts[downloadId];
                }
                
                // Remove downloading state
                formatElement.classList.remove('downloading');
                activeDownloads--;
                
                // Hide loading overlay if no active downloads
                if (activeDownloads === 0) {
                    loadingOverlay.classList.add('hidden');
                }
            }
            
            function showNotification(message) {
                // Create notification element if it doesn't exist
                let notification = document.getElementById('notification');
                if (!notification) {
                    notification = document.createElement('div');
                    notification.id = 'notification';
                    notification.className = 'notification';
                    document.body.appendChild(notification);
                }
                
                // Set message and show
                notification.textContent = message;
                notification.classList.add('show');
                
                // Hide after 5 seconds
                setTimeout(() => {
                    notification.classList.remove('show');
                }, 5000);
            }
            
            function formatNumber(num) {
                if (num === 'Unknown' || !num) return 'Unknown';
                return new Intl.NumberFormat().format(num);
            }
            
            // Check if URL is already in the input field on page load
            if (videoUrlInput.value.trim()) {
                currentUrl = videoUrlInput.value.trim();
                getVideoInfo(currentUrl);
            }
            
            // Debug functions
            window.toggleDebugMode = function() {
                const debugPanel = document.getElementById('debug-panel');
                debugPanel.classList.toggle('hidden');
            };
            
            window.logDebug = function(message) {
                const debugContent = document.getElementById('debug-content');
                const timestamp = new Date().toLocaleTimeString();
                debugContent.innerHTML += `[${timestamp}] ${message}\n`;
                debugContent.scrollTop = debugContent.scrollHeight;
            };
            
            // Press Ctrl+Shift+D to toggle debug mode
            document.addEventListener('keydown', function(e) {
                if (e.ctrlKey && e.shiftKey && e.key === 'D') {
                    window.toggleDebugMode();
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
