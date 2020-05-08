<?php
require_once __DIR__ . "/config.php";
$ip = getenv('HTTP_CLIENT_IP') ?: getenv('HTTP_X_FORWARDED_FOR') ?: getenv('HTTP_X_FORWARDED') ?: getenv('HTTP_FORWARDED_FOR') ?: getenv('HTTP_FORWARDED') ?: getenv('REMOTE_ADDR');
$adminonline = 0;
$nick = $config['img']['found_nick'];
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
				if (!file_exists('cache/'.$config['cache_name']) || filemtime('cache/'.$config['cache_name']) + 1 * 30 < time()) {
					$query = new ts3admin($config['ts3']['host'], $config['ts3']['query_port'], 2);
					$query->connect();
					$query->login($config['ts3']['login'],$config['ts3']['password']);
					$query->selectServer($config['ts3']['login_port']);
					$srv = [];
					$srv['server'] = $query->getElement('data', $query->serverInfo());
					$srv['groups'] = $query->getElement('data', $query->serverGroupList());
					$srv['clients'] = $query->getElement('data', $query->clientList('-uid -away -voice -times -groups -info -icon -country -ip'));
					$srv['channel'] = $query->getElement('data', $query->channelList());
					$srv['banlist'] = $query->getElement('data', $query->banList());

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
	//$json = file_get_contents('https://api.xman8830.ovh/weather?key='.$config['apikey'].'&ip=' . $ip);
	//$data = json_decode($json, true);
	//$weathericonfile = 'weathericon/' . $data['icon'] . '.png';

	foreach ($srv['clients'] as $client) {
		$groups = explode(',', $client['client_servergroups']);
		echo $groups;
		if ($client["connection_client_ip"] == $ip) {
			$nick = $client['client_nickname'];
		}
		foreach ($admingroups as $group) {
			if (in_array($group, $groups)) {
				$adminonline++;
			}
		}
	}
}
