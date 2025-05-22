<?php
// This file contains proxy configuration for bypassing rate limits
// Include this file in your index.php

// List of free proxy servers
// Note: Free proxies may be unreliable, consider using a paid proxy service for production
$free_proxies = [
    // Format: [ip, port, type]
    // Type can be 'http', 'https', 'socks4', or 'socks5'
    // These are examples and may not work - replace with working proxies
    ['80.48.119.28', '8080', 'http'],
    ['176.9.75.42', '3128', 'http'],
    ['159.89.49.60', '31264', 'http'],
    ['95.216.194.46', '1081', 'socks5'],
    ['51.158.68.133', '8811', 'http'],
    ['51.158.119.88', '8811', 'http']
];

// Function to get a random proxy
function get_random_proxy() {
    global $free_proxies;
    
    if (empty($free_proxies)) {
        return null;
    }
    
    return $free_proxies[array_rand($free_proxies)];
}

// Function to build a proxy command for yt-dlp
function build_proxy_command($proxy) {
    if (!$proxy) {
        return '';
    }
    
    list($ip, $port, $type) = $proxy;
    
    switch ($type) {
        case 'http':
        case 'https':
            return "--proxy http://$ip:$port";
        case 'socks4':
            return "--proxy socks4://$ip:$port";
        case 'socks5':
            return "--proxy socks5://$ip:$port";
        default:
            return '';
    }
}

// Function to test if a proxy is working
function test_proxy($proxy) {
    if (!$proxy) {
        return false;
    }
    
    list($ip, $port, $type) = $proxy;
    
    $ch = curl_init('https://www.google.com');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    switch ($type) {
        case 'http':
        case 'https':
            curl_setopt($ch, CURLOPT_PROXY, "$ip:$port");
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
            break;
        case 'socks4':
            curl_setopt($ch, CURLOPT_PROXY, "$ip:$port");
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS4);
            break;
        case 'socks5':
            curl_setopt($ch, CURLOPT_PROXY, "$ip:$port");
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
            break;
    }
    
    $result = curl_exec($ch);
    $error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ($result !== false && $http_code == 200);
}

// Function to find a working proxy
function find_working_proxy() {
    global $free_proxies;
    
    // Shuffle the proxy list
    shuffle($free_proxies);
    
    // Try each proxy until we find one that works
    foreach ($free_proxies as $proxy) {
        if (test_proxy($proxy)) {
            return $proxy;
        }
    }
    
    return null;
}
?>
