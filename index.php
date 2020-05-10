<?php
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/picture_template.php";

# TODO: catch if there is no config.php
# TODO: execute check_cache_file_write_perms on 'server' and 'ipdata' and 'weathericons'?
# TODO: check if ipdata timeout works correctly in a week
# TODO: core.php run
# TODO: debug function
# TODO: round displayed time
# TODO: exclude config tester
# TODO: display ping=0 when user=0?


/**
 *
 * echo an alert
 * exit the script
 *
 * @param mixed $msg
 * @param bool $exit Default: true [optional]
 */
function alert($msg, $exit = true) {
    if (is_bool($msg)) $msg = $msg ? 'true' : 'false';
    if (is_array($msg)) $msg = implode("|", $msg);
    echo '<b>Alert: </b>' . $msg;
    if ($exit) exit;
}

/**
 *
 * Check if the string A starts with string B
 * if a is not a string return false
 *
 * @param mixed $a String A
 * @param string $b String B
 * @return bool Does the string A starts with string B
 */
function startsWith($a, $b) {
	if (!is_string($a)) {return False;}
     $length = strlen($b);
     return (substr($a, 0, $length) === $b);
}

/**
 * @param string $xy
 * @param int $img_length
 * @param int $length
 * @return int
 */
function getCenter($xy, $img_length, $length) {
	if (count(explode(";",$xy)) > 1) {
		return intval(abs($img_length/2 - $length/2 + intval(explode(";",$xy)[1])));
	} else {
		return intval(abs($img_length/2 - $length/2));
	}
}

/**
 * @param string $xy
 * @param int $img_length
 * @param int $length
 * @return int
 */
function getRight_Bottom($xy, $img_length, $length) {
	if (count(explode(";",$xy)) > 1) {
		return intval(abs($img_length - $length + intval(explode(";",$xy)[1])));
	} else {
		return intval(abs($img_length - $length));
	}
}

/**
 * @param resource $img Image
 * @param int|string $x
 * @param int|string $y
 * @param string $text
 * @param string $font
 * @param int $fontsize
 * @param string $color
 */
