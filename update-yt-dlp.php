<?php
// Script to update yt-dlp to the latest version
// This should be run periodically to ensure compatibility with sites

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>yt-dlp Update Script</h1>";

// Check if yt-dlp is installed
exec('which yt-dlp 2>&1', $which_output, $which_return);
if ($which_return !== 0) {
    echo "<p style='color:red'>yt-dlp not found in PATH</p>";
    
    // Check if it exists in /tmp
    if (file_exists('/tmp/yt-dlp')) {
        echo "<p>Found yt-dlp in /tmp</p>";
        $ytdlp_path = '/tmp/yt-dlp';
    } else {
        echo "<p style='color:red'>yt-dlp not found in /tmp either. Please install it first.</p>";
        exit(1);
    }
} else {
    $ytdlp_path = trim($which_output[0]);
    echo "<p>Found yt-dlp at: $ytdlp_path</p>";
}

// Get current version
exec("$ytdlp_path --version 2>&1", $version_output, $version_return);
if ($version_return === 0) {
    echo "<p>Current yt-dlp version: " . $version_output[0] . "</p>";
} else {
    echo "<p style='color:red'>Could not determine current yt-dlp version.</p>";
}

// Update yt-dlp
echo "<p>Updating yt-dlp...</p>";
exec("$ytdlp_path -U 2>&1", $update_output, $update_return);

echo "<h2>Update Output:</h2>";
echo "<pre>" . implode("\n", $update_output) . "</pre>";

if ($update_return === 0) {
    echo "<p style='color:green'>Update completed successfully.</p>";
    
    // Get new version
    exec("$ytdlp_path --version 2>&1", $new_version_output, $new_version_return);
    if ($new_version_return === 0) {
        echo "<p>New yt-dlp version: " . $new_version_output[0] . "</p>";
    }
} else {
    echo "<p style='color:red'>Update failed with return code: $update_return</p>";
    
    // Try alternative update method
    echo "<p>Trying alternative update method...</p>";
    
    if ($ytdlp_path === '/tmp/yt-dlp') {
        // Download directly to /tmp
        exec('curl -L https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp -o /tmp/yt-dlp.new 2>&1', $alt_output, $alt_return);
        
        if ($alt_return === 0) {
            echo "<p>Downloaded new version to /tmp/yt-dlp.new</p>";
            
            // Make executable
            exec('chmod a+rx /tmp/yt-dlp.new', $chmod_output, $chmod_return);
            
            if ($chmod_return === 0) {
                // Replace old version
                rename('/tmp/yt-dlp.new', '/tmp/yt-dlp');
                echo "<p style='color:green'>Successfully updated yt-dlp in /tmp</p>";
                
                // Get new version
                exec('/tmp/yt-dlp --version 2>&1', $new_version_output, $new_version_return);
                if ($new_version_return === 0) {
                    echo "<p>New yt-dlp version: " . $new_version_output[0] . "</p>";
                }
            } else {
                echo "<p style='color:red'>Failed to make new version executable</p>";
            }
        } else {
            echo "<p style='color:red'>Failed to download new version</p>";
            echo "<pre>" . implode("\n", $alt_output) . "</pre>";
        }
    } else {
        echo "<p style='color:red'>Alternative update not implemented for system-wide installation</p>";
    }
}

echo "<p>Update script completed at " . date('Y-m-d H:i:s') . "</p>";
?>
