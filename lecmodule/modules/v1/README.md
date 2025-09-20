# How to log in through the API endpoint

This document provides instructions on how to log in through the API endpoint.

## Making a POST request to the API

To make a POST request to the API, you can use any HTTP client. The following example uses Guzzle, a popular PHP HTTP client.

First, you need to install Guzzle:

```bash
composer require guzzlehttp/guzzle
```

Then, you can use the following code to make a request to the login endpoint:

```php
<?php

require __DIR__ . '/vendor/autoload.php';

$client = new \GuzzleHttp\Client();

$response = $client->post('https://lectdev2.uonbi.ac.ke/v1/login', [
    'json' => [
        'payrollNumber' => 'your_payroll_number',
        'userPassword' => 'your_password',
    ],
]);

$body = $response->getBody();
$data = json_decode($body, true);

$token = $data['token'];
```
