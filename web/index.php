<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once('./settings.php');

include_once('./scripts/db.php');

include_once('./scripts/functions.php');
include_once('./scripts/init.php');

$request = [
    'url' => $_GET['q'] ?? '',
    'method' => $_SERVER['REQUEST_METHOD'],
    'get' => $_GET,
    'post' => $_POST,
    'Content-Type' => 'text/html'
];

$response = init($request, $urlconf, $db);

if (!empty($response['headers'])) {
    foreach ($response['headers'] as $key => $value) {
        header(is_string($key) ? "$key: $value" : $value);
    }
}

echo $response['entity'] ?? '';