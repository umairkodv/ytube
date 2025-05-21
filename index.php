<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Direct Video Downloader</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #68b4a7;
            --dark-color: #1e293b;
            --gray-color: #64748b;
            --info-color: #3b82f6;
            --success-color: #16a34a;
            --warning-color: #eab308;
            --danger-color: #dc2626;

            --border-radius: 0.5rem;
            --box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: var(--dark-color);
            line-height: 1.6;
        }

        .container {
            max-width: 960px;
            margin: auto;
            padding: 1.5rem;
        }

        header {
            background-color: var(--primary-color);
            color: white;
            padding: 1rem 0;
            margin-bottom: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        header .container {
            text-align: center;
        }

        h1 {
            margin: 0;
            font-size: 2.2rem;
        }

        .card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 1.5rem;
            border: none;
        }

        .card-header {
            background-color: #e9ecef;
            color: var(--gray-color);
            padding: 0.75rem 1.25rem;
            border-bottom: 1px solid #dee2e6;
            font-weight: 500;
            border-top-left-radius: var(--border-radius);
            border-top-right-radius: var(--border-radius);
        }

        .card-body {
            padding: 1.25rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        label {
            font-weight: 500;
            display: block;
            margin-bottom: 0.5rem;
        }

        input[type="text"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ced4da;
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        input[type="text"]:focus {
            border-color: var(--primary-color);
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
        }

        .alert {
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
            padding: 1rem 1.25rem;
        }

        .alert-info {
            background-color: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
        }

        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }

        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }

        .hidden {
            display: none !important;
        }

        .video-info {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .video-thumbnail {
            margin-right: 1.5rem;
            flex: 0 0 auto;
        }

        .video-thumbnail img {
            max-width: 320px;
            height: auto;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        .video-details {
            flex: 1 1 auto;
        }

        .video-title {
            font-size: 1.5rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .video-meta {
            color: var(--gray-color);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
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
            margin: 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
        
        /* Debug toggle button */
        .debug-toggle {
            position: fixed;
            bottom: 10px;
            right: 10px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: var(--box-shadow);
            z-index: 999;
        }
        
        /* Test buttons */
        .test-buttons {
            margin-top: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .test-button {
            background-color: var(--info-color);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 0.9rem;
            transition: background-color 0.3s;
        }
        
        .test-button:hover {
            background-color: #3a7bc8;
        }
        
        /* Platform badges */
        .platform-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            margin-left: 8px;
            vertical-align: middle;
            color: white;
        }
        
        .youtube-badge {
            background-color: #FF0000;
        }
        
        .tiktok-badge {
            background-color: #000000;
        }
        
        .instagram-badge {
            background-color: #C13584;
        }
        
        .facebook-badge {
            background-color: #3b5998;
        }
        
        /* Admin actions */
        .admin-actions {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: var(--border-radius);
            border: 1px solid #dee2e6;
        }
        
        .admin-actions h4 {
            margin-top: 0;
            margin-bottom: 10px;
        }
        
        .admin-actions .btn {
            margin-right: 10px;
            margin-bottom: 10px;
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
        <?php
// Error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session for tracking downloads
session_start();

// Find the full path to ffmpeg
$ffmpeg_path = trim(shell_exec('which ffmpeg'));
if (empty($ffmpeg_path)) {
    $ffmpeg_path = 'ffmpeg'; // Fallback to just the command name
}

// Configuration
$config = [
    'ytdlp_path' => '/usr/local/bin/yt-dlp', // Path to yt-dlp executable
    'ffmpeg_path' => $ffmpeg_path, // Path to ffmpeg executable
    'temp_dir' => '/tmp/', // Temporary directory for downloads
    'log_dir' => '/tmp/', // Log directory (must be writable)
    'debug' => true, // Enable debug logging
    'format_options' => [
        'best' => [
            'label' => 'Best Quality',
            'format' => 'bestvideo+bestaudio/best',
            'ext' => 'mp4'
        ],
        'medium' => [
            'label' => 'Medium Quality (720p)',
            'format' => 'bestvideo[height<=720]+bestaudio/best[height<=720]',
            'ext' => 'mp4'
        ],
        'low' => [
            'label' => 'Low Quality (480p)',
            'format' => 'bestvideo[height<=480]+bestaudio/best[height<=480]',
            'ext' => 'mp4'
        ],
        'audio' => [
            'label' => 'Audio Only (MP3)',
            'format' => 'bestaudio',
            'ext' => 'mp3',
            'audio_only' => true
        ]
    ],
    'user_agents' => [
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Safari/605.1.15",
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:89.0) Gecko/20100101 Firefox/89.0",
        "Mozilla/5.0 (iPad; CPU OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1",
        "Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1",
        "Mozilla/5.0 (Linux; Android 11; SM-G991B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Mobile Safari/537.36",
        "Mozilla/5.0 (Linux; Android 11; Pixel 5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Mobile Safari/537.36"
    ],
    'cookies_file' => 'cookies.txt', // Add this line near the top of the $config array
    'proxy' => '', // Add this line - leave empty if no proxy is needed
    'max_retries' => 3, // Maximum number of retry attempts
    'sleep_interval' => 2, // Sleep interval between retries (seconds)
    'youtube_options' => '--sleep-interval 2 --max-sleep-interval 5 --extractor-args "youtube:player_client=android" --geo-bypass',
];

// Create necessary directories with proper permissions
foreach ([$config['temp_dir'], $config['log_dir']] as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Create cache directory with proper permissions
$cache_dir = '/tmp/yt-dlp-cache/';
if (!file_exists($cache_dir)) {
    mkdir($cache_dir, 0777, true);
}
putenv("XDG_CACHE_HOME={$cache_dir}");

// Dependency check
$dependencies = [
    'ytdlp' => [
        'installed' => file_exists($config['ytdlp_path']),
        'path' => $config['ytdlp_path'],
        'version' => ''
    ],
    'ffmpeg' => [
        'installed' => file_exists($config['ffmpeg_path']),
        'path' => $config['ffmpeg_path']
    ]
];

// Get yt-dlp version
if ($dependencies['ytdlp']['installed']) {
    exec($config['ytdlp_path'] . ' --version 2>&1', $output, $return_var);
    if ($return_var === 0 && isset($output[0])) {
        $dependencies['ytdlp']['version'] = trim($output[0]);
    }
}

// Helper function for debug logging
function debug_log($message, $config, $level = 'INFO') {
    if ($config['debug']) {
        $timestamp = date('Y-m-d H:i:s');
        error_log("[$timestamp] [$level] $message\n", 0);
    }
}

// Helper function to get a random user agent
function get_random_user_agent($config) {
    return $config['user_agents'][array_rand($config['user_agents'])];
}

// Sanitize file name
function sanitize_filename($filename) {
    $filename = preg_replace('/[^\w\s\-\.]/u', '', $filename);
    $filename = trim($filename, '.-_ ');
    $filename = preg_replace('/\s+/u', '_', $filename);
    return $filename;
}

// Function to get video info with enhanced error handling
function get_video_info($url, $config) {
    debug_log("Getting video info for URL: $url", $config);
    $ytdlp_path = $config['ytdlp_path'];
    $escaped_url = escapeshellarg($url);
    $output_file = $config['temp_dir'] . 'info_' . md5(uniqid() . $url) . '.json';
    $error_log = $config['log_dir'] . 'info_error_' . uniqid() . '.log';

    // Get a random user agent
    $user_agent = get_random_user_agent($config);
    $user_agent_arg = escapeshellarg($user_agent);

    // Use cookies file if it exists
    $cookies_arg = '';
    if (!empty($config['cookies_file']) && file_exists($config['cookies_file'])) {
        $cookies_arg = '--cookies ' . escapeshellarg($config['cookies_file']);
        debug_log("Using cookies file: {$config['cookies_file']}", $config);
    }
    
    // Use proxy if configured
    $proxy_arg = '';
    if (!empty($config['proxy'])) {
        $proxy_arg = '--proxy ' . escapeshellarg($config['proxy']);
        debug_log("Using proxy: {$config['proxy']}", $config);
    }

    // Determine platform-specific options
    $is_youtube = (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false);
    $is_youtube_shorts = (strpos($url, 'youtube.com/shorts') !== false);
    $is_tiktok = (strpos($url, 'tiktok.com') !== false);
    $is_instagram = (strpos($url, 'instagram.com') !== false);
    $is_facebook = (strpos($url, 'facebook.com') !== false || strpos($url, 'fb.watch') !== false);

    // YouTube-specific approach
    if ($is_youtube) {
        debug_log("Using YouTube-specific approach", $config);
        
        // First try: Use YouTube-specific options
        $youtube_cmd = "$ytdlp_path --no-check-certificate --no-warnings --ignore-errors " .
                      "--extractor-args \"youtube:player_client=android\" " .
                      "--user-agent $user_agent_arg $cookies_arg $proxy_arg " .
                      "--dump-json $escaped_url > \"$output_file\" 2>\"$error_log\"";
        
        debug_log("Executing YouTube command: $youtube_cmd", $config);
        exec($youtube_cmd, $youtube_output, $youtube_return_var);
        debug_log("YouTube command returned: $youtube_return_var", $config);
        
        // Check if the output file exists and has valid content
        if (file_exists($output_file) && filesize($output_file) > 10) {
            $info_content = file_get_contents($output_file);
            
            // Try to parse the JSON
            $info = json_decode($info_content, true);
            
            if ($info) {
                debug_log("Successfully parsed YouTube video info", $config);
                
                // Clean up the files
                @unlink($output_file);
                @unlink($error_log);
                
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
                        'is_shorts' => $is_youtube_shorts,
                        'is_youtube' => $is_youtube,
                        'is_tiktok' => $is_tiktok,
                        'is_instagram' => $is_instagram,
                        'is_facebook' => $is_facebook
                    ]
                ];
            } else {
                debug_log("Failed to parse YouTube JSON: " . json_last_error_msg(), $config, 'ERROR');
                debug_log("Raw YouTube content: " . substr($info_content, 0, 500) . "...", $config, 'ERROR');
                
                // Log error if available
                if (file_exists($error_log)) {
                    $error_content = file_get_contents($error_log);
                    debug_log("YouTube info error: " . $error_content, $config, 'ERROR');
                }
            }
        }
        
        // Second try: Use simpler approach with different options
        debug_log("First YouTube approach failed, trying alternative method", $config);
        
        // Use a different format for YouTube
        $alt_youtube_cmd = "$ytdlp_path --no-check-certificate --no-warnings --ignore-errors " .
                          "--user-agent $user_agent_arg $cookies_arg $proxy_arg " .
                          "--print-json $escaped_url > \"$output_file\" 2>\"$error_log\"";
        
        debug_log("Executing alternative YouTube command: $alt_youtube_cmd", $config);
        exec($alt_youtube_cmd, $alt_output, $alt_return_var);
        debug_log("Alternative YouTube command returned: $alt_return_var", $config);
        
        if (file_exists($output_file) && filesize($output_file) > 10) {
            $info_content = file_get_contents($output_file);
            
            // Try to parse the JSON
            $info = json_decode($info_content, true);
            
            if ($info) {
                debug_log("Successfully parsed YouTube video info with alternative method", $config);
                
                // Clean up the files
                @unlink($output_file);
                @unlink($error_log);
                
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
                        'is_shorts' => $is_youtube_shorts,
                        'is_youtube' => $is_youtube,
                        'is_tiktok' => $is_tiktok,
                        'is_instagram' => $is_instagram,
                        'is_facebook' => $is_facebook
                    ]
                ];
            } else {
                debug_log("Failed to parse YouTube JSON with alternative method: " . json_last_error_msg(), $config, 'ERROR');
                debug_log("Raw YouTube content: " . substr($info_content, 0, 500) . "...", $config, 'ERROR');
                
                // Log error if available
                if (file_exists($error_log)) {
                    $error_content = file_get_contents($error_log);
                    debug_log("Alternative YouTube info error: " . $error_content, $config, 'ERROR');
                }
            }
        }
        
        // Third try: Use YouTube API to get basic info
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
            
            // Clean up the files
            @unlink($output_file);
            @unlink($error_log);
            
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
                    'is_shorts' => $is_youtube_shorts,
                    'is_youtube' => $is_youtube,
                    'is_tiktok' => $is_tiktok,
                    'is_instagram' => $is_instagram,
                    'is_facebook' => $is_facebook
                ]
            ];
        }
        
        // If all YouTube-specific approaches fail
        debug_log("All YouTube approaches failed", $config, 'ERROR');
        
        // Clean up the files
        @unlink($output_file);
        @unlink($error_log);
        
        return [
            'success' => false,
            'message' => 'Failed to retrieve YouTube video information'
        ];
    }
    // Instagram-specific approach
    else if ($is_instagram) {
        debug_log("Using Instagram-specific approach", $config);
        
        // Use mobile user agent for Instagram
        $instagram_user_agent = "Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1";
        $instagram_user_agent_arg = escapeshellarg($instagram_user_agent);
        
        $instagram_cmd = "$ytdlp_path --no-check-certificate --no-warnings " .
                        "--user-agent $instagram_user_agent_arg $cookies_arg $proxy_arg " .
                        "-J $escaped_url > \"$output_file\" 2>\"$error_log\"";
        
        debug_log("Executing Instagram command: $instagram_cmd", $config);
        exec($instagram_cmd, $instagram_output, $instagram_return_var);
        debug_log("Instagram command returned: $instagram_return_var", $config);
        
        // Check if the output file exists and has valid content
        if (file_exists($output_file) && filesize($output_file) > 10) {
            $info_content = file_get_contents($output_file);
            
            // Try to parse the JSON
            $info = json_decode($info_content, true);
            
            if ($info) {
                debug_log("Successfully parsed Instagram content info", $config);
                
                // Clean up the files
                @unlink($output_file);
                @unlink($error_log);
                
                // Sanitize filename for download
                $title = $info['title'] ?? 'Instagram Content';
                $sanitized_title = preg_replace('/[^\w\s\-]/u', '', $title);
                $sanitized_title = str_replace(' ', '_', $sanitized_title);
                $sanitized_title = substr($sanitized_title, 0, 100);
                if (empty($sanitized_title)) {
                    $sanitized_title = 'Instagram_Content_' . time();
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
                        'is_shorts' => false,
                        'is_youtube' => false,
                        'is_tiktok' => false,
                        'is_instagram' => true,
                        'is_facebook' => false
                    ]
                ];
            } else {
                debug_log("Failed to parse Instagram JSON: " . json_last_error_msg(), $config, 'ERROR');
                debug_log("Raw Instagram content: " . substr($info_content, 0, 500) . "...", $config, 'ERROR');
                
                // Log error if available
                if (file_exists($error_log)) {
                    $error_content = file_get_contents($error_log);
                    debug_log("Instagram info error: " . $error_content, $config, 'ERROR');
                }
            }
        }
        
        // Try alternative approach for Instagram
        debug_log("First Instagram approach failed, trying alternative method", $config);
        
        $alt_instagram_cmd = "$ytdlp_path --no-check-certificate --no-warnings " .
                            "--user-agent $instagram_user_agent_arg $cookies_arg $proxy_arg " .
                            "--dump-json $escaped_url > \"$output_file\" 2>\"$error_log\"";
        
        debug_log("Executing alternative Instagram command: $alt_instagram_cmd", $config);
        exec($alt_instagram_cmd, $alt_output, $alt_return_var);
        debug_log("Alternative Instagram command returned: $alt_return_var", $config);
        
        if (file_exists($output_file) && filesize($output_file) > 10) {
            $info_content = file_get_contents($output_file);
            
            // Try to parse the JSON
            $info = json_decode($info_content, true);
            
            if ($info) {
                debug_log("Successfully parsed Instagram content info with alternative method", $config);
                
                // Clean up the files
                @unlink($output_file);
                @unlink($error_log);
                
                // Sanitize filename for download
                $title = $info['title'] ?? 'Instagram Content';
                $sanitized_title = preg_replace('/[^\w\s\-]/u', '', $title);
                $sanitized_title = str_replace(' ', '_', $sanitized_title);
                $sanitized_title = substr($sanitized_title, 0, 100);
                if (empty($sanitized_title)) {
                    $sanitized_title = 'Instagram_Content_' . time();
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
                        'is_shorts' => false,
                        'is_youtube' => false,
                        'is_tiktok' => false,
                        'is_instagram' => true,
                        'is_facebook' => false
                    ]
                ];
            } else {
                debug_log("Failed to parse Instagram JSON with alternative method: " . json_last_error_msg(), $config, 'ERROR');
                debug_log("Raw Instagram content: " . substr($info_content, 0, 500) . "...", $config, 'ERROR');
                
                // Log error if available
                if (file_exists($error_log)) {
                    $error_content = file_get_contents($error_log);
                    debug_log("Alternative Instagram info error: " . $error_content, $config, 'ERROR');
                }
            }
        }
        
        // If all Instagram-specific approaches fail
        debug_log("All Instagram approaches failed", $config, 'ERROR');
        
        // Clean up the files
        @unlink($output_file);
        @unlink($error_log);
        
        return [
            'success' => false,
            'message' => 'Failed to retrieve Instagram content information. Instagram may require authentication.'
        ];
    }
    // Facebook-specific approach
    else if ($is_facebook) {
        debug_log("Using Facebook-specific approach", $config);
        
        // Use mobile user agent for Facebook
        $facebook_user_agent = "Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1";
        $facebook_user_agent_arg = escapeshellarg($facebook_user_agent);
        
        $facebook_cmd = "$ytdlp_path --no-check-certificate --no-warnings " .
                       "--user-agent $facebook_user_agent_arg $cookies_arg $proxy_arg " .
                       "-J $escaped_url > \"$output_file\" 2>\"$error_log\"";
        
        debug_log("Executing Facebook command: $facebook_cmd", $config);
        exec($facebook_cmd, $facebook_output, $facebook_return_var);
        debug_log("Facebook command returned: $facebook_return_var", $config);
        
        // Check if the output file exists and has valid content
        if (file_exists($output_file) && filesize($output_file) > 10) {
            $info_content = file_get_contents($output_file);
            
            // Try to parse the JSON
            $info = json_decode($info_content, true);
            
            if ($info) {
                debug_log("Successfully parsed Facebook content info", $config);
                
                // Clean up the files
                @unlink($output_file);
                @unlink($error_log);
                
                // Sanitize filename for download
                $title = $info['title'] ?? 'Facebook Content';
                $sanitized_title = preg_replace('/[^\w\s\-]/u', '', $title);
                $sanitized_title = str_replace(' ', '_', $sanitized_title);
                $sanitized_title = substr($sanitized_title, 0, 100);
                if (empty($sanitized_title)) {
                    $sanitized_title = 'Facebook_Content_' . time();
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
                        'is_shorts' => false,
                        'is_youtube' => false,
                        'is_tiktok' => false,
                        'is_instagram' => false,
                        'is_facebook' => true
                    ]
                ];
            } else {
                debug_log("Failed to parse Facebook JSON: " . json_last_error_msg(), $config, 'ERROR');
                debug_log("Raw Facebook content: " . substr($info_content, 0, 500) . "...", $config, 'ERROR');
                
                // Log error if available
                if (file_exists($error_log)) {
                    $error_content = file_get_contents($error_log);
                    debug_log("Facebook info error: " . $error_content, $config, 'ERROR');
                }
            }
        }
        
        // Try alternative approach for Facebook
        debug_log("First Facebook approach failed, trying alternative method", $config);
        
        $alt_facebook_cmd = "$ytdlp_path --no-check-certificate --no-warnings " .
                           "--user-agent $facebook_user_agent_arg $cookies_arg $proxy_arg " .
                           "--dump-json $escaped_url > \"$output_file\" 2>\"$error_log\"";
        
        debug_log("Executing alternative Facebook command: $alt_facebook_cmd", $config);
        exec($alt_facebook_cmd, $alt_output, $alt_return_var);
        debug_log("Alternative Facebook command returned: $alt_return_var", $config);
        
        if (file_exists($output_file) && filesize($output_file) > 10) {
            $info_content = file_get_contents($output_file);
            
            // Try to parse the JSON
            $info = json_decode($info_content, true);
            
            if ($info) {
                debug_log("Successfully parsed Facebook content info with alternative method", $config);
                
                // Clean up the files
                @unlink($output_file);
                @unlink($error_log);
                
                // Sanitize filename for download
                $title = $info['title'] ?? 'Facebook Content';
                $sanitized_title = preg_replace('/[^\w\s\-]/u', '', $title);
                $sanitized_title = str_replace(' ', '_', $sanitized_title);
                $sanitized_title = substr($sanitized_title, 0, 100);
                if (empty($sanitized_title)) {
                    $sanitized_title = 'Facebook_Content_' . time();
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
                        'is_shorts' => false,
                        'is_youtube' => false,
                        'is_tiktok' => false,
                        'is_instagram' => false,
                        'is_facebook' => true
                    ]
                ];
            } else {
                debug_log("Failed to parse Facebook JSON with alternative method: " . json_last_error_msg(), $config, 'ERROR');
                debug_log("Raw Facebook content: " . substr($info_content, 0, 500) . "...", $config, 'ERROR');
                
                // Log error if available
                if (file_exists($error_log)) {
                    $error_content = file_get_contents($error_log);
                    debug_log("Alternative Facebook info error: " . $error_content, $config, 'ERROR');
                }
            }
        }
        
        // If all Facebook-specific approaches fail
        debug_log("All Facebook approaches failed", $config, 'ERROR');
        
        // Clean up the files
        @unlink($output_file);
        @unlink($error_log);
        
        return [
            'success' => false,
            'message' => 'Failed to retrieve Facebook content information. Facebook may require authentication.'
        ];
    }

    // TikTok and other platforms - use the working approach
    $cmd = "$ytdlp_path -J --no-check-certificate --no-warnings $cookies_arg $proxy_arg $escaped_url > \"$output_file\" 2>\"$error_log\"";

    // Log the command
    debug_log("Executing info command: $cmd", $config);

    // Execute the command
    exec($cmd, $output, $return_var);

    // Log the result
    debug_log("Info command returned: $return_var", $config);

    // Check if the output file exists and has content
    if (!file_exists($output_file) || filesize($output_file) < 10) {
        debug_log("Info file not created or empty. Return code: $return_var", $config, 'ERROR');
        
        // Log error if available
        if (file_exists($error_log)) {
            $error_content = file_get_contents($error_log);
            debug_log("Info error: " . $error_content, $config, 'ERROR');
        }
        
        // Try a simpler approach
        debug_log("Trying simpler approach", $config);
        $simple_cmd = "$ytdlp_path --dump-json --skip-download $cookies_arg $proxy_arg $escaped_url > \"$output_file\" 2>\"$error_log\"";
        debug_log("Executing simple command: $simple_cmd", $config);
        exec($simple_cmd, $simple_output, $simple_return_var);
        debug_log("Simple command returned: $simple_return_var", $config);
        
        if (!file_exists($output_file) || filesize($output_file) < 10) {
            debug_log("Simple approach failed. Return code: $simple_return_var", $config, 'ERROR');
            
            // Log error if available
            if (file_exists($error_log)) {
                $error_content = file_get_contents($error_log);
                debug_log("Simple info error: " . $error_content, $config, 'ERROR');
            }
            
            // Clean up the files
            @unlink($output_file);
            @unlink($error_log);
            
            return [
                'success' => false,
                'message' => 'Failed to retrieve video information'
            ];
        }
    }

    // Read and parse the JSON file
    $info_content = file_get_contents($output_file);
    debug_log("Info file size: " . strlen($info_content) . " bytes", $config);

    // Clean up the files
    @unlink($output_file);
    @unlink($error_log);

    // Parse JSON
    $info = json_decode($info_content, true);

    if (!$info) {
        debug_log("Failed to parse video info JSON: " . json_last_error_msg(), $config, 'ERROR');
        debug_log("Raw content: " . substr($info_content, 0, 500) . "...", $config, 'ERROR');
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
            'is_shorts' => $is_youtube_shorts,
            'is_youtube' => $is_youtube,
            'is_tiktok' => $is_tiktok,
            'is_instagram' => $is_instagram,
            'is_facebook' => $is_facebook
        ]
    ];
}

