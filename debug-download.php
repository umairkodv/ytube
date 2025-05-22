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
    <title>Download Debug - Detailed</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow: auto; }
        .error { color: red; }
        .success { color: green; }
        .section { margin-bottom: 20px; border-bottom: 1px solid #ccc; padding-bottom: 10px; }
        .code { font-family: monospace; background: #f0f0f0; padding: 2px 4px; }
    </style>
</head>
<body>
    <h1>Detailed Download Debug Tool</h1>";

// Test URL - you can change this to test different platforms
$test_url = isset($_GET['url']) ? $_GET['url'] : 'https://www.tiktok.com/@tiktok/video/7106594312292453675';
$format = isset($_GET['format']) ? $_GET['format'] : 'best';

echo "<div class='section'>
    <h2>Testing URL: {$test_url}</h2>
    <p>Format: {$format}</p>
</div>";

// Check included files
echo "<div class='section'>
    <h2>Checking Required Files</h2>";

$required_files = [
    'platform-specific.php',
    'youtube-bypass.php',
    'proxy-config.php',
    'fix-download-updated.php',
    'error-handler.php',
    'directory-helper.php'
];

foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "<p class='success'>✓ File exists: {$file}</p>";
    } else {
        echo "<p class='error'>✗ File missing: {$file}</p>";
    }
}

echo "</div>";

// Check directories
echo "<div class='section'>
    <h2>Checking Directories</h2>";

$directories = [
    $config['temp_dir'],
    $config['log_dir'],
    '/tmp/yt-dlp-cache'
];

foreach ($directories as $dir) {
    if (is_dir($dir)) {
        echo "<p>Directory exists: {$dir}</p>";
        if (is_writable($dir)) {
            echo "<p class='success'>✓ Directory is writable: {$dir}</p>";
        } else {
            echo "<p class='error'>✗ Directory is not writable: {$dir}</p>";
            echo "<p>Attempting to create a test file...</p>";
            $test_file = $dir . '/test_' . uniqid() . '.txt';
            $result = @file_put_contents($test_file, 'Test');
            if ($result !== false) {
                echo "<p class='success'>✓ Successfully wrote test file despite permissions</p>";
                @unlink($test_file);
            } else {
                echo "<p class='error'>✗ Could not write to directory</p>";
            }
        }
    } else {
        echo "<p class='error'>✗ Directory does not exist: {$dir}</p>";
    }
}

echo "</div>";

// Test video info with detailed error handling
echo "<div class='section'>
    <h2>Testing Video Info with Detailed Logging</h2>";

