<?php
// Retrieve username and password from the POST request
$username = $_POST['username'];
$password = $_POST['password'];

// Encrypt the username and password with the public key
$public_key = openssl_pkey_get_public(file_get_contents('auth.pub'));
openssl_public_encrypt("$username:$password", $encrypted, $public_key);

// Send the encrypted data to auth.local and receive a response
$options = [
    'https' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode(['data' => base64_encode($encrypted)])
    ]
];
$context = stream_context_create($options);
$response = file_get_contents('http://auth.local/auth.php', false, $context);

// Decrypt the response with the password hash
$decoded_response = json_decode($response, true);
$password_hash = hash('sha256', $password);
$encrypted_response = base64_decode($decoded_response['data']);
openssl_private_decrypt($encrypted_response, $decrypted_response, $password_hash);

// Check if the authentication was successful
$auth_response = json_decode($decrypted_response, true);
if ($auth_response['auth'] === 'success') {
    // Redirect to application.local with the token
    header("Location: http://application.local?token={$auth_response['token']}");
} else {
    // Redirect to the login page
    header("Location: login.php");
}