// Function to download video directly with enhanced error handling
function download_video($url, $format_key, $config) {
    debug_log("Starting direct download for URL: $url, Format: $format_key", $config);

    // Get video info to get the title
    $video_info = get_video_info($url, $config);

    if (!$video_info['success']) {
        debug_log("Failed to get video info for download", $config, 'ERROR');
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
    $is_youtube = $info['is_youtube'] ?? false;
    $is_tiktok = $info['is_tiktok'] ?? false;
    $is_instagram = strpos($url, 'instagram.com') !== false;
    $is_facebook = strpos($url, 'facebook.com') !== false || strpos($url, 'fb.watch') !== false;

    // Create a temporary file with unique name
    $temp_file = $config['temp_dir'] . uniqid('download_') . '.' . $ext;
    $error_log = $config['log_dir'] . 'download_error_' . uniqid() . '.log';
    debug_log("Temp file: $temp_file", $config);
    debug_log("Error log: $error_log", $config);

    // Get a random user agent
    $user_agent = get_random_user_agent($config);
    $user_agent_arg = escapeshellarg($user_agent);

    // Build the command
    $ytdlp_path = $config['ytdlp_path'];
    $escaped_url = escapeshellarg($url);
    $ffmpeg_path = escapeshellarg($config['ffmpeg_path']);
    $cookies_arg = '';
    
    // Use cookies file if it exists
    if (!empty($config['cookies_file']) && file_exists($config['cookies_file'])) {
        $cookies_arg = '--cookies ' . escapeshellarg($config['cookies_file']);
        debug_log("Using cookies file: {$config['cookies_file']}", $config);
    }
    
    // Use proxy if configured
    $proxy_arg = '';
    if (!empty($config['proxy'])) {
        $proxy_arg = '--proxy ' . escapeshellarg($config['proxy']);
        debug_log("Using proxy: {$config['proxy']}", $config);
    }

    // YouTube-specific download approaches
    if ($is_youtube) {
        debug_log("Using YouTube-specific download approach", $config);
        
        // APPROACH 1: Try with format selection and YouTube-specific options
        if ($audio_only) {
            $cmd = "$ytdlp_path -f $format_str -x --audio-format $ext --audio-quality 0 " .
                  "--no-check-certificate --no-warnings --ignore-errors " .
                  "--extractor-args \"youtube:player_client=android\" " .
                  "--user-agent $user_agent_arg $cookies_arg $proxy_arg " .
                  "--ffmpeg-location $ffmpeg_path -o \"$temp_file\" $escaped_url 2>\"$error_log\"";
        } else {
            $cmd = "$ytdlp_path -f $format_str --merge-output-format $ext " .
                  "--no-check-certificate --no-warnings --ignore-errors " .
                  "--extractor-args \"youtube:player_client=android\" " .
                  "--user-agent $user_agent_arg $cookies_arg $proxy_arg " .
                  "--ffmpeg-location $ffmpeg_path -o \"$temp_file\" $escaped_url 2>\"$error_log\"";
        }
        
        // Log the command
        debug_log("Executing YouTube download command (Approach 1): $cmd", $config);
        
        // Execute the command
        exec($cmd, $output, $return_var);
        
        // Log the result
        debug_log("YouTube download command returned: $return_var", $config);
        
        // Check if the file exists and has content
        if ($return_var === 0 && file_exists($temp_file) && filesize($temp_file) > 1000) {
            debug_log("YouTube download successful with Approach 1", $config);
            goto download_success;
        }
        
        // Log error if available
        if (file_exists($error_log)) {
            $error_content = file_get_contents($error_log);
            debug_log("YouTube download error (Approach 1): " . $error_content, $config, 'ERROR');
        }
        
        // APPROACH 2: Try with simpler options and different format string
        debug_log("YouTube Approach 1 failed, trying Approach 2", $config);
        
        if ($audio_only) {
            $cmd = "$ytdlp_path -f bestaudio --extract-audio --audio-format $ext --audio-quality 0 " .
                  "--no-check-certificate --no-warnings $cookies_arg $proxy_arg " .
                  "--user-agent $user_agent_arg " .
                  "--ffmpeg-location $ffmpeg_path -o \"$temp_file\" $escaped_url 2>\"$error_log\"";
        } else {
            $cmd = "$ytdlp_path -f \"bestvideo[ext=mp4]+bestaudio[ext=m4a]/best[ext=mp4]/best\" " .
                  "--no-check-certificate --no-warnings $cookies_arg $proxy_arg " .
                  "--user-agent $user_agent_arg " .
                  "--ffmpeg-location $ffmpeg_path -o \"$temp_file\" $escaped_url 2>\"$error_log\"";
        }
        
        debug_log("Executing YouTube download command (Approach 2): $cmd", $config);
        exec($cmd, $output, $return_var);
        debug_log("YouTube download command returned: $return_var", $config);
        
        if ($return_var === 0 && file_exists($temp_file) && filesize($temp_file) > 1000) {
            debug_log("YouTube download successful with Approach 2", $config);
            goto download_success;
        }
        
        // Log error if available
        if (file_exists($error_log)) {
            $error_content = file_get_contents($error_log);
            debug_log("YouTube download error (Approach 2): " . $error_content, $config, 'ERROR');
        }
        
        // APPROACH 3: Try with --force-overwrites and different format
        debug_log("YouTube Approach 2 failed, trying Approach 3", $config);
        
        $cmd = "$ytdlp_path --force-overwrites --no-check-certificate " .
              "--no-warnings --ignore-errors $cookies_arg $proxy_arg " .
              "--user-agent $user_agent_arg " .
              "--ffmpeg-location $ffmpeg_path -o \"$temp_file\" $escaped_url 2>\"$error_log\"";
        
        debug_log("Executing YouTube download command (Approach 3): $cmd", $config);
        exec($cmd, $output, $return_var);
        debug_log("YouTube download command returned: $return_var", $config);
        
        if ($return_var === 0 && file_exists($temp_file) && filesize($temp_file) > 1000) {
            debug_log("YouTube download successful with Approach 3", $config);
            goto download_success;
        }
        
        // Log error if available
        if (file_exists($error_log)) {
            $error_content = file_get_contents($error_log);
            debug_log("YouTube download error (Approach 3): " . $error_content, $config, 'ERROR');
        }
        
        // APPROACH 4: Last resort - try with --no-playlist and minimal options
        debug_log("YouTube Approach 3 failed, trying Approach 4 (last resort)", $config);
        
        $cmd = "$ytdlp_path --no-playlist --no-check-certificate " .
              "--force-overwrites $cookies_arg $proxy_arg " .
              "-o \"$temp_file\" $escaped_url 2>\"$error_log\"";
        
        debug_log("Executing YouTube download command (Approach 4): $cmd", $config);
        exec($cmd, $output, $return_var);
        debug_log("YouTube download command returned: $return_var", $config);
        
        if ($return_var === 0 && file_exists($temp_file) && filesize($temp_file) > 1000) {
            debug_log("YouTube download successful with Approach 4", $config);
            goto download_success;
        }
        
        // Log error if available
        if (file_exists($error_log)) {
            $error_content = file_get_contents($error_log);
            debug_log("YouTube download error (Approach 4): " . $error_content, $config, 'ERROR');
        }
        
        // All approaches failed
        debug_log("All YouTube download approaches failed", $config, 'ERROR');
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Failed to download YouTube video after multiple attempts'
        ]);
        
        // Clean up
        if (file_exists($temp_file)) {
            unlink($temp_file);
        }
        if (file_exists($error_log)) {
            unlink($error_log);
        }
        
        exit;
    }
    // Instagram-specific approach
    else if ($is_instagram) {
        debug_log("Using Instagram-specific download approach", $config);
        
        $cmd = "$ytdlp_path --no-check-certificate --no-warnings " .
              "$cookies_arg $proxy_arg " .
              "--user-agent $user_agent_arg " .
              "--ffmpeg-location $ffmpeg_path -o \"$temp_file\" $escaped_url 2>\"$error_log\"";
        
        debug_log("Executing Instagram download command: $cmd", $config);
        exec($cmd, $output, $return_var);
        debug_log("Instagram download command returned: $return_var", $config);
        
        if ($return_var === 0 && file_exists($temp_file) && filesize($temp_file) > 1000) {
            debug_log("Instagram download successful", $config);
            goto download_success;
        }
        
        // Log error if available
        if (file_exists($error_log)) {
            $error_content = file_get_contents($error_log);
            debug_log("Instagram download error: " . $error_content, $config, 'ERROR');
        }
        
        // Try alternative approach for Instagram
        debug_log("Instagram download failed, trying alternative approach", $config);
        
        $cmd = "$ytdlp_path --force-overwrites --no-check-certificate " .
              "$cookies_arg $proxy_arg " .
              "--user-agent \"Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1\" " .
              "-o \"$temp_file\" $escaped_url 2>\"$error_log\"";
        
        debug_log("Executing alternative Instagram download command: $cmd", $config);
        exec($cmd, $output, $return_var);
        debug_log("Alternative Instagram download command returned: $return_var", $config);
        
        if ($return_var === 0 && file_exists($temp_file) && filesize($temp_file) > 1000) {
            debug_log("Instagram download successful with alternative approach", $config);
            goto download_success;
        }
        
        // Log error if available
        if (file_exists($error_log)) {
            $error_content = file_get_contents($error_log);
            debug_log("Alternative Instagram download error: " . $error_content, $config, 'ERROR');
        }
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Failed to download Instagram content. Instagram may require authentication.'
        ]);
        
        // Clean up
        if (file_exists($temp_file)) {
            unlink($temp_file);
        }
        if (file_exists($error_log)) {
            unlink($error_log);
        }
        
        exit;
    }
    // Facebook-specific approach
    else if ($is_facebook) {
        debug_log("Using Facebook-specific download approach", $config);
        
        $cmd = "$ytdlp_path --no-check-certificate --no-warnings " .
              "$cookies_arg $proxy_arg " .
              "--user-agent $user_agent_arg " .
              "--ffmpeg-location $ffmpeg_path -o \"$temp_file\" $escaped_url 2>\"$error_log\"";
        
        debug_log("Executing Facebook download command: $cmd", $config);
        exec($cmd, $output, $return_var);
        debug_log("Facebook download command returned: $return_var", $config);
        
        if ($return_var === 0 && file_exists($temp_file) && filesize($temp_file) > 1000) {
            debug_log("Facebook download successful", $config);
            goto download_success;
        }
        
        // Log error if available
        if (file_exists($error_log)) {
            $error_content = file_get_contents($error_log);
            debug_log("Facebook download error: " . $error_content, $config, 'ERROR');
        }
        
        // Try alternative approach for Facebook
        debug_log("Facebook download failed, trying alternative approach", $config);
        
        $cmd = "$ytdlp_path --force-overwrites --no-check-certificate " .
              "$cookies_arg $proxy_arg " .
              "--user-agent \"Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1\" " .
              "-o \"$temp_file\" $escaped_url 2>\"$error_log\"";
        
        debug_log("Executing alternative Facebook download command: $cmd", $config);
        exec($cmd, $output, $return_var);
        debug_log("Alternative Facebook download command returned: $return_var", $config);
        
        if ($return_var === 0 && file_exists($temp_file) && filesize($temp_file) > 1000) {
            debug_log("Facebook download successful with alternative approach", $config);
            goto download_success;
        }
        
        // Log error if available
        if (file_exists($error_log)) {
            $error_content = file_get_contents($error_log);
            debug_log("Alternative Facebook download error: " . $error_content, $config, 'ERROR');
        }
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Failed to download Facebook content. Facebook may require authentication.'
        ]);
        
        // Clean up
        if (file_exists($temp_file)) {
            unlink($temp_file);
        }
        if (file_exists($error_log)) {
            unlink($error_log);
        }
        
        exit;
    }
    // TikTok and other platforms - use the working approach
    else {
        if ($audio_only) {
            $cmd = "$ytdlp_path -f $format_str -x --audio-format $ext --audio-quality 0 " .
                  "--no-check-certificate $cookies_arg $proxy_arg " .
                  "--ffmpeg-location $ffmpeg_path -o \"$temp_file\" $escaped_url 2>\"$error_log\"";
        } else {
            $cmd = "$ytdlp_path -f $format_str --merge-output-format $ext " .
                  "--no-check-certificate $cookies_arg $proxy_arg " .
                  "--ffmpeg-location $ffmpeg_path -o \"$temp_file\" $escaped_url 2>\"$error_log\"";
        }

        // Log the command
        debug_log("Executing download command: $cmd", $config);

        // Execute the command
        exec($cmd, $output, $return_var);

        // Log the result
        debug_log("Download command returned: $return_var", $config);

        // Check if the file exists and has content
        if ($return_var !== 0 || !file_exists($temp_file) || filesize($temp_file) < 1000) { // File should be at least 1KB
            debug_log("Download failed or file too small. Return code: $return_var", $config, 'ERROR');
            
            // Log error if available
            if (file_exists($error_log)) {
                $error_content = file_get_contents($error_log);
                debug_log("Download error: " . $error_content, $config, 'ERROR');
            }
            
            // Try a simpler approach
            debug_log("Trying simpler download approach", $config);
            
            $simple_cmd = "$ytdlp_path --no-check-certificate $cookies_arg $proxy_arg " .
                         "-o \"$temp_file\" $escaped_url 2>\"$error_log\"";
            
            debug_log("Executing simple download command: $simple_cmd", $config);
            exec($simple_cmd, $simple_output, $simple_return_var);
            debug_log("Simple download command returned: $simple_return_var", $config);
            
            if ($simple_return_var !== 0 || !file_exists($temp_file) || filesize($temp_file) < 1000) {
                debug_log("Simple download approach failed. Return code: $simple_return_var", $config, 'ERROR');
                
                // Log error if available
                if (file_exists($error_log)) {
                    $error_content = file_get_contents($error_log);
                    debug_log("Simple download error: " . $error_content, $config, 'ERROR');
                }
                
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to download video'
                ]);
                
                // Clean up
                if (file_exists($temp_file)) {
                    unlink($temp_file);
                }
                if (file_exists($error_log)) {
                    unlink($error_log);
                }
                
                exit;
            }
        }
    }

    download_success:
    // File exists and has content, prepare for download
    debug_log("Download successful, preparing to serve file: " . filesize($temp_file) . " bytes", $config);

    // Clean up error log if it exists
    if (file_exists($error_log)) {
        unlink($error_log);
    }

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
        debug_log("File not found: $file_path", $config, 'ERROR');
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
    if (ob_get_level()) {
        ob_end_clean();
    }
    flush();

    // Read the file and output it to the browser
    readfile($file_path);

    // Delete the temporary file
    unlink($file_path);
    exit;
}

