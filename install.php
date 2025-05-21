<?php
// Installation script for the Enhanced Video Downloader

// Check PHP version
$required_php_version = '7.4.0';
if (version_compare(PHP_VERSION, $required_php_version, '<')) {
    die("Error: PHP version $required_php_version or higher is required. Your version: " . PHP_VERSION . "\n");
}

// Check for required PHP extensions
$required_extensions = ['curl', 'json', 'session', 'fileinfo'];
$missing_extensions = [];

foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $missing_extensions[] = $ext;
    }
}

if (!empty($missing_extensions)) {
    die("Error: The following PHP extensions are required but missing: " . implode(', ', $missing_extensions) . "\n");
}

// Create necessary directories
$directories = ['downloads', 'temp', 'logs'];
foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "Created directory: $dir\n";
        } else {
            die("Error: Failed to create directory: $dir\n");
        }
    } else {
        echo "Directory already exists: $dir\n";
    }
}

// Set proper permissions
foreach ($directories as $dir) {
    if (!is_writable($dir)) {
        if (chmod($dir, 0755)) {
            echo "Set permissions for: $dir\n";
        } else {
            echo "Warning: Could not set permissions for: $dir\n";
        }
    }
}

// Check for yt-dlp
echo "Checking for yt-dlp...\n";

// Try to determine OS
$os = php_uname('s');
$is_windows = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');

if ($is_windows) {
    echo "Detected Windows system.\n";
    echo "Please make sure yt-dlp is installed in C:\\Windows\\yt-dlp\\ or update the path in index.php.\n";
    echo "Download yt-dlp from: https://github.com/yt-dlp/yt-dlp/releases\n\n";
    
    echo "Also make sure FFmpeg is installed in the same directory.\n";
    echo "Download FFmpeg from: https://www.gyan.dev/ffmpeg/builds/ (get the essentials build)\n\n";
    
    echo "After installing both, update the paths in index.php if needed:\n";
    echo "\$config = [\n";
    echo "    ...\n";
    echo "    'ytdlp_path' => 'C:\\\\Windows\\\\yt-dlp\\\\yt-dlp.exe',\n";
    echo "    'ffmpeg_path' => 'C:\\\\Windows\\\\yt-dlp\\\\ffmpeg.exe',\n";
    echo "    ...\n";
    echo "];\n";
} elseif (stripos($os, 'linux') !== false) {
    echo "Detected Linux system.\n";
    echo "Please install yt-dlp using your package manager or run:\n";
    echo "sudo curl -L https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp -o /usr/local/bin/yt-dlp\n";
    echo "sudo chmod a+rx /usr/local/bin/yt-dlp\n\n";
    
    echo "Also install FFmpeg:\n";
    echo "sudo apt update && sudo apt install ffmpeg\n\n";
    
    echo "After installing both, update the paths in index.php if needed:\n";
    echo "\$config = [\n";
    echo "    ...\n";
    echo "    'ytdlp_path' => '/usr/local/bin/yt-dlp',\n";
    echo "    'ffmpeg_path' => '/usr/bin/ffmpeg',\n";
    echo "    ...\n";
    echo "];\n";
} elseif (stripos($os, 'darwin') !== false) {
    echo "Detected macOS system.\n";
    echo "Please install yt-dlp using Homebrew:\n";
    echo "brew install yt-dlp ffmpeg\n\n";
    
    echo "After installing both, update the paths in index.php if needed:\n";
    echo "\$config = [\n";
    echo "    ...\n";
    echo "    'ytdlp_path' => '/usr/local/bin/yt-dlp',\n";
    echo "    'ffmpeg_path' => '/usr/local/bin/ffmpeg',\n";
    echo "    ...\n";
    echo "];\n";
} else {
    echo "Unknown operating system. Please install yt-dlp and FFmpeg manually.\n";
}

// Create .htaccess file for security
$htaccess_content = <<<EOT
# Protect directories
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>

# Protect sensitive files
<FilesMatch "^(update-status\.php|install\.php)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Protect log directory
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^logs/ - [F,L]
    RewriteRule ^temp/ - [F,L]
</IfModule>

# PHP settings
php_flag display_errors off
php_value max_execution_time 300
php_value memory_limit 256M
php_value post_max_size 8M
php_value upload_max_filesize 8M
EOT;

file_put_contents('.htaccess', $htaccess_content);
echo "Created .htaccess file for security.\n";

echo "\nInstallation completed!\n";
echo "Please make sure yt-dlp and FFmpeg are installed and the paths are correctly set in index.php.\n";
echo "You can now access the video downloader by opening index.php in your web browser.\n";