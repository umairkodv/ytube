<?php
// This script is designed to fix common issues with yt-dlp on render.com
// Run this script once after deploying to render.com

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Render.com Environment Setup for Video Downloader</h1>";

// Check if running on render.com
$is_render = isset($_ENV['RENDER']) || isset($_SERVER['RENDER_EXTERNAL_URL']);
echo "<p>Running on Render.com: " . ($is_render ? "Yes" : "No") . "</p>";

// Create necessary directories
$dirs = ['temp', 'logs', '/tmp/yt-dlp-cache'];
foreach ($dirs as $dir) {
    if (!file_exists($dir)) {
        if (mkdir($dir, 0777, true)) {
            echo "<p>Created directory: $dir</p>";
        } else {
            echo "<p style='color:red'>Failed to create directory: $dir</p>";
        }
    } else {
        echo "<p>Directory already exists: $dir</p>";
    }
    
    // Set permissions
    chmod($dir, 0777);
    echo "<p>Set permissions for $dir to 0777</p>";
}

// Check for yt-dlp
echo "<h2>Checking for yt-dlp</h2>";
exec('which yt-dlp 2>&1', $output, $return_var);
if ($return_var !== 0) {
    echo "<p style='color:red'>yt-dlp not found in PATH</p>";
    
    // Try to install yt-dlp
    echo "<p>Attempting to install yt-dlp...</p>";
    exec('curl -L https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp -o /usr/local/bin/yt-dlp 2>&1', $install_output, $install_return);
    echo "<pre>" . implode("\n", $install_output) . "</pre>";
    
    if ($install_return !== 0) {
        echo "<p style='color:red'>Failed to download yt-dlp. Trying alternate location...</p>";
        exec('curl -L https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp -o /tmp/yt-dlp 2>&1', $alt_output, $alt_return);
        echo "<pre>" . implode("\n", $alt_output) . "</pre>";
        
        if ($alt_return === 0) {
            echo "<p>Downloaded yt-dlp to /tmp/yt-dlp</p>";
            exec('chmod a+rx /tmp/yt-dlp', $chmod_output, $chmod_return);
            if ($chmod_return === 0) {
                echo "<p>Made yt-dlp executable</p>";
                echo "<p style='color:green'>You should update your config to use '/tmp/yt-dlp' as the path</p>";
            } else {
                echo "<p style='color:red'>Failed to make yt-dlp executable</p>";
            }
        } else {
            echo "<p style='color:red'>Failed to download yt-dlp to alternate location</p>";
        }
    } else {
        echo "<p>Downloaded yt-dlp to /usr/local/bin/yt-dlp</p>";
        exec('chmod a+rx /usr/local/bin/yt-dlp', $chmod_output, $chmod_return);
        if ($chmod_return === 0) {
            echo "<p>Made yt-dlp executable</p>";
        } else {
            echo "<p style='color:red'>Failed to make yt-dlp executable</p>";
        }
    }
} else {
    echo "<p style='color:green'>yt-dlp found: " . $output[0] . "</p>";
    
    // Check version
    exec('yt-dlp --version', $version_output, $version_return);
    if ($version_return === 0) {
        echo "<p>yt-dlp version: " . $version_output[0] . "</p>";
    }
}

// Check for ffmpeg
echo "<h2>Checking for ffmpeg</h2>";
exec('which ffmpeg 2>&1', $ffmpeg_output, $ffmpeg_return);
if ($ffmpeg_return !== 0) {
    echo "<p style='color:red'>ffmpeg not found in PATH</p>";
    echo "<p>On Render.com, you need to add ffmpeg via the environment setup</p>";
} else {
    echo "<p style='color:green'>ffmpeg found: " . $ffmpeg_output[0] . "</p>";
    
    // Check version
    exec('ffmpeg -version', $ffmpeg_version_output, $ffmpeg_version_return);
    if ($ffmpeg_version_return === 0) {
        echo "<p>ffmpeg version: " . $ffmpeg_version_output[0] . "</p>";
    }
}

// Test yt-dlp with a simple command
echo "<h2>Testing yt-dlp</h2>";
$test_cmd = 'yt-dlp --version';
echo "<p>Running: $test_cmd</p>";
exec($test_cmd . ' 2>&1', $test_output, $test_return);
echo "<pre>" . implode("\n", $test_output) . "</pre>";
if ($test_return === 0) {
    echo "<p style='color:green'>yt-dlp test successful</p>";
} else {
    echo "<p style='color:red'>yt-dlp test failed</p>";
}

// Test environment variables
echo "<h2>Environment Variables</h2>";
echo "<p>Setting XDG_CACHE_HOME to /tmp/yt-dlp-cache</p>";
putenv("XDG_CACHE_HOME=/tmp/yt-dlp-cache");
echo "<p>Current value: " . getenv("XDG_CACHE_HOME") . "</p>";

// Test file permissions
echo "<h2>Testing File Permissions</h2>";
$test_file = 'temp/test_' . uniqid() . '.txt';
$test_content = 'Test content: ' . date('Y-m-d H:i:s');
if (file_put_contents($test_file, $test_content)) {
    echo "<p style='color:green'>Successfully wrote to test file: $test_file</p>";
    
    $read_content = file_get_contents($test_file);
    if ($read_content === $test_content) {
        echo "<p style='color:green'>Successfully read from test file</p>";
    } else {
        echo "<p style='color:red'>Failed to read correct content from test file</p>";
    }
    
    if (unlink($test_file)) {
        echo "<p style='color:green'>Successfully deleted test file</p>";
    } else {
        echo "<p style='color:red'>Failed to delete test file</p>";
    }
} else {
    echo "<p style='color:red'>Failed to write to test file</p>";
}

// Recommendations
echo "<h2>Recommendations</h2>";
echo "<ol>";
echo "<li>Make sure your temp and logs directories have write permissions (chmod 0777)</li>";
echo "<li>Set 'ytdlp_path' in your config to the correct path shown above</li>";
echo "<li>Add the following to your PHP code to set the cache directory:<br><code>putenv(\"XDG_CACHE_HOME=/tmp/yt-dlp-cache\");</code></li>";
echo "<li>For YouTube videos, use the '--extractor-args \"youtube:player_client=android\"' option</li>";
echo "<li>For Facebook and Instagram, use cookies or user agents to simulate a browser</li>";
echo "<li>Consider using a proxy service if render.com's IP is blocked by platforms</li>";
echo "</ol>";

echo "<h2>Suggested Configuration Updates</h2>";
echo "<pre>";
echo '$config = [
    \'temp_dir\' => \'temp/\',
    \'log_dir\' => \'logs/\',
    \'ytdlp_path\' => \'' . (file_exists('/tmp/yt-dlp') ? '/tmp/yt-dlp' : 'yt-dlp') . '\',
    \'ffmpeg_path\' => \'ffmpeg\',
    // Other config options...
];

// Create cache directory with proper permissions
$cache_dir = \'/tmp/yt-dlp-cache/\';
if (!file_exists($cache_dir)) {
    mkdir($cache_dir, 0777, true);
}
putenv("XDG_CACHE_HOME={$cache_dir}");';
echo "</pre>";

echo "<p>Script completed at " . date('Y-m-d H:i:s') . "</p>";
?>
