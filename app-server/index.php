<?php

// Retrieve the token from the URL query parameters
$token = $_GET['token'];

// Decrypt the token with the shared secret
$secret = hash('sha256', file_get_contents('../auth.sec'));
$decrypted_token = openssl_decrypt(base64_decode($token), 'AES-256-CBC', $secret, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, substr($secret, 0, 16));

if (!$decrypted_token) {
    // Failed to decrypt the token, redirect to client.local
    header("Location: http://client.local");
    exit;
}

// Verify the token with provider.local
$options = [
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/x-www-form-urlencoded',
        'content' => http_build_query(['access_token' => $decrypted_token])
    ]
];
$context = stream_context_create($options);
$response = file_get_contents('http://provider.local/resource.php', false, $context);

$decoded_response = json_decode($response, true);
if (!$decoded_response['success']) {
    // Token verification failed, redirect to client.local
    header("Location: http://client.local");
    exit;
}

// Token verification succeeded, display welcome message
?>
<!DOCTYPE html>
<html>

<head>
    <title>Welcome</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 16px;
            line-height: 1.5;
            text-align: center;
            background-color: #F5F5F5;
        }

        h1 {
            font-size: 32px;
            margin-top: 50px;
        }

        p {
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <h1>Welcome!</h1>
    <p>Your access token is valid. You can now use the application.</p>
</body>

</html>