try {
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

    // Get video info with detailed error handling
    echo "<p>Calling get_video_info()...</p>";
    
    // For YouTube, test the specialized function
    if ($is_youtube) {
        echo "<p>Testing YouTube-specific function...</p>";
        
        // Check if the function exists
        if (function_exists('get_youtube_video_info')) {
            echo "<p class='success'>✓ Function get_youtube_video_info() exists</p>";
            
            try {
                $youtube_info = get_youtube_video_info($test_url, $config);
                echo "<p>YouTube info result: " . ($youtube_info['success'] ? 'Success' : 'Failed') . "</p>";
                if (!$youtube_info['success']) {
                    echo "<p class='error'>Error message: " . $youtube_info['message'] . "</p>";
                } else {
                    echo "<pre>" . print_r($youtube_info['info'], true) . "</pre>";
                }
            } catch (Exception $e) {
                echo "<p class='error'>Exception in get_youtube_video_info(): " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p class='error'>✗ Function get_youtube_video_info() does not exist</p>";
        }
    }
    
    // Test the general video info function
    $video_info = get_video_info($test_url, $config);

    if ($video_info['success']) {
        echo "<p class='success'>✓ Successfully retrieved video info</p>";
        echo "<pre>" . print_r($video_info['info'], true) . "</pre>";
    } else {
        echo "<p class='error'>✗ Failed to retrieve video info: " . $video_info['message'] . "</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>Exception: " . $e->getMessage() . "</p>";
}

echo "</div>";

// Test download function with detailed error handling
echo "<div class='section'>
    <h2>Testing Download Function with Detailed Logging</h2>";

try {
    // For YouTube, test the specialized bypass function
    if ($is_youtube) {
        echo "<p>Testing YouTube bypass function...</p>";
        
        // Check if the function exists
        if (function_exists('bypass_youtube_rate_limit')) {
            echo "<p class='success'>✓ Function bypass_youtube_rate_limit() exists</p>";
            
            // We can't actually call it here as it would exit the script
            echo "<p>Function exists but won't be called directly (would exit script)</p>";
            
            // Instead, let's examine the function code
            $function_exists = function_exists('bypass_youtube_rate_limit');
            echo "<p>Function exists: " . ($function_exists ? 'Yes' : 'No') . "</p>";
            
            if ($function_exists) {
                $reflection = new ReflectionFunction('bypass_youtube_rate_limit');
                $start_line = $reflection->getStartLine();
                $end_line = $reflection->getEndLine();
                echo "<p>Function defined in file: " . $reflection->getFileName() . "</p>";
                echo "<p>Function spans lines: $start_line to $end_line</p>";
            }
        } else {
            echo "<p class='error'>✗ Function bypass_youtube_rate_limit() does not exist</p>";
            echo "<p>Check if youtube-bypass.php is properly included</p>";
        }
    }
    
    // Test the fix_download_process function
    echo "<p>Testing fix_download_process function...</p>";
    
    // Check if the function exists
    if (function_exists('fix_download_process')) {
        echo "<p class='success'>✓ Function fix_download_process() exists</p>";
        
        // We can't actually call it here as it would exit the script
        echo "<p>Function exists but won't be called directly (would exit script)</p>";
        
        // Instead, let's examine the function code
        $reflection = new ReflectionFunction('fix_download_process');
        $start_line = $reflection->getStartLine();
        $end_line = $reflection->getEndLine();
        echo "<p>Function defined in file: " . $reflection->getFileName() . "</p>";
        echo "<p>Function spans lines: $start_line to $end_line</p>";
    } else {
        echo "<p class='error'>✗ Function fix_download_process() does not exist</p>";
        echo "<p>Check if fix-download-updated.php is properly included</p>";
    }
    
    // Test a direct yt-dlp command to see if it works
    echo "<p>Testing direct yt-dlp command...</p>";
    
    $ytdlp_path = $config['ytdlp_path'];
    $escaped_url = escapeshellarg($test_url);
    $test_cmd = "$ytdlp_path --no-check-certificate --version";
    
    echo "<p>Command: $test_cmd</p>";
    
    exec($test_cmd . " 2>&1", $cmd_output, $cmd_return);
    
    echo "<p>Command output:</p>";
    echo "<pre>" . implode("\n", $cmd_output) . "</pre>";
    
    if ($cmd_return === 0) {
        echo "<p class='success'>✓ yt-dlp command executed successfully</p>";
    } else {
        echo "<p class='error'>✗ yt-dlp command failed with return code: $cmd_return</p>";
    }
    
    // Test a simple download command
    echo "<p>Testing simple download command...</p>";
    
    $temp_file = $config['temp_dir'] . 'test_' . uniqid() . '.info.json';
    $simple_cmd = "$ytdlp_path --no-check-certificate -J $escaped_url > \"$temp_file\" 2>&1";
    
    echo "<p>Command: $simple_cmd</p>";
    
    exec($simple_cmd, $simple_output, $simple_return);
    
    echo "<p>Command return code: $simple_return</p>";
    
    if (file_exists($temp_file)) {
        $file_size = filesize($temp_file);
        echo "<p>Output file exists, size: $file_size bytes</p>";
        
        if ($file_size > 0) {
            $file_content = file_get_contents($temp_file);
            $first_100_chars = substr($file_content, 0, 100) . '...';
            echo "<p>First 100 chars of file: " . htmlspecialchars($first_100_chars) . "</p>";
            
            // Try to parse as JSON
            $json_data = json_decode($file_content, true);
            if ($json_data !== null) {
                echo "<p class='success'>✓ File contains valid JSON</p>";
            } else {
                echo "<p class='error'>✗ File does not contain valid JSON: " . json_last_error_msg() . "</p>";
                echo "<p>First 500 chars of file:</p>";
                echo "<pre>" . htmlspecialchars(substr($file_content, 0, 500)) . "...</pre>";
            }
        } else {
            echo "<p class='error'>✗ Output file is empty</p>";
        }
        
        // Clean up
        unlink($temp_file);
    } else {
        echo "<p class='error'>✗ Output file was not created</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>Exception: " . $e->getMessage() . "</p>";
}

echo "</div>";

// Recommendations
echo "<div class='section'>
    <h2>Recommendations</h2>
    <ul>";

echo "<li>Check the logs directory for detailed error messages: <code>{$config['log_dir']}debug_log.txt</code></li>";
echo "<li>Make sure all required files are properly included and have the correct permissions</li>";
echo "<li>Try running the script with a different YouTube video URL</li>";
echo "<li>Consider adding more detailed error logging to the download process</li>";
echo "<li>Check if render.com has any restrictions on running external commands</li>";

echo "</ul>
</div>

<div class='section'>
    <h2>Manual Download Test</h2>
    <p>This will attempt to download the video directly using the bypass function and show detailed output.</p>
    <form method='post' action='manual-download-test.php'>
        <input type='hidden' name='url' value='" . htmlspecialchars($test_url) . "'>
        <input type='hidden' name='format' value='" . htmlspecialchars($format) . "'>
        <button type='submit'>Run Manual Download Test</button>
    </form>
</div>

</body>
</html>";
?>