function onImage($img, $x, $y, $text, $font, $fontsize, $color) {
    $fontfile = __DIR__ . $font;
    if (!is_readable($fontfile)) alert("The font file does not exist or you don't have read permission on it");
    # TODO what is the name of the font file?
    $box      = imageTTFBbox($fontsize, 0, $fontfile, $text);
    $width    = abs($box[4] - $box[0]);
    $height   = abs($box[5] - $box[1]);
    if (startsWith($x, "right")) {
        $x = getRight_Bottom($x, imagesx($img), $width);
    } else if (startsWith($x, "center")) {
        $x = getCenter($x, imagesx($img), $width);
    }
    if (startsWith($y, "bottom")) {
        $y = getRight_Bottom($y, imagesy($img), $height);
    } else if (startsWith($y, "center")) {
        $y = getCenter($y, imagesy($img), $height);
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

/**
 *
 * This function will alert() if the file is not writable to creatable
 *
 * @param string $path file path
 * @return bool need_new_file
 */
function check_cache_file_write_perms($path) {
    if (!is_writable($path)) {
        if (file_exists($path) and !chmod($path, 0777)) alert('The cache file exists, but you have to set the file permission of writing manually');
        if (!file_exists($path)) {
            //if (!touch($path)) {
            if (!is_writable(dirname($path))) {
                alert('PHP is not able to create your cache file, please set the directory permission so PHP is able to write new files [0777 rwxrwxrwx]');
                # TODO what is the name of the directory?
            } else {
                // file can get created
                //check_cache_file_write_perms($path);
                return true;
            }
        }
    }
    return false; // able to write cache file exists
}

/**
 *
 * Executes a curl request
 * returns curl data in array
 *
 * @param string $url
 * @param bool $FAILONERROR Default: false [optional]
 * @param bool $BINARYTRANSFER Default: false [optional]
 * @param bool $CONNECTTIMEOUT Default: false [optional]
 * @param int $TO Default: 5 [optional]
 * @return array answer of curl
 */
function curl_get_data($url, $FAILONERROR = false, $BINARYTRANSFER = false, $CONNECTTIMEOUT = false, $TO = 5) {
    $ch = curl_init($url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    if ($FAILONERROR) curl_setopt($ch, CURLOPT_FAILONERROR, true);
    if ($BINARYTRANSFER) curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
    if ($CONNECTTIMEOUT) curl_setopt($ch,CURLOPT_CONNECTTIMEOUT, $TO);
    $data = curl_exec($ch);
    $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return array($data, $responseCode);
}

/**
 * @param string $host
 * @param int $query_port
 * @param int $timeout
 * @param string $login
 * @param string $password
 * @param int $login_port
 * @param string $nickname
 * @return array TS-data
 */
function get_TS3_data($host, $query_port, $timeout, $login, $password, $login_port, $nickname) {
    require_once __DIR__ . "/class/ts3admin.class.php";
    $srv = [];
    $query = new par0noid\ts3admin($host, $query_port, $timeout);
    $output = $query->connect();
    $srv = array_merge($srv, $output);
    if (!$srv['success']) return $srv;
    $output = $query->login($login,$password);
    $srv = array_merge($srv, $output);
    if (!$srv['success']) return $srv;
    // edits to the new ts3admin version
    $output = $query->selectServer($login_port, "port", false, $nickname);
    $srv = array_merge($srv, $output);
    if (!$srv['success']) return $srv;
    $srv['server'] = $query->getElement('data', $query->serverInfo());
    $srv['groups'] = $query->getElement('data', $query->serverGroupList());
    $srv['clients'] = $query->getElement('data', $query->clientList('-uid -away -voice -times -groups -info -icon -country -ip'));
    $srv['clientsdb'] = $query->getElement('data', $query->clientDbList());
    $srv['channel'] = $query->getElement('data', $query->channelList());
    $srv['banlist'] = $query->getElement('data', $query->banList());
    //$query->sendMessage("3",<id>, <text>);  Debug
    $query->logout();
    return $srv;
}

/**
 * @param string $apikey
 * @param string $ip
 * @return false|string data if the request worked, else false
 */
function ipinfo($apikey, $ip) {
    if (!$apikey or !$ip) return false;
    $data = curl_get_data("https://ipinfo.io/" . rawurlencode($ip) . "/city?token=" . rawurlencode($apikey),  true);
    $output = $data[0];
    $responseCode = $data[1];

    if ($output === false) {
        //alert("CURL Error: " . curl_error($ch));
        return false;
    }

    /*
     * 4xx status codes are client errors
     * 5xx status codes are server errors
     * 429 status code rate limits exceed
     */
    if ($responseCode >= 400) {
        //alert("HTTP Error: " . $responseCode);
        return false;
    }
    if ($responseCode == 403) alert('Your apikey_ipinfo is invalid, look at: $config[\'apikey_ipinfo\'] in config.php');
    return trim($output);
}

/**
 * @param string $ip
 * @return false|string data if the request worked, else false
 */
function ip_api($ip) {
    $json_string = file_get_contents('http://ip-api.com/json/'.rawurlencode($ip));
    $api_data = json_decode($json_string, true);
    // file_get_contents is working here good, because ip-api.com always output http code 200
    // HTTP 429 on usage limits (45 req/min)
    if ($api_data and $api_data['status'] == 'success') {
        return strval($api_data['city']);
    }
    return false;
}

/**
 * @param string $language
 * @return string name of month in specific language
 */
function get_translated_name_of_month($language) {
    setlocale(LC_TIME, $language);
    $month = utf8_encode(strftime('%B'));
    setlocale(LC_TIME, "C");
    return $month;
}

$ip = getenv('HTTP_CLIENT_IP') ?: getenv('HTTP_X_FORWARDED_FOR') ?: getenv('HTTP_X_FORWARDED') ?: getenv('HTTP_FORWARDED_FOR') ?: getenv('HTTP_FORWARDED') ?: getenv('REMOTE_ADDR');

$client_city = false;
$adminonline = 0;
$my_user = [];
$my_user["nick"] = $config['img']['found_nick'];
$ping = "";

### CONFIG CHECK

if (!$config['cache_name']) alert('your config file is wrong, look at $config[\'cache_name\'] in config.php');
$cache_file = './cache/'.$config['cache_name'];

if (!file_exists($config['banner']['background'])) alert('background image path not found - your config file is wrong, look at: $config[\'banner\'][\'background\'] in config.php');

if ($config['banner']['format'] == 'png') {
    $image = imagecreatefrompng($config['banner']['background']);
} elseif ($config['banner']['format'] == 'jpg') {
    $image = imagecreatefromjpeg($config['banner']['background']);
} else {
    alert('your config file is wrong, look at: $config[\'banner\'][\'format\'] in config.php');
}

if ($config['settings'] != 'auto' and $config['settings'] != 'bot') {
    alert('your config file is wrong, look at: $config[\'settings\'] in config.php');
}

if (!$config['ts3']['timeout'] or !is_int($config['ts3']['timeout']) or $config['ts3']['timeout'] < 0) {
    alert('your config file is wrong, look at: $config[\'ts3\'][\'timeout\'] in config.php');
}
if (!$config['ts3']['cachetime'] or !is_int($config['ts3']['cachetime']) or $config['ts3']['cachetime'] < 0) {
    alert('your config file is wrong, look at: $config[\'ts3\'][\'cachetime\'] in config.php');
}
if (!$config['ip_cachetime'] or !is_int($config['ip_cachetime']) or $config['ip_cachetime'] < 0) {
    alert('your config file is wrong, look at: $config[\'ip_cachetime\'] in config.php');
}
if (!$config['fallback_city']) {
    alert('your config file is wrong, look at: $config[\'fallback_city\'] in config.php');
}
if (!is_array($config['ts3']['admingroups'])) {
    alert('your config file is wrong, look at: $config[\'ts3\'][\'admingroups\'] in config.php');
}

if (strlen($config['ts3']['nickname']) < 4) {
    alert('your config file is wrong, you have to set a nickname with more than 3 characters, look at: $config[\'ts3\'][\'nickname\'] in config.php');
}

### GET TS3 DATA

$need_new_file = check_cache_file_write_perms($cache_file);
//alert(($need_new_file ? 'true' : 'false'));
if ($config['settings'] == 'auto' and ($need_new_file or filemtime($cache_file) + $config['ts3']['cachetime'] < time())) {
    $ts3 = get_TS3_data($config['ts3']['host'], $config['ts3']['query_port'], $config['ts3']['timeout'],
        $config['ts3']['login'], $config['ts3']['password'], $config['ts3']['login_port'], $config['ts3']['nickname']);
    if ($ts3['success']) {
        @file_put_contents($cache_file, json_encode($ts3));
        // The @ operator tells PHP to suppress error messages, so that they will not be shown.
    } // else {alert($ts3['errors']);}
} else {
    if (!is_readable($cache_file)) alert("The cache file does not exist or you don't have read permission on it");
    $ts3 = json_decode(file_get_contents($cache_file), true);
}

### GET LOCATION AND WEATHER DATA

if ($weather['status']) {
    // Note: ipdata caching is not working when the access permission in missing.
	$raw_ipdata = file_get_contents("cache/ipdata");
	if (!$raw_ipdata) {$ipdata = [];} else {$ipdata = json_decode($raw_ipdata, True);}
    $city_data_source = "";
    foreach($ipdata as $key=>$item) {
		if ($item["ip"] == $ip) {
		    $client_city = "";
            if ($item["source"] != "ipinfo" or $item["unix-timestamp"] + 604800 < time()) {
                $output = ipinfo($config['apikey_ipinfo'], $ip);
                if ($output) {
                    $ipdata[$key]["city"] = $client_city = $output;
                    $ipdata[$key]["source"] = "ipinfo";
                    $ipdata[$key]["unix-timestamp"] = time();
                    file_put_contents("cache/ipdata",json_encode($ipdata));
                } elseif ($item["unix-timestamp"] + $config['ip_cachetime'] < time()) {
                    $output = ip_api($ip);
                    if ($output) {
                        $ipdata[$key]["city"] = $client_city = $output;
                        $ipdata[$key]["source"] = "ip-api";
                        $ipdata[$key]["unix-timestamp"] = time();
                        file_put_contents("cache/ipdata",json_encode($ipdata));
                    }
                }
            }
            if (!$client_city) $client_city = $item["city"];
			break;
		}
	}
	
	if (!$client_city) {
        $output = ipinfo($config['apikey_ipinfo'], $ip);
        if ($output) {
            $client_city = $output;
            $city_data_source = "ipinfo";
        }
    }
	if (!$client_city) {
	    $output = ip_api($ip);
        if ($output) {
            $client_city = $output;
            $city_data_source = "ip-api";
        }
    }
    if (!$client_city) {
        $client_city = $config['fallback_city'];
    }
    if ($city_data_source) {
        $new_ip = [
            "ip" => $ip,
            "city" => $client_city,
            "source" => $city_data_source,
            "unix-timestamp" => time(),
        ];

        array_push($ipdata, $new_ip);
        file_put_contents("cache/ipdata",json_encode($ipdata));
    }

    $weathericonfile = "";
	if ($config['apikey_openweathermap']) {
        $json_string = curl_get_data('https://api.openweathermap.org/data/2.5/weather?q=' .
            rawurlencode($client_city) . '&appid=' . rawurlencode($config['apikey_openweathermap']) .
            '&units=metric&lang=' . $config['lang'], false, false, true)[0];
        // language support: https://openweathermap.org/current#multi
        // if $config['lang'] is missing or not recognised by openweathermap.org english is fallback.
        $weather_data = json_decode($json_string, true);
        //https://openweathermap.org/faq#error401
        if ($weather_data['cod'] == 401) {
           alert('Your apikey_openweathermap is invalid, look at: $config[\'apikey_openweathermap\'] in config.php');
        } else {
            $weathericon = $weather_data['weather'][0]['icon'];
            if ($weathericon) {
                $weathericonurl = 'https://openweathermap.org/img/wn/' . $weathericon . '@2x.png';
                $weathericonfile = 'cache/weathericons/' . $weathericon . '.png';

                if (!file_exists($weathericonfile)) {
                    $rawdata = curl_get_data($weathericonurl,  false, true)[0];
                    check_cache_file_write_perms($weathericonfile);
                    $fp = fopen($weathericonfile, 'x');
                    fwrite($fp, $rawdata);
                    fclose($fp);
                }
            }
        }
    }
}

### EXPLODE TS DATA
### SEARCH FOR CLIENT NICKNAME, ADMINS, CLIENT TOTAL CONNECTIONS, PING

$is_ts_user_data_totally_found = false;
foreach ($ts3['clients'] as $client) {
	if ($client['client_type'] == "0") {
		$groups = explode(',', $client['client_servergroups']);
		if ($client["connection_client_ip"] == $ip) {
			$my_user["nick"] = $client['client_nickname'];
			$my_user['client_unique_identifier'] = $client['client_unique_identifier'];
		}
		foreach ($config['ts3']['admingroups'] as $group) {
			if (in_array($group, $groups)) {
				$adminonline++;
			}
		}
	}
}

foreach ($ts3['clientsdb'] as $clientdb) {
	if ($clientdb["client_unique_identifier"] == $my_user['client_unique_identifier']) {
		$my_user['client_totalconnections'] = $clientdb['client_totalconnections'];
        $is_ts_user_data_totally_found = true;
	}
}
	
$ping = strval(floatval($ts3['server']['virtualserver_total_ping'])); // this is the overall connection ping of the server, not of the client
// when $ts3['server']['virtualserver_total_ping'] == NULL then $ping is == 0

// you can add here a language
# TODO: find a better way
//setlocale(LC_TIME, "fi");
//echo utf8_encode(strftime('%A'));
if ($config['lang'] == 'pl') {
    $month = array(1 => 'stycznia', 'lutego', 'marca', 'kwietnia', 'maja', 'czerwca', 'lipca', 'sierpnia', 'wrzesnia', 'pazdziernika', 'listopada', 'grudnia');
} elseif ($config['lang'] == 'en') {
    $month = array(1 => 'january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december');
} elseif ($config['lang'] == 'de') {
    $month = array(1 => 'Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember');
} else {
    $month = array(1 => 'january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december');
}

### PLACE IT ON THE IMAGE

foreach ($img as $item) {
    if ($item['needs_teamspeak_server_connection'] and !$ts3['success']) continue;
    if ($item['needs_totally_found_ts_user_data'] and !$is_ts_user_data_totally_found) continue;

    $text = ($item[$config['lang']]) ? $item[$config['lang']] : $item['en'];
    $replaces = array(
        1 => array(1 => '[adminsOnline]', 2 => $adminonline),
        2 => array(1 => '[nick]', 2 => $my_user["nick"]),
        3 => array(1 => '[channel]', 2 => $ts3['server']['virtualserver_channelsonline']),
        4 => array(1 => '[visit]', 2 => $my_user['client_totalconnections']),
        5 => array(1 => '[max]', 2 => $ts3['server']['virtualserver_maxclients']),
        6 => array(1 => '[online]', 2 => $ts3['server']['virtualserver_clientsonline'] - $ts3['server']['virtualserver_queryclientsonline']),
        7 => array(1 => '[ping]', 2 => $ping),
        8 => array(1 => '[time]', 2 => date('H:i')),
        //9 => array(1 => '[date]', 2 => date('j') . '. ' . get_translated_name_of_month($config['lang'])), # TODO
        9 => array(1 => '[date]', 2 => date('j') . '. ' . $month[date('n')]),
        10 => array(1 => '[uptime]', 2 => floor($ts3['server']['virtualserver_uptime'] / 86400)),
    );

    foreach ($replaces as $replace) {
        $text = str_replace($replace[1], $replace[2], $text);
    }
    onImage($image, $item['x'], $item['y'], $text, $item['font'], $item['size'], $item['color']);
}

if (($weather_data['name']) != "") {
    if ($weather['icon']['status']) {
        $weathericon_image = imagecreatefrompng($weathericonfile); // TODO: if $weathericonfile exists?
        imagecopy($image, $weathericon_image, $weather['icon']['x'], $weather['icon']['y'], 0, 0, 80, 80);
    }
    if ($weather['city']['status']) {
        onImage($image, $weather['city']['x'], $weather['city']['y'], $weather_data['name'], $weather['city']['font'], $weather['city']['size'], $weather['city']['color']);
    }
    if ($weather['temp']['status']) {
        onImage($image, $weather['temp']['x'], $weather['temp']['y'], round((float) $weather_data['main']['temp'], 1) . '°C', $weather['temp']['font'], $weather['temp']['size'], $weather['temp']['color']);
    }
    if ($weather['description']['status']) {
        onImage($image, $weather['description']['x'], $weather['description']['y'], $weather_data['weather'][0]['description'], $weather['description']['font'], $weather['description']['size'], $weather['description']['color']);
    }
} elseif ($weather['city']['status']) {
    onImage($image, $weather['city']['x'], $weather['city']['y'], $client_city, $weather['city']['font'], $weather['city']['size'], $weather['city']['color']);
}

### POST IMAGE

if ($config['banner']['format'] == 'png') {
    header('Content-Type: image/png');
    imagepng($image);
} elseif ($config['banner']['format'] == 'jpg') {
    header('Content-Type: image/jpg');
    imagejpeg($image);
}
imagedestroy($image);
