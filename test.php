<?php

require_once('vendor/autoload.php');

use GuzzleHttp\Client;

$client = new Client([
    // Base URI is used with relative requests
    'base_uri' => 'https://odessa',
    // You can set any number of default request options.
    'timeout'  => 2.0,
    'verify' => false,
]);

//$response = $client->request('PUT', 'https://odessa/api/upload/10/', [
//    'headers' => ['X-Auth-Token' => '1784d1d4-ac5b-4db1-94a5-2b1e23e1e804'],
//    'json' => [
//        'filename' => 'file',
//    ],
//]);

$options = [
    'headers' => [
        'X-Auth-Token' => '1784d1d4-ac5b-4db1-94a5-2b1e23e1e804',
        'file' => 'file',
    ],
];

try {
    $request = $client->put('/api/upload/10/', $options);
    $request->setBody('{"foo":"baz"}', 'application/json');
} catch(\Exception $e) {}


$request = $client->put('testfile.txt');
$request->setBody(fopen('testfile.txt', 'r'));
$response = $request->send();

exit;

