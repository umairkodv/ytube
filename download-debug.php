<?php
// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include your configuration
require_once('index.php'); // This will include your config

// Create a simple HTML page
echo "<!DOCTYPE html>
<html>
<head>
    <title>Download Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow: auto; }
        .error { color: red; }
        .success { color: green; }
        .section { margin-bottom: 20px; border-bottom: 1px solid #ccc; padding-bottom: 10px; }
    </style>
</head>
<body>
    <h1>Download Debug Tool</h1>";

// Test URL - you can change this to test different platforms
$test_url = isset($_GET['url']) ? $_GET['url'] : 'https://www.tiktok.com/@tiktok/video/7106594312292453675';
$format = isset($_GET['format']) ? $_GET['format'] : 'best';

echo "<div class='section'>
    <h2>Testing URL: {$test_url}</h2>
    <p>Format: {$format}</p>
</div>";

// Check environment
echo "<div class='section'>
    <h2>Environment Check</h2>";

// Check yt-dlp
exec($config['ytdlp_path'] . ' --version 2>&1', $ytdlp_output, $ytdlp_return);
if ($ytdlp_return === 0) {
    echo "<p class='success'>yt-dlp found: " . $ytdlp_output[0] . "</p>";
} else {
    echo "<p class='error'>yt-dlp not found or not executable. Error code: {$ytdlp_return}</p>";
    echo "<pre>" . implode("\n", $ytdlp_output) . "</pre>";
}

// Check ffmpeg
exec($config['ffmpeg_path'] . ' -version 2>&1', $ffmpeg_output, $ffmpeg_return);
if ($ffmpeg_return === 0) {
    echo "<p class='success'>ffmpeg found: " . (isset($ffmpeg_output[0]) ? $ffmpeg_output[0] : "Installed") . "</p>";
} else {
    echo "<p class='error'>ffmpeg not found or not executable. Error code: {$ffmpeg_return}</p>";
    echo "<pre>" . implode("\n", $ffmpeg_output) . "</pre>";
}

// Check temp directory
if (is_dir($config['temp_dir']) && is_writable($config['temp_dir'])) {
    echo "<p class='success'>Temp directory exists and is writable: {$config['temp_dir']}</p>";
} else {
    echo "<p class='error'>Temp directory issue: " . (is_dir($config['temp_dir']) ? "Not writable" : "Doesn't exist") . "</p>";
    echo "<p>Attempting to create/fix temp directory...</p>";
    
    if (!is_dir($config['temp_dir'])) {
        if (mkdir($config['temp_dir'], 0777, true)) {
            echo "<p class='success'>Created temp directory</p>";
        } else {
            echo "<p class='error'>Failed to create temp directory</p>";
        }
    } else {
        if (chmod($config['temp_dir'], 0777)) {
            echo "<p class='success'>Fixed temp directory permissions</p>";
        } else {
            echo "<p class='error'>Failed to fix temp directory permissions</p>";
        }
    }
}

// Check cache directory
$cache_dir = '/tmp/yt-dlp-cache/';
if (is_dir($cache_dir) && is_writable($cache_dir)) {
    echo "<p class='success'>Cache directory exists and is writable: {$cache_dir}</p>";
} else {
    echo "<p class='error'>Cache directory issue: " . (is_dir($cache_dir) ? "Not writable" : "Doesn't exist") . "</p>";
    echo "<p>Attempting to create/fix cache directory...</p>";
    
    if (!is_dir($cache_dir)) {
        if (mkdir($cache_dir, 0777, true)) {
            echo "<p class='success'>Created cache directory</p>";
        } else {
            echo "<p class='error'>Failed to create cache directory</p>";
        }
    } else {
        if (chmod($cache_dir, 0777)) {
            echo "<p class='success'>Fixed cache directory permissions</p>";
        } else {
            echo "<p class='error'>Failed to fix cache directory permissions</p>";
        }
    }
}

echo "</div>";

// Test video info
echo "<div class='section'>
    <h2>Testing Video Info</h2>";

// Determine which platform the URL is from
$is_youtube = (strpos($test_url, 'youtube.com') !== false || strpos($test_url, 'youtu.be') !== false);
$is_facebook = (strpos($test_url, 'facebook.com') !== false || strpos($test_url, 'fb.watch') !== false);
$is_instagram = (strpos($test_url, 'instagram.com') !== false);
$is_tiktok = (strpos($test_url, 'tiktok.com') !== false);

echo "<p>Platform detection: " . 
    ($is_youtube ? "YouTube" : 
    ($is_facebook ? "Facebook" : 
    ($is_instagram ? "Instagram" : 
    ($is_tiktok ? "TikTok" : "Other")))) . "</p>";

// Get video info
$video_info = get_video_info($test_url, $config);

if ($video_info['success']) {
    echo "<p class='success'>Successfully retrieved video info</p>";
    echo "<pre>" . print_r($video_info['info'], true) . "</pre>";
} else {
    echo "<p class='error'>Failed to retrieve video info: " . $video_info['message'] . "</p>";
}

echo "</div>";

// Test direct download command
echo "<div class='section'>
    <h2>Testing Direct Download Command</h2>";

// Create a test command
$ytdlp_path = $config['ytdlp_path'];
$escaped_url = escapeshellarg($test_url);
$temp_file = $config['temp_dir'] . 'test_download_' . time() . '.mp4';
$format_str = $config['format_options'][$format]['format'];

// Get a random user agent
$user_agents = [
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15'
];
$user_agent = $user_agents[array_rand($user_agents)];
$user_agent_arg = escapeshellarg($user_agent);

// Build a simple test command
$test_cmd = "$ytdlp_path -f $format_str --merge-output-format mp4 " .
           "--no-check-certificate --no-warnings --ignore-errors " .
           "--user-agent $user_agent_arg " .
           "-o \"$temp_file\" $escaped_url";

echo "<p>Test command:</p>";
echo "<pre>{$test_cmd}</pre>";

echo "<p>Executing test command...</p>";
exec($test_cmd . " 2>&1", $cmd_output, $cmd_return);

echo "<p>Command output:</p>";
echo "<pre>" . implode("\n", $cmd_output) . "</pre>";

if ($cmd_return === 0 && file_exists($temp_file) && filesize($temp_file) > 1000) {
    echo "<p class='success'>Test download successful! File size: " . filesize($temp_file) . " bytes</p>";
    
    // Clean up
    unlink($temp_file);
    echo "<p>Test file deleted</p>";
} else {
    echo "<p class='error'>Test download failed. Return code: {$cmd_return}</p>";
    
    if (file_exists($temp_file)) {
        echo "<p>File exists but size is: " . filesize($temp_file) . " bytes</p>";
        unlink($temp_file);
        echo "<p>Test file deleted</p>";
    } else {
        echo "<p>No file was created</p>";
    }
}

echo "</div>";

// Test simplified download
echo "<div class='section'>
    <h2>Testing Simplified Download</h2>";

// Build a simpler test command
$simple_cmd = "$ytdlp_path --no-check-certificate -o \"$temp_file\" $escaped_url";

echo "<p>Simple command:</p>";
echo "<pre>{$simple_cmd}</pre>";

echo "<p>Executing simple command...</p>";
exec($simple_cmd . " 2>&1", $simple_output, $simple_return);

echo "<p>Command output:</p>";
echo "<pre>" . implode("\n", $simple_output) . "</pre>";

if ($simple_return === 0 && file_exists($temp_file) && filesize($temp_file) > 1000) {
    echo "<p class='success'>Simple download successful! File size: " . filesize($temp_file) . " bytes</p>";
    
    // Clean up
    unlink($temp_file);
    echo "<p>Test file deleted</p>";
} else {
    echo "<p class='error'>Simple download failed. Return code: {$simple_return}</p>";
    
    if (file_exists($temp_file)) {
        echo "<p>File exists but size is: " . filesize($temp_file) . " bytes</p>";
        unlink($temp_file);
        echo "<p>Test file deleted</p>";
    } else {
        echo "<p>No file was created</p>";
    }
}

echo "</div>";

// Recommendations
echo "<div class='section'>
    <h2>Recommendations</h2>
    <ul>";

if ($ytdlp_return !== 0) {
    echo "<li class='error'>Fix yt-dlp installation or path</li>";
}

if ($ffmpeg_return !== 0) {
    echo "<li class='error'>Fix ffmpeg installation or path</li>";
}

if (!is_dir($config['temp_dir']) || !is_writable($config['temp_dir'])) {
    echo "<li class='error'>Fix temp directory permissions</li>";
}

if (!is_dir($cache_dir) || !is_writable($cache_dir)) {
    echo "<li class='error'>Fix cache directory permissions</li>";
}

if ($cmd_return !== 0 && $simple_return !== 0) {
    echo "<li class='error'>Both download methods failed. Check if render.com is blocking external processes or downloads</li>";
    echo "<li>Try using a different format option</li>";
    echo "<li>Try updating yt-dlp to the latest version</li>";
    echo "<li>Check if render.com has any outbound connection restrictions</li>";
}

echo "</ul>
</div>

<div class='section'>
    <h2>Test Different URL</h2>
    <form method='get'>
        <input type='text' name='url' placeholder='Enter URL to test' style='width: 400px;'>
        <select name='format'>
            <option value='best'>Best Quality</option>
            <option value='medium'>Medium Quality</option>
            <option value='low'>Low Quality</option>
            <option value='audio'>Audio Only</option>
        </select>
        <button type='submit'>Test</button>
    </form>
</div>

</body>
</html>";
?>