// Function to test directory permissions
function test_permissions($config) {
    $test_file = $config['temp_dir'] . 'test_' . uniqid() . '.txt';
    $test_content = 'Permission test: ' . date('Y-m-d H:i:s');

    $write_success = @file_put_contents($test_file, $test_content);

    if ($write_success === false) {
        return [
            'success' => false,
            'message' => 'Failed to write to temp directory'
        ];
    }

    $read_content = @file_get_contents($test_file);
    @unlink($test_file);

    if ($read_content !== $test_content) {
        return [
            'success' => false,
            'message' => 'Failed to read from temp directory'
        ];
    }

    $log_file = $config['log_dir'] . 'test_' . uniqid() . '.txt';
    $log_success = @file_put_contents($log_file, $test_content);
    @unlink($log_file);

    if ($log_success === false) {
        return [
            'success' => false,
            'message' => 'Failed to write to log directory'
        ];
    }

    return [
        'success' => true,
        'message' => 'Directory permissions are correct'
    ];
}

// Add this new function to update yt-dlp
function update_ytdlp($config) {
    debug_log("Updating yt-dlp", $config);
    $ytdlp_path = $config['ytdlp_path'];
    $output_file = $config['log_dir'] . 'update_' . uniqid() . '.log';
    
    // Build the command
    $cmd = "$ytdlp_path -U > \"$output_file\" 2>&1";
    
    // Log the command
    debug_log("Executing update command: $cmd", $config);
    
    // Execute the command
    exec($cmd, $output, $return_var);
    
    // Log the result
    debug_log("Update command returned: $return_var", $config);
    
    // Read the output
    $update_output = '';
    if (file_exists($output_file)) {
        $update_output = file_get_contents($output_file);
        debug_log("Update output: " . $update_output, $config);
        @unlink($output_file);
    }
    
    if ($return_var === 0) {
        return [
            'success' => true,
            'message' => 'yt-dlp updated successfully: ' . trim($update_output)
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Failed to update yt-dlp: ' . trim($update_output)
        ];
    }
}

