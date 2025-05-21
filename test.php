<?php
// Configuration
$testUrls = [
    'YouTube' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    'TikTok'  => 'https://vt.tiktok.com/ZShsdTSVr/', // Replace with a valid public TikTok URL
];

$logDir = __DIR__ . '/logs';
$videoDir = __DIR__ . '/videos';
$ytDlpPath = 'yt-dlp'; // or full path if needed
$ffmpegPath = 'ffmpeg'; // or full path if needed

// Create necessary directories
@mkdir($logDir, 0777, true);
@mkdir($videoDir, 0777, true);

function isInstalled($cmd) {
    return trim(shell_exec("which $cmd")) !== '';
}

function testDownload($name, $url, $ytDlpPath, $ffmpegPath, $videoDir, $logDir) {
    $outputFile = $videoDir . '/' . $name . '_' . time() . '.%(ext)s';
    $command = escapeshellcmd("$ytDlpPath --ffmpeg-location $ffmpegPath -f best -o $outputFile $url") . " 2>&1";
    
    file_put_contents("$logDir/{$name}_command.log", $command);
    $output = shell_exec($command);
    file_put_contents("$logDir/{$name}_output.log", $output);

    echo "<h3>Test: $name</h3>";
    echo "<strong>URL:</strong> $url<br>";
    echo "<strong>Command:</strong><br><pre>$command</pre>";
    echo "<strong>Output:</strong><br><pre>$output</pre>";
    echo "<strong>Log Files:</strong> <a href='logs/{$name}_command.log' target='_blank'>Command</a> | <a href='logs/{$name}_output.log' target='_blank'>Output</a>";
    echo "<hr>";
}

// Environment check
echo "<h2>yt-dlp / ffmpeg Server Environment Check</h2>";
echo "<ul>";
echo "<li><strong>yt-dlp:</strong> " . (isInstalled($ytDlpPath) ? "✅ Found" : "❌ Not Found") . "</li>";
echo "<li><strong>ffmpeg:</strong> " . (isInstalled($ffmpegPath) ? "✅ Found" : "❌ Not Found") . "</li>";
echo "<li><strong>Write Permission (logs):</strong> " . (is_writable($logDir) ? "✅ Writable" : "❌ Not Writable") . "</li>";
echo "<li><strong>Write Permission (videos):</strong> " . (is_writable($videoDir) ? "✅ Writable" : "❌ Not Writable") . "</li>";
echo "</ul>";
echo "<hr>";

// Run tests
foreach ($testUrls as $name => $url) {
    testDownload($name, $url, $ytDlpPath, $ffmpegPath, $videoDir, $logDir);
}
?>
