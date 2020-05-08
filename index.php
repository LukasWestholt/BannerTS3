<?php
require_once __DIR__ . "/config.php";

function startsWith($haystack, $needle) {
	if (!is_string($haystack)) {return False;}
     $length = strlen($needle);
     return (substr($haystack, 0, $length) === $needle);
}

function getCenter($xy, $img_length, $length) {
	if (count(explode(";",$xy)) > 1) {
		return abs($img_length/2 - $length/2 + intval(explode(";",$xy)[1])); 
	} else {
		return abs($img_length/2 - $length/2);
	}
}

function getRight_Bottom($xy, $img_length, $length) {
	if (count(explode(";",$xy)) > 1) {
		return abs($img_length - $length + intval(explode(";",$xy)[1])); 
	} else {
		return abs($img_length - $length);
	}
}

$ip = getenv('HTTP_CLIENT_IP') ?: getenv('HTTP_X_FORWARDED_FOR') ?: getenv('HTTP_X_FORWARDED') ?: getenv('HTTP_FORWARDED_FOR') ?: getenv('HTTP_FORWARDED') ?: getenv('REMOTE_ADDR');

$client_city = false;
$adminonline = 0;
$my_user = [];
$my_user["nick"] = $config['img']['found_nick'];
$ping = "";
if ($config['settings'] == 'auto') {
	require_once __DIR__ . "/class/ts3admin.class.php";
	$files = array_diff(scandir('cache'), array('..', '.', 'fulls', 'thumbs'));
		if (!empty($config['cache_name']) or $config['cache_name'] != '') {
			if (!in_array($config['cache_name'] ,$files)) {
				if(touch('./cache/'.$config['cache_name'])) {
				if(chmod('./cache/'.$config['cache_name'], 0777)) {
					$cache = 'success';
				} else {
					alert ('The folder and the cache file were created, no access rights were granted');
					exit;
				}
				} else {
					alert ('Not created <b>cache</b>');
					exit;
				}
			} else {
			$srv = '';
				if (!file_exists('cache/'.$config['cache_name']) || filemtime('cache/'.$config['cache_name']) + 30 < time()) {;
					$query = new ts3admin($config['ts3']['host'], $config['ts3']['query_port'], 2);
					$query->connect();
					$query->login($config['ts3']['login'],$config['ts3']['password']);
					$query->selectServer($config['ts3']['login_port']);
					$query->setName('loading-server-banner-service');
					$srv = [];
					$srv['server'] = $query->getElement('data', $query->serverInfo());
					$srv['groups'] = $query->getElement('data', $query->serverGroupList());
					$srv['clients'] = $query->getElement('data', $query->clientList('-uid -away -voice -times -groups -info -icon -country -ip'));
					$srv['clientsdb'] = $query->getElement('data', $query->clientDbList());
					$srv['channel'] = $query->getElement('data', $query->channelList());
					$srv['banlist'] = $query->getElement('data', $query->banList());
					//$query->sendMessage("3",<id>, <text>);  Debug
					$query->logout();

					@file_put_contents('cache/'.$config['cache_name'], json_encode($srv));
				} else {
					$srv = file_get_contents('cache/'.$config['cache_name']);
					$srv = json_decode($srv, true);
				}
			}
		} else {
			alert ('cache name not found in config.php');
			exit;
		}
} elseif ($config['settings'] == 'bot') {
	$ts3 = file_get_contents('cache/'.$config['cache_name']);
	$srv = json_decode($ts3, true);	
}
if ($weather['status']) {	
	$raw_ipdata = file_get_contents("cache/ipdata");
	if (!$raw_ipdata) {$ipdata = [];} else {$ipdata = json_decode($raw_ipdata, True);}
	foreach($ipdata as $item) {
		if ($item["ip"] == $ip) {
			$client_city = $item["city"];
			break;
		}
	}
	
	if (!$client_city) {
		$ch = curl_init("https://ipinfo.io/".rawurlencode($ip)."/city?token=".rawurlencode($config['ipinfo_token']));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		$output = curl_exec($ch);
		$error = false;
		if ($output === false) {
			// "CURL Error: " . curl_error($ch);
			$error = true;
		}

		$responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		/*
		 * 4xx status codes are client errors
		 * 5xx status codes are server errors
		 * 429 status code rate limits exceed
		 */
		if ($responseCode >= 400) {
			// "HTTP Error: " . $responseCode;
			$error = true;
		}
		curl_close($ch);
		
		if (!$error) {
			$client_city = trim($output);
		} else {
			$query = @unserialize(file_get_contents('http://ip-api.com/php/'.rawurlencode($ip)));
			if ($query && $query['status'] == 'success') {
				$client_city = $query['city'];
			}
		}
		
		if (!$client_city) {
			$client_city = "Berlin"; //fallback
		} else {
			$client = [
				"ip" => $ip,
				"city" => $client_city,
			];
		
			array_push($ipdata, $client);
			file_put_contents("cache/ipdata",json_encode($ipdata));
		}
	}
	
	$json = file_get_contents('https://api.openweathermap.org/data/2.5/weather?q=' .rawurlencode($client_city) .'&appid=' . rawurlencode($config['apikey_openweathermap']) . '&units=metric&lang=' . $config['lang']);
	// language support: https://openweathermap.org/current#multi
	$data = json_decode($json, true);
	$weathericon = $data['weather'][0]['icon'];
	$weathericonurl = 'https://openweathermap.org/img/wn/' . $weathericon . '@2x.png';
	$weathericonfile = 'cache/weathericons/' . $weathericon . '.png';

	if (!file_exists($weathericonfile)) {
		$ch = curl_init($weathericonurl);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
		$rawdata = curl_exec($ch);
		curl_close($ch);
		$fp = fopen($weathericonfile, 'x');
		fwrite($fp, $rawdata);
		fclose($fp);
	}
}