// AJAX request handling
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    switch ($_GET['action']) {
        case 'get_info':
            $url = $_POST['url'] ?? '';
            if (empty($url)) {
                echo json_encode(['success' => false, 'message' => 'URL is required']);
            } else {
                echo json_encode(get_video_info($url, $config));
            }
            break;
        case 'check_dependencies':
            echo json_encode($dependencies);
            break;
        case 'test_permissions':
            echo json_encode(test_permissions($config));
            break;
        case 'update_ytdlp':
            echo json_encode(update_ytdlp($config));
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit;
}

// Download request handling
if (isset($_GET['download'])) {
    $url = $_GET['url'] ?? '';
    $format = $_GET['format'] ?? 'best';
    if (empty($url)) {
        header('HTTP/1.0 400 Bad Request');
        echo json_encode(['success' => false, 'message' => 'URL is required']);
    } else {
        download_video($url, $format, $config);
    }
    exit;
}

// Serve file request handling
if (isset($_GET['serve'])) {
    $file = $_GET['file'] ?? '';
    $name = $_GET['name'] ?? 'downloaded_video.mp4';
    if (empty($file) || !file_exists($file)) {
        header('HTTP/1.0 404 Not Found');
        echo "File not found";
    } else {
        serve_file($file, $name, $config);
    }
    exit;
}

