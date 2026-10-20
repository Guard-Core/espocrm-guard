<?php

// Usage: php apply-config.php <guard-config.php>
// Merges the keys the file returns (guardEnabled and guard) into data/config.php. The file is rewritten in place
// because EspoCRM's ConfigWriter drops keys it does not know yet.

$path = '/var/www/html/data/config.php';

$config = include $path;
$override = include $argv[1];

$config = array_replace($config, $override);
$config['microtime'] = microtime(true);

file_put_contents($path, "<?php\nreturn " . var_export($config, true) . ";\n", LOCK_EX);

if (function_exists('opcache_invalidate')) {
    opcache_invalidate($path, true);
}

echo "applied {$argv[1]}\n";
