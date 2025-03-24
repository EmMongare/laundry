<?php
function generateAccessToken() {
    $consumerKey = 'Aa5j2vWG1I8K48qvdYLUIV9Wu7F4ZinkhgOoJvJkPqMd74O5';
    $consumerSecret = 'DxtPK7k3WhhYq1R1IbJd4jgIsN6BUMNGfzoCXEt2ciRG62EQ2Tc4D8gAAcpbLuWN';
    $url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'Authorization: Basic ' . base64_encode("$consumerKey:$consumerSecret"),
        'Content-Type: application/json'
    ]);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    $decodedResponse = json_decode($response, true);

    if ($http_code !== 200 || !isset($decodedResponse['access_token'])) {
        die("Error fetching access token. HTTP Code: $http_code, Response: " . json_encode($decodedResponse));
    }

    return $decodedResponse['access_token'];
}
?>
