<?php
// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering to capture any errors
ob_start();

// Include your configuration
require_once('index.php'); // This will include your config

// Create a simple HTML page
echo "<!DOCTYPE html>
<html>
<head>
    <title>Manual Download Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow: auto; }
        .error { color: red; }
        .success { color: green; }
        .section { margin-bottom: 20px; border-bottom: 1px solid #ccc; padding-bottom: 10px; }
    </style>
</head>
<body>
    <h1>Manual Download Test</h1>";

// Get URL and format from POST
$test_url = isset($_POST['url']) ? $_POST['url'] : '';
$format = isset($_POST['format']) ? $_POST['format'] : 'best';

if (empty($test_url)) {
    echo "<div class='error'>No URL provided</div>";
    echo "</body></html>";
    exit;
}

echo "<div class='section'>
    <h2>Testing URL: {$test_url}</h2>
    <p>Format: {$format}</p>
</div>";

// Determine which platform the URL is from
$is_youtube = (strpos($test_url, 'youtube.com') !== false || strpos($test_url, 'youtu.be') !== false);
$is_facebook = (strpos($test_url, 'facebook.com') !== false || strpos($test_url, 'fb.watch') !== false);
$is_instagram = (strpos($test_url, 'instagram.com') !== false);
$is_tiktok = (strpos($test_url, 'tiktok.com') !== false);

echo "<div class='section'>
    <h2>Platform Detection</h2>
    <p>Platform: " . 
    ($is_youtube ? "YouTube" : 
    ($is_facebook ? "Facebook" : 
    ($is_instagram ? "Instagram" : 
    ($is_tiktok ? "TikTok" : "Other")))) . "</p>
</div>";

// Test video info
echo "<div class='section'>
    <h2>Step 1: Getting Video Info</h2>";

try {
    // Get video info
    echo "<p>Calling get_video_info()...</p>";
    
    // For YouTube, use the specialized function
    if ($is_youtube && function_exists('get_youtube_video_info')) {
        echo "<p>Using YouTube-specific function...</p>";
        $video_info = get_youtube_video_info($test_url, $config);
    } else {
        $video_info = get_video_info($test_url, $config);
    }

    if ($video_info['success']) {
        echo "<p class='success'>Successfully retrieved video info</p>";
        echo "<pre>" . print_r($video_info['info'], true) . "</pre>";
    } else {
        echo "<p class='error'>Failed to retrieve video info: " . $video_info['message'] . "</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>Exception: " . $e->getMessage() . "</p>";
}

echo "</div>";

// Test download
echo "<div class='section'>
    <h2>Step 2: Testing Download</h2>";

try {
    // Create a temporary file
    $temp_file = $config['temp_dir'] . 'manual_test_' . uniqid() . '.mp4';
    echo "<p>Temporary file: $temp_file</p>";
    
    // Get a random user agent
    $user_agents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15'
    ];
    $user_agent = $user_agents[array_rand($user_agents)];
    
    // For YouTube, use specialized approaches
    if ($is_youtube) {
        echo "<p>Using YouTube-specific approaches...</p>";
        
        // Approach 1: Try using youtube-dl with specific options
        echo "<h3>Approach 1: Using specific YouTube options</h3>";
        $cmd1 = "{$config['ytdlp_path']} --no-check-certificate --no-warnings " .
               "--extractor-args \"youtube:player_client=android\" " .
               "--user-agent " . escapeshellarg($user_agent) . " " .
               "--geo-bypass --geo-bypass-country US " .
               "--output \"$temp_file\" " . escapeshellarg($test_url) . " 2>&1";
        
        echo "<p>Command: " . htmlspecialchars($cmd1) . "</p>";
        
        exec($cmd1, $output1, $return_var1);
        
        echo "<p>Command output:</p>";
        echo "<pre>" . implode("\n", $output1) . "</pre>";
        echo "<p>Return code: $return_var1</p>";
        
        if ($return_var1 === 0 && file_exists($temp_file) && filesize($temp_file) > 1000) {
            echo "<p class='success'>Approach 1 successful! File size: " . filesize($temp_file) . " bytes</p>";
            
            // Clean up
            unlink($temp_file);
            echo "<p>Test file deleted</p>";
        } else {
            echo "<p class='error'>Approach 1 failed</p>";
            
            // Approach 2: Try using a different format string
            echo "<h3>Approach 2: Using different format string</h3>";
            $cmd2 = "{$config['ytdlp_path']} --no-check-certificate --no-warnings " .
                   "-f \"best[ext=mp4]/best\" " .
                   "--user-agent " . escapeshellarg($user_agent) . " " .
                   "--output \"$temp_file\" " . escapeshellarg($test_url) . " 2>&1";
            
            echo "<p>Command: " . htmlspecialchars($cmd2) . "</p>";
            
            exec($cmd2, $output2, $return_var2);
            
            echo "<p>Command output:</p>";
            echo "<pre>" . implode("\n", $output2) . "</pre>";
            echo "<p>Return code: $return_var2</p>";
            
            if ($return_var2 === 0 && file_exists($temp_file) && filesize($temp_file) > 1000) {
                echo "<p class='success'>Approach 2 successful! File size: " . filesize($temp_file) . " bytes</p>";
                
                // Clean up
                unlink($temp_file);
                echo "<p>Test file deleted</p>";
            } else {
                echo "<p class='error'>Approach 2 failed</p>";
                
                // Approach 3: Try using a direct API call
                echo "<h3>Approach 3: Using direct URL extraction</h3>";
                $cmd3 = "{$config['ytdlp_path']} --no-check-certificate --no-warnings " .
                       "--get-url " . escapeshellarg($test_url) . " 2>&1";
                
                echo "<p>Command: " . htmlspecialchars($cmd3) . "</p>";
                
                exec($cmd3, $output3, $return_var3);
                
                echo "<p>Command output:</p>";
                echo "<pre>" . implode("\n", $output3) . "</pre>";
                echo "<p>Return code: $return_var3</p>";
                
                if ($return_var3 === 0 && !empty($output3[0]) && filter_var($output3[0], FILTER_VALIDATE_URL)) {
                    $direct_url = $output3[0];
                    echo "<p class='success'>Got direct URL: " . htmlspecialchars($direct_url) . "</p>";
                    
                    // Try to download using curl
                    if (function_exists('curl_init')) {
                        echo "<p>Downloading with curl...</p>";
                        $ch = curl_init($direct_url);
                        $fp = fopen($temp_file, 'wb');
                        curl_setopt($ch, CURLOPT_FILE, $fp);
                        curl_setopt($ch, CURLOPT_HEADER, 0);
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
                        curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
                        curl_exec($ch);
                        $curl_error = curl_error($ch);
                        curl_close($ch);
                        fclose($fp);
                        
                        if (empty($curl_error) && file_exists($temp_file) && filesize($temp_file) > 1000) {
                            echo "<p class='success'>Curl download successful! File size: " . filesize($temp_file) . " bytes</p>";
                            
                            // Clean up
                            unlink($temp_file);
                            echo "<p>Test file deleted</p>";
                        } else {
                            echo "<p class='error'>Curl download failed: $curl_error</p>";
                            
                            if (file_exists($temp_file)) {
                                echo "<p>File exists but size is: " . filesize($temp_file) . " bytes</p>";
                                unlink($temp_file);
                                echo "<p>Test file deleted</p>";
                            }
                        }
                    } else {
                        echo "<p class='error'>Curl not available</p>";
                    }
                } else {
                    echo "<p class='error'>Failed to get direct URL</p>";
                }
            }
        }
    } else {
        // For non-YouTube videos, use a simpler approach
        echo "<p>Using standard download approach for non-YouTube video...</p>";
        
        $cmd = "{$config['ytdlp_path']} --no-check-certificate --no-warnings " .
               "--user-agent " . escapeshellarg($user_agent) . " " .
               "--output \"$temp_file\" " . escapeshellarg($test_url) . " 2>&1";
        
        echo "<p>Command: " . htmlspecialchars($cmd) . "</p>";
        
        exec($cmd, $output, $return_var);
        
        echo "<p>Command output:</p>";
        echo "<pre>" . implode("\n", $output) . "</pre>";
        echo "<p>Return code: $return_var</p>";
        
        if ($return_var === 0 && file_exists($temp_file) && filesize($temp_file) > 1000) {
            echo "<p class='success'>Download successful! File size: " . filesize($temp_file) . " bytes</p>";
            
            // Clean up
            unlink($temp_file);
            echo "<p>Test file deleted</p>";
        } else {
            echo "<p class='error'>Download failed</p>";
            
            if (file_exists($temp_file)) {
                echo "<p>File exists but size is: " . filesize($temp_file) . " bytes</p>";
                unlink($temp_file);
                echo "<p>Test file deleted</p>";
            }
        }
    }
} catch (Exception $e) {
    echo "<p class='error'>Exception: " . $e->getMessage() . "</p>";
}

echo "</div>";

// Check for any buffered errors
$buffer = ob_get_clean();
if (preg_match('/<b>.*?<\/b>/', $buffer)) {
    echo "<div class='section'>
        <h2>PHP Errors Detected</h2>
        <div class='error'>
            " . $buffer . "
        </div>
    </div>";
} else {
    echo $buffer;
}

echo "<div class='section'>
    <h2>Recommendations</h2>
    <ul>
        <li>Check the logs directory for detailed error messages</li>
        <li>Try a different YouTube video URL</li>
        <li>Consider using a different hosting provider if render.com is blocking YouTube downloads</li>
        <li>Try updating yt-dlp to the latest version</li>
    </ul>
</div>

<p><a href='debug-download.php?url=" . urlencode($test_url) . "&format=" . urlencode($format) . "'>Go back to Debug Page</a></p>

</body>
</html>";
?>
