<?php

require_once __DIR__.'/../vendor/autoload.php';

use Illuminate\Http\Request;

// create a request instance
$input = Request::createFromGlobals();

// create contact instance
$contact = new \PulkitJalan\ContactForm\Contact();

// set header to allow all origins
header('Access-Control-Allow-Origin: *');

// if form is not configured throw 500 error
if (! $contact->isConfigured()) {
    http_response_code(500);
    echo 'Contact form is not configured properly';
    exit;
}

// if request method was post then user is trying to send an email
if (! $input->isMethod('post')) {
    echo 'Contact form is up';
    exit;
}

$data = $input->all();

$required = $contact->getConfigParam('required');
foreach ($required as $validate) {
    if (! in_array($validate, array_keys($data)) || empty(array_get($data, $validate))) {
        http_response_code(400);
        echo 'Failed';

        // Check if user has set a redirect
        if ($failure = $contact->getConfigParam('redirect.failure', array_get($data, 'redirect.failure'))) {
            header('Location: '.$failure);
        }

        exit;
    }
}

// Verify captcha
if (array_get($data, 'g-recaptcha-response') && $secret = $contact->getConfigParam('recaptcha.secret')) {
    $post_data = http_build_query(
        [
            'secret' => $secret,
            'response' => array_get($data, 'g-recaptcha-response'),
            'remoteip' => array_get($_SERVER, 'HTTP_CLIENT_IP', array_get($_SERVER, 'HTTP_X_FORWARDED_FOR', array_get($_SERVER, 'REMOTE_ADDR'))),
        ]
    );

    $opts = [
        'http' => [
            'method' => 'POST',
            'header' => 'Content-type: application/x-www-form-urlencoded',
            'content' => $post_data,
        ],
    ];

    $context = stream_context_create($opts);
    $response = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
    $result = json_decode($response);

    if (! $result->success) {
        http_response_code(400);
        echo 'Failed';

        // Check if user has set a redirect
        if ($failure = $contact->getConfigParam('redirect.failure', array_get($data, 'redirect.failure'))) {
            header('Location: '.$failure);
        }

        exit;
    }

    unset($data['g-recaptcha-response']);
}

$files = array_get($data, 'files', []);

$data['files'] = array_map(function ($file) {
    if (empty($file)) {
        return;
    }

    return [
        'path' => $file->getRealPath(),
        'name' => urldecode($file->getClientOriginalName()),
        'type' => $file->getMimeType(),
    ];
}, (array) $files);

$success = false;
try {
    $success = $contact->send($data);
} catch (Exception $e) {
    error_log($e);
    $success = false;
}

// try sending
if ($success) {
    echo 'Successful';

    // Check if user has set a redirect
    if ($success = $contact->getConfigParam('redirect.success', array_get($data, 'redirect.success'))) {
        header('Location: '.$success);
    }
} else {
    http_response_code(400);
    echo 'Failed';

    // Check if user has set a redirect
    if ($failure = $contact->getConfigParam('redirect.failure', array_get($data, 'redirect.failure'))) {
        header('Location: '.$failure);
    }
}
