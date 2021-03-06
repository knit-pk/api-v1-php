<?php

use function K911\Swoole\decode_string_as_set;
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
    umask(0000);

    Debug::enable();
}

if ($trustedHosts = $_SERVER['APP_TRUSTED_HOSTS'] ?? false) {
    Request::setTrustedHosts(decode_string_as_set($trustedHosts));
}

if ($trustedProxies = $_SERVER['APP_TRUSTED_PROXIES'] ?? false) {
    Request::setTrustedProxies(decode_string_as_set($trustedProxies), Request::HEADER_X_FORWARDED_ALL);
}

$kernel = new Kernel($_SERVER['APP_ENV'] ?? 'dev', $debug);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