foreach ($srv['clients'] as $client) {
	if ($client['client_type'] == "0") {
		$groups = explode(',', $client['client_servergroups']);
		if ($client["connection_client_ip"] == $ip) {
			$my_user["nick"] = $client['client_nickname'];
			$my_user['client_unique_identifier'] = $client['client_unique_identifier'];
		}
		foreach ($admingroups as $group) {
			if (in_array($group, $groups)) {
				$adminonline++;
			}
		}
	}
}

foreach ($srv['clientsdb'] as $clientdb) {
	if ($clientdb["client_unique_identifier"] == $my_user['client_unique_identifier']) {
		$my_user['client_totalconnections'] = $clientdb['client_totalconnections'];
	}
}
	
$ping = strval(floatval($srv['server']['virtualserver_total_ping']));

if ($config['lang'] == 'PL') $month = array(1 => 'stycznia', 'lutego', 'marca', 'kwietnia', 'maja', 'czerwca', 'lipca', 'sierpnia', 'wrzesnia', 'pazdziernika', 'listopada', 'grudnia');
if ($config['lang'] == 'EN') $month = array(1 => 'january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december');
if ($config['lang'] == 'DE') $month = array(1 => 'Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember');
if ($config['lang'] != 'EN' and $config['lang'] != 'PL'and $config['lang'] != 'DE') $month = array(1 => 'january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december');
$day = array('01' => '1','02' => '2','03' => '3','04' => '4','05' => '5','06' => '6','07' => '7','08' => '8','09' => '9','10' => '10','11' => '11','12' => '12','13' => '13','14' => '14','15' => '15','16' => '16','17' => '17','18' => '18','19' => '19','20' => '20','21' => '21','22' => '22','23' => '23','24' => '24','25' => '25','26' => '26','27' => '27','28' => '28','29' => '29','30' => '30','31' => '31');

if ($config['banner']['format'] == 'png') {
    $image = imagecreatefrompng($config['banner']['background']);
} elseif ($config['banner']['format'] == 'jpg') {
    $image = imagecreatefromjpeg($config['banner']['background']);
}

