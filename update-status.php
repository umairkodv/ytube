<?php
// Script to update yt-dlp to the latest version
// This should be run periodically to ensure compatibility with sites

// Check if yt-dlp is installed
exec('which yt-dlp', $output, $return_var);
if ($return_var !== 0) {
    echo "Error: yt-dlp is not installed.\n";
    exit(1);
}

// Get current version
exec('yt-dlp --version', $version_output, $version_return);
if ($version_return === 0) {
    echo "Current yt-dlp version: " . $version_output[0] . "\n";
} else {
    echo "Could not determine current yt-dlp version.\n";
}

// Update yt-dlp
echo "Updating yt-dlp...\n";
exec('yt-dlp -U', $update_output, $update_return);

if ($update_return === 0) {
    echo "Update completed successfully.\n";
    echo implode("\n", $update_output) . "\n";
    
    // Get new version
    exec('yt-dlp --version', $new_version_output, $new_version_return);
    if ($new_version_return === 0) {
        echo "New yt-dlp version: " . $new_version_output[0] . "\n";
    }
} else {
    echo "Update failed.\n";
    echo implode("\n", $update_output) . "\n";
}