// Render the HTML page if no AJAX or download request
?>

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
                <p><strong>Paths:</strong> yt-dlp: <?php echo $dependencies['ytdlp']['path']; ?>, ffmpeg: <?php echo $dependencies['ffmpeg']['path']; ?></p>
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
                
                <!-- Test buttons for quick testing -->
                <div class="test-buttons">
                    <button class="test-button" onclick="testUrl('https://www.youtube.com/shorts/9NHpoCMMIko')">Test YouTube Shorts</button>
                    <button class="test-button" onclick="testUrl('https://vt.tiktok.com/ZShsdTSVr/')">Test TikTok</button>
                    <button class="test-button" onclick="testUrl('https://www.youtube.com/watch?v=dQw4w9WgXcQ')">Test YouTube</button>
                    <button class="test-button" onclick="runDiagnostics()">Run Diagnostics</button>
                    <button class="test-button" onclick="updateYtDlp()">Update yt-dlp</button>
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
    
    <button class="debug-toggle" onclick="window.toggleDebugMode()">
        <i class="fas fa-bug"></i>
    </button>

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
            videoUrlInput.addEventListener('keyup', function(e) {
                clearTimeout(typingTimer);
                if (videoUrlInput.value) {
                    // If Enter key is pressed, immediately process
                    if (e.key === 'Enter') {
                        doneTyping();
                    } else {
                        typingTimer = setTimeout(doneTyping, doneTypingInterval);
                    }
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
                
                // Log to debug panel
                window.logDebug('ERROR: ' + message);
            }
            
            function showSuccess(message) {
                successContainer.textContent = message;
                successContainer.classList.remove('hidden');
                
                // Auto-hide success after 5 seconds
                setTimeout(() => {
                    successContainer.classList.add('hidden');
                }, 5000);
                
                // Log to debug panel
                window.logDebug('SUCCESS: ' + message);
            }
            
            function hideError() {
                errorContainer.classList.add('hidden');
            }
            
            function getVideoInfo(url) {
                hideError();
                videoInfoContainer.classList.add('hidden');
                
                // Show spinner in URL input
                urlInputSpinner.classList.remove('hidden');
                
                // Show full-screen loading overlay for fetching video info
                const isYoutube = url.includes('youtube.com') || url.includes('youtu.be');
                const isTiktok = url.includes('tiktok.com');
                const isInstagram = url.includes('instagram.com');
                const isFacebook = url.includes('facebook.com') || url.includes('fb.watch');
                
                if (isYoutube) {
                    updateLoadingText('Fetching YouTube video information...', 'YouTube videos may take longer to process. Please be patient.');
                } else if (isTiktok) {
                    updateLoadingText('Fetching TikTok video information...', 'Please wait while we analyze the video.');
                } else if (isInstagram) {
                    updateLoadingText('Fetching Instagram content...', 'Instagram content may take longer to process. Please be patient.');
                } else if (isFacebook) {
                    updateLoadingText('Fetching Facebook content...', 'Facebook content may take longer to process. Please be patient.');
                } else {
                    updateLoadingText('Fetching video information...', 'Please wait while we analyze the video.');
                }
                
                loadingOverlay.classList.remove('hidden');
                
                // Log to debug panel
                window.logDebug('Fetching info for URL: ' + url);
                
                fetch('?action=get_info', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `url=${encodeURIComponent(url)}`
                })
                .then(response => response.json())
                .then(data => {
                    // Hide loading overlay and spinner
                    loadingOverlay.classList.add('hidden');
                    urlInputSpinner.classList.add('hidden');
                    
                    if (!data.success) {
                        showError(data.message || 'Failed to get video information');
                        return;
                    }
                    
                    // Log success to debug panel
                    window.logDebug('Successfully retrieved video info: ' + data.info.title);
                    
                    videoInfo = data;
                    displayVideoInfo(data);
                })
                .catch(error => {
                    // Hide loading overlay and spinner
                    loadingOverlay.classList.add('hidden');
                    urlInputSpinner.classList.add('hidden');
                    
                    showError('Error: ' + error.message);
                    window.logDebug('Error fetching video info: ' + error.message);
                });
            }
            
            function displayVideoInfo(data) {
                const info = data.info;
                const isShorts = info.is_shorts || false;
                const isYoutube = info.is_youtube || false;
                const isTiktok = info.is_tiktok || false;
                const isInstagram = info.is_instagram || false;
                const isFacebook = info.is_facebook || false;
                
                // Create platform badge
                let platformBadge = '';
                if (isYoutube) {
                    platformBadge = '<span class="platform-badge youtube-badge">YouTube</span>';
                    if (isShorts) {
                        platformBadge = '<span class="platform-badge youtube-badge">YouTube Shorts</span>';
                    }
                } else if (isTiktok) {
                    platformBadge = '<span class="platform-badge tiktok-badge">TikTok</span>';
                } else if (isInstagram) {
                    platformBadge = '<span class="platform-badge instagram-badge">Instagram</span>';
                } else if (isFacebook) {
                    platformBadge = '<span class="platform-badge facebook-badge">Facebook</span>';
                }
                
                // Display video info
                videoInfoElement.innerHTML = `
                    <div class="video-thumbnail">
                        <img src="${info.thumbnail || '/placeholder.svg?height=200&width=300'}" alt="Thumbnail" onerror="this.src='/placeholder.svg?height=200&width=300'">
                    </div>
                    <div class="video-details">
                        <div class="video-title">
                            ${info.title}
                            ${platformBadge}
                        </div>
                        <div class="video-meta">
                            <span><i class="fas fa-user"></i> ${info.uploader}</span>
                            <span><i class="fas fa-clock"></i> ${info.duration}</span>
                        </div>
                        <div class="video-meta">
                            <span><i class="fas fa-eye"></i> ${formatNumber(info.view_count)} views</span>
                            <span><i class="fas fa-thumbs-up"></i> ${formatNumber(info.like_count)} likes</span>
                        </div>
                        ${isYoutube ? '<div class="alert alert-info" style="margin-top: 10px;"><i class="fas fa-info-circle"></i> YouTube videos may take longer to download due to anti-scraping measures.</div>' : ''}
                        ${isTiktok ? '<div class="alert alert-info" style="margin-top: 10px;"><i class="fas fa-info-circle"></i> TikTok videos may require multiple attempts to download.</div>' : ''}
                        ${isInstagram ? '<div class="alert alert-info" style="margin-top: 10px;"><i class="fas fa-info-circle"></i> Instagram content may require login cookies for some private content.</div>' : ''}
                        ${isFacebook ? '<div class="alert alert-info" style="margin-top: 10px;"><i class="fas fa-info-circle"></i> Facebook content may require login cookies for some private content.</div>' : ''}
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
                const isYoutube = url.includes('youtube.com') || url.includes('youtu.be');
                const isTiktok = url.includes('tiktok.com');
                const isInstagram = url.includes('instagram.com');
                const isFacebook = url.includes('facebook.com') || url.includes('fb.watch');
                
                if (isYoutube) {
                    updateLoadingText('Preparing YouTube download...', 'YouTube videos may take longer to process. Please be patient.');
                } else if (isTiktok) {
                    updateLoadingText('Preparing TikTok download...', 'Please wait while we process your download.');
                } else if (isInstagram) {
                    updateLoadingText('Preparing Instagram download...', 'Instagram content may take longer to process. Please be patient.');
                } else if (isFacebook) {
                    updateLoadingText('Preparing Facebook download...', 'Facebook content may take longer to process. Please be patient.');
                } else {
                    updateLoadingText('Preparing your download...', 'This may take a moment. Please don\'t close this page.');
                }
                
                loadingOverlay.classList.remove('hidden');
                
                // Reset progress bar animation
                const progressBar = document.querySelector('.progress-bar');
                progressBar.style.animation = 'none';
                setTimeout(() => {
                    progressBar.style.animation = 'progress 120s linear forwards';
                }, 10);
                
                // Log to debug panel
                window.logDebug('Starting download: URL=' + url + ', Format=' + format);
                
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
                            
                            // Log success to debug panel
                            window.logDebug('Download successful: ' + data.file.name + ' (' + formatFileSize(data.file.size) + ')');
                            
                            // Complete the download process
                            completeDownload(downloadId, formatElement);
                        } else {
                            // Download failed
                            showError(data.message || 'Download failed');
                            window.logDebug('Download failed: ' + (data.message || 'Unknown error'));
                            completeDownload(downloadId, formatElement);
                        }
                    })
                    .catch(error => {
                        showError('Error: ' + error.message);
                        window.logDebug('Download error: ' + error.message);
                        completeDownload(downloadId, formatElement);
                    });
                
                // Set a timeout for the download
                downloadTimeouts[downloadId] = setTimeout(() => {
                    if (activeDownloads > 0) {
                        showError('Download process timed out. Please try again or try a different format.');
                        window.logDebug('Download timeout for ID: ' + downloadId);
                        completeDownload(downloadId, formatElement);
                    }
                }, 300000); // 5 minutes timeout
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
            
            function formatNumber(num) {
                if (num === 'Unknown' || !num) return 'Unknown';
                return new Intl.NumberFormat().format(num);
            }
            
            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
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
            
            // Test URL function
            window.testUrl = function(url) {
                videoUrlInput.value = url;
                currentUrl = url;
                getVideoInfo(url);
            };
            
            // Run diagnostics function
            window.runDiagnostics = function() {
                window.logDebug('Running diagnostics...');
                
                // Show debug panel
                document.getElementById('debug-panel').classList.remove('hidden');
                
                // Log browser info
                window.logDebug('Browser: ' + navigator.userAgent);
                
                // Check dependencies
                fetch('?action=check_dependencies')
                    .then(response => response.json())
                    .then(data => {
                        window.logDebug('yt-dlp: ' + (data.ytdlp.installed ? 'Installed (' + data.ytdlp.version + ')' : 'Not installed'));
                        window.logDebug('ffmpeg: ' + (data.ffmpeg.installed ? 'Installed' : 'Not installed'));
                        
                        if (!data.ytdlp.installed || !data.ffmpeg.installed) {
                            window.logDebug('ERROR: Missing dependencies. Please install yt-dlp and ffmpeg.');
                            showError('Missing dependencies. Please install yt-dlp and ffmpeg.');
                        } else {
                            window.logDebug('All dependencies are installed.');
                            showSuccess('All dependencies are installed.');
                            
                            // Test directory permissions
                            window.logDebug('Testing directory permissions...');
                            
                            fetch('?action=test_permissions')
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        window.logDebug('Directory permissions: OK');
                                        showSuccess('Directory permissions are correct.');
                                    } else {
                                        window.logDebug('ERROR: Directory permissions issue: ' + data.message);
                                        showError('Directory permissions issue: ' + data.message);
                                    }
                                })
                                .catch(error => {
                                    window.logDebug('ERROR: Failed to test directory permissions: ' + error.message);
                                });
                        }
                    })
                    .catch(error => {
                        window.logDebug('ERROR: Failed to check dependencies: ' + error.message);
                    });
            };
            
            // Update yt-dlp function
            window.updateYtDlp = function() {
                window.logDebug('Updating yt-dlp...');
                
                // Show debug panel
                document.getElementById('debug-panel').classList.remove('hidden');
                
                // Show loading overlay
                updateLoadingText('Updating yt-dlp...', 'This may take a moment. Please wait.');
                loadingOverlay.classList.remove('hidden');
                
                fetch('?action=update_ytdlp')
                    .then(response => response.json())
                    .then(data => {
                        loadingOverlay.classList.add('hidden');
                        
                        if (data.success) {
                            window.logDebug('yt-dlp update successful: ' + data.message);
                            showSuccess('yt-dlp updated successfully. Please refresh the page.');
                        } else {
                            window.logDebug('ERROR: yt-dlp update failed: ' + data.message);
                            showError('Failed to update yt-dlp: ' + data.message);
                        }
                    })
                    .catch(error => {
                        loadingOverlay.classList.add('hidden');
                        window.logDebug('ERROR: Failed to update yt-dlp: ' + error.message);
                        showError('Failed to update yt-dlp: ' + error.message);
                    });
            };
            
            // Press Ctrl+Shift+D to toggle debug mode
            document.addEventListener('keydown', function(e) {
                if (e.ctrlKey && e.shiftKey && e.key === 'D') {
                    window.toggleDebugMode();
                    e.preventDefault();
                }
            });
            
            // Log initial debug info
            window.logDebug('Video Downloader initialized');
            window.logDebug('User Agent: ' + navigator.userAgent);
        });
    </script>
</body>
</html>
