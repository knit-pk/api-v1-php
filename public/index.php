<?php

use App\Kernel;
use Symfony\Component\Debug\Debug;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;

const APP_PATH = '../';
const FULL_APP_PATH = __DIR__ . '/' . APP_PATH;

require FULL_APP_PATH . 'vendor/autoload.php';

// The check is to ensure we don't use .env in production
if (!isset($_SERVER['APP_ENV'])) {
    (new Dotenv())->load(FULL_APP_PATH . '.env');
}

if ($debug = $_SERVER['APP_DEBUG'] ?? false) {
    // WARNING: You should setup permissions the proper way!
    // REMOVE the following PHP line and read
    // https://symfony.com/doc/current/book/installation.html#checking-symfony-application-configuration-and-setup
    umask(0000);

    Debug::enable();
}

if ($trustedHosts = $_SERVER['APP_TRUSTED_HOSTS'] ?? false) {
    $trustedHosts = str_replace('\'', '', $trustedHosts);
    $trustedHosts = explode(',', trim($trustedHosts, '[]'));
    Request::setTrustedHosts($trustedHosts);
}

if ($trustedProxies = $_SERVER['APP_TRUSTED_PROXIES'] ?? false) {
    $trustedProxies = str_replace('\'', '', $trustedProxies);
    $trustedProxies = explode(',', trim($trustedProxies, '[]'));
    Request::setTrustedProxies($trustedProxies, Request::HEADER_X_FORWARDED_ALL);
}

$kernel = new Kernel($_SERVER['APP_ENV'] ?? 'dev', $debug);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
