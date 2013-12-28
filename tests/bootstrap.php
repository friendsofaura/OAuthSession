<?php
// turn on all errors
error_reporting(E_ALL);

$file = dirname(__DIR__) . '/vendor/autoload.php';
if (! file_exists($file)) {
    // vendor/friendsofaura/oauthsession/tests
    $file = dirname(dirname(dirname(__DIR__))) . '/autoload.php';    
    if (! file_exists($file)) {
        throw new RuntimeException('Install dependencies to run test suite.');
    }
}

// autoloader
$loader = require $file;
$loader->add('Aura\Session', dirname($file) . '/aura/session/tests');
ob_start();