foreach ($img as $item) {
    $text    = $item['text'];
	$replace = array(
		1 => array(1 => '[adminsOnline]', 2 => $adminonline),
		2 => array(1 => '[nick]', 2 => $my_user["nick"]),
		3 => array(1 => '[channel]', 2 => $srv['server']['virtualserver_channelsonline']),
		//4 => array(1 => '[visit]', 2 => $srv['server']['virtualserver_client_connections']),
		4 => array(1 => '[visit]', 2 => $my_user['client_totalconnections']),
		5 => array(1 => '[max]', 2 => $srv['server']['virtualserver_maxclients']),
		6 => array(1 => '[online]', 2 => $srv['server']['virtualserver_clientsonline'] - $srv['server']['virtualserver_queryclientsonline']),
		7 => array(1 => '[ping]', 2 => $ping),
		8 => array(1 => '[time]', 2 => date('H:i')),
		9 => array(1 => '[date]', 2 => $day[date('d')].'. '.$month[date('n')]),
		10 => array(1 => '[uptime]', 2 => floor($srv['server']['virtualserver_uptime'] / 86400)),
	);
    if (!empty($data['name'])) {
        if ($weather['icon']['status']) {
            $weathericon_image = imagecreatefrompng($weathericonfile);
            imagecopy($image, $weathericon_image, $weather['icon']['x'], $weather['icon']['y'], 0, 0, 80, 80);
        }
        if ($weather['city']['status']) {
            onImage($image, $weather['city']['x'], $weather['city']['y'], $data['name'], $weather['city']['font'], $weather['city']['size'], $weather['city']['color']);
        }
        if ($weather['temp']['status']) {
            onImage($image, $weather['temp']['x'], $weather['temp']['y'], round((float) $data['main']['temp'], 1) . '°C', $weather['temp']['font'], $weather['temp']['size'], $weather['temp']['color']);
        }
        if ($weather['description']['status']) {
            onImage($image, $weather['description']['x'], $weather['description']['y'], $data['weather'][0]['description'], $weather['description']['font'], $weather['description']['size'], $weather['description']['color']);
        }
    }
    foreach ($replace as $new) {
        $text = str_replace($new[1], $new[2], $text);
    }
    onImage($image, $item['x'], $item['y'], $text, $item['font'], $item['size'], $item['color']);
}
if ($config['banner']['format'] == 'png') {
    header('Content-Type: image/png');
    imagepng($image);
} elseif ($config['banner']['format'] == 'jpg') {
    header('Content-Type: image/jpg');
    imagejpeg($image);
}
imagedestroy($image);

function onImage($img, $x, $y, $text, $font, $fontsize, $color) {
    $fontfile = __DIR__ . $font;
    $box      = imageTTFBbox($fontsize, 0, $fontfile, $text);
    $width    = abs($box[4] - $box[0]);
    $height   = abs($box[5] - $box[1]);
    if (startsWith($x, "right")) {
		$x = getRight_Bottom($x, imagesx($img), $width);
	} else if (startsWith($x, "center")) {
		$x = getCenter($x, imagesx($img), $width);
	} else {
		//$x -= $width / 2;
	}
    if (startsWith($y, "bottom")) {
		$y = getRight_Bottom($y, imagesy($img), $height);
	} else if (startsWith($y, "center")) {
		$y = getCenter($y, imagesy($img), $height);
	} else {
		//$y += $height / 2;
	}
	
	$hex = str_replace("#", "", $color);
	if(strlen($hex) == 3) {
		$r = hexdec(substr($hex,0,1).substr($hex,0,1));
		$g = hexdec(substr($hex,1,1).substr($hex,1,1));
		$b = hexdec(substr($hex,2,1).substr($hex,2,1));
	} else {
        $r = hexdec(substr($hex,0,2));
		$g = hexdec(substr($hex,2,2));
		$b = hexdec(substr($hex,4,2));
	}
    imagettftext($img, $fontsize, 0, $x, $y, ImageColorAllocate($img, $r, $g, $b), $fontfile, $text);
}

function alert($msg) {
    echo '<b>Error: </b>' . $msg;
}