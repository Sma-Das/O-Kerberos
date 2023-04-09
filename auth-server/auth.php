<?php
// Load private key
$private_key = openssl_pkey_get_private("file:///path/to/auth.priv");

// Decrypt credentials from client.local
$encrypted_credentials = $_POST['credentials'];
openssl_private_decrypt($encrypted_credentials, $decrypted_credentials, $private_key);

// Extract username and password from decrypted credentials
$credentials = json_decode($decrypted_credentials, true);
$username = $credentials['username'];
$password = $credentials['password'];

// Send credentials to provider.local
$provider_url = "http://provider.local/auth.php";
$provider_response = file_get_contents($provider_url . "?username=$username&password=$password");

// Extract access token from provider response
$provider_token = json_decode($provider_response, true)['access_token'];

// Encrypt token with sha256 hash of password
if ($provider_token) {
    $token = openssl_encrypt($provider_token, "aes-256-cbc", hash("sha256", $password));
    $auth = "success";
} else {
    $token = "";
    $auth = "fail";
}

// Send auth response to client.local
$response = array("auth" => $auth, "token" => $token);
echo json_encode($response);
