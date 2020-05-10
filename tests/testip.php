<?php
//require_once __DIR__ . "/../config.php";
//$ipinfo_token = $config['ipinfo_token'];

echo getenv('HTTP_CLIENT_IP');
echo "<br>";
echo getenv('HTTP_X_FORWARDED_FOR');
echo "<br>";
echo getenv('HTTP_X_FORWARDED');
echo "<br>";
echo getenv('HTTP_FORWARDED_FOR');
echo "<br>";
echo getenv('HTTP_FORWARDED');
echo "<br>";
echo getenv('REMOTE_ADDR');
echo "<br>";
$ip = file_get_contents("http://ipecho.net/plain");
echo $ip;
echo "<br>";
$ip = getenv('HTTP_CLIENT_IP') ?: getenv('HTTP_X_FORWARDED_FOR') ?: getenv('HTTP_X_FORWARDED') ?: getenv('HTTP_FORWARDED_FOR') ?: getenv('HTTP_FORWARDED') ?: getenv('REMOTE_ADDR');

$query = @unserialize(file_get_contents('http://ip-api.com/php/'.$ip));
if($query && $query['status'] == 'success') {
	$client_city = $query['city'];
	echo "City: ".$client_city;
}
//echo "<br>";
//echo "City: ".file_get_contents("https://ipinfo.io/".$ip."/city?token=".$ipinfo_token);
