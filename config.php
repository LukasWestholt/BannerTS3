<?php 
########################################
#									   #
#		Created by xman8830			   #
#									   #
########################################									

########################################
#									   #
#   	Version by Lukas Westholt      #
#									   #
########################################	
date_default_timezone_set('Europe/Berlin');//Time zone
$config['cache_name'] = 'server'; //Name cache
$config['ipinfo_token'] = ''; //https://ipinfo.io/account
$config['apikey_openweathermap'] = ''; //https://openweathermap.org/api
$config['ts3']['host'] = '';  //Host IP
$config['ts3']['login_port'] = '';  //Port Server
$config['ts3']['query_port'] = ''; //Port Query
$config['ts3']['login'] = ''; //Login Query
$config['ts3']['password'] = '';  //Password Query

$config['settings'] = 'auto'; // "auto" - refresh website or "bot" - screen bot refresh

$config['banner']['format'] = 'png'; //Format png/jpg
$config['banner']['background'] = 'img/banner.png'; //link to background png/jpg
$admingroups = array(6); //id admins groups
$config['img']['found_nick'] = 'unknown';// Nick if not found nick
$config['lang'] = 'DE';//Language PL/EN/DE
$weather = array(
	'status' => true,
	'icon' => array (//Icon weather
		'status' => true,
		'x' => 1300,
		'y' => 80
	),
	'description' => array (//Details weather
		'status' => true,
		'font' => '/fonts/Ubuntu-l.ttf',
		'color' => '#fff',
		'size' => 20,
		'x' => "right;-10",
		'y' => 230
	),
	'temp' => array (//Temperature
		'status' => true,
		'font' => '/fonts/Ubuntu-l.ttf',
		'color' => '#fff',
		'size' => 33,
		'x' => "right;-10",
		'y' => 190
	),
	'city' => array (//City
		'status' => true,
		'font' => '/fonts/Ubuntu-l.ttf',
		'color' => '#fff',
		'size' => 34,
		'x' => "right;-10",
		'y' => 50
	)
);

$img[] = array (
	'x' => 20,
	'y' => 50,
	'size' => 34,
	'text' => 'Admin online: [adminsOnline]',
	'font' => '/fonts/Ubuntu-l.ttf',
	'color' => '#fff',
); 

$img[] = array (
	'x' => "center",
	'y' => "center",
	'size' => 50,
	'text' => 'Hallo [nick]!',
	'font' => '/fonts/Ubuntu-l.ttf',
	'color' => '#fff',
); 

$img[] = array (
	'x' => "center",
	'y' => "center;50",
	'size' => 40,
	'text' => 'Jetzt online: [online]/[max]',
	'font' => '/fonts/Ubuntu-l.ttf',
	'color' => '#fff',
); 

$img[] = array (
	'x' => "right;-10",
	'y' => "bottom;60",
	'size' => 80,
	'text' => '[time]',
	'font' => '/fonts/Ubuntu-l.ttf',
	'color' => '#fff',
);

$img[] = array (
	'x' => "right;-30",
	'y' => "bottom;-80",
	'size' => 34,
	'text' => '[date]',
	'font' => '/fonts/Ubuntu-l.ttf',
	'color' => '#fff',
); 


$img[] = array (
	'x' => 20,
	'y' => "bottom;20",
	'size' => 30,
	'text' => 'Up: [uptime] T.',
	'font' => '/fonts/Ubuntu-l.ttf',
	'color' => '#fff',
); 
$img[] = array (
	'x' => "center;-30",
	'y' => "bottom;20",
	'size' => 34,
	'text' => 'Du bist hier auf dem Server zum [visit]. Mal',
	'font' => '/fonts/Ubuntu-l.ttf',
	'color' => '#fff',
); 

$img[] = array (
	'x' => "center",
	'y' => 50,
	'size' => 34,
	'text' => 'Ping: [ping]',
	'font' => '/fonts/Ubuntu-l.ttf',
	'color' => '#fff',
); 

?>
