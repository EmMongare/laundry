<?php
require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$apiKey = $_ENV["API_KEY"];
$username = $_ENV["USERNAME"];
$dbUrl = $_ENV["DB_URL"];

echo "API Key: $apiKey \n";
echo "Username: $username \n";
echo "Database URL: $dbUrl \n";
?>
