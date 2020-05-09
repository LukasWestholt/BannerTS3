<?php
$weather = array(
    'status' => true,
    'icon' => array ( //Icon weather
        'status' => true,
        'x' => 1300,
        'y' => 80
    ),
    'description' => array ( //Details weather
        'status' => true,
        'font' => '/fonts/Ubuntu-l.ttf',
        'color' => '#fff',
        'size' => 20,
        'x' => "right;-10",
        'y' => 230
    ),
    'temp' => array ( //Temperature
        'status' => true,
        'font' => '/fonts/Ubuntu-l.ttf',
        'color' => '#fff',
        'size' => 33,
        'x' => "right;-10",
        'y' => 190
    ),
    'city' => array ( //City
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
    'en' => 'admin online: [adminsOnline]',
    'de' => 'Admin online: [adminsOnline]',
    'pl' => 'Admini online: [adminsOnline]',
    'font' => '/fonts/Ubuntu-l.ttf',
    'color' => '#fff',
    'needs_teamspeak_server_connection' => true,
);

$img[] = array (
    'x' => "center",
    'y' => "center",
    'size' => 50,
    'en' => 'Hello [nick]!',
    'de' => 'Hallo [nick]!',
    'pl' => 'Nick [nick]',
    'font' => '/fonts/Ubuntu-l.ttf',
    'color' => '#fff',
    'needs_teamspeak_server_connection' => false,
);

$img[] = array (
    'x' => "center",
    'y' => "center;50",
    'size' => 40,
    'en' => 'Now online: [online]/[max]',
    'de' => 'Jetzt online: [online]/[max]',
    'pl' => 'online: [online]/[max]',
    'font' => '/fonts/Ubuntu-l.ttf',
    'color' => '#fff',
    'needs_teamspeak_server_connection' => true,
);

$img[] = array (
    'x' => "right;-10",
    'y' => "bottom;60",
    'size' => 80,
    'en' => '[time]',
    'font' => '/fonts/Ubuntu-l.ttf',
    'color' => '#fff',
    'needs_teamspeak_server_connection' => false,
);

$img[] = array (
    'x' => "right;-30",
    'y' => "bottom;-80",
    'size' => 34,
    'en' => '[date]',
    'font' => '/fonts/Ubuntu-l.ttf',
    'color' => '#fff',
    'needs_teamspeak_server_connection' => false,
);


$img[] = array (
    'x' => 20,
    'y' => "bottom;20",
    'size' => 30,
    'en' => 'uptime: [uptime] days',
    'de' => 'Up: [uptime] T.',
    'pl' => 'uptime: [uptime] dni',
    'font' => '/fonts/Ubuntu-l.ttf',
    'color' => '#fff',
    'needs_teamspeak_server_connection' => true,
);
$img[] = array (
    'x' => "center;-30",
    'y' => "bottom;20",
    'size' => 34,
    'en' => 'The [visit]. visit on this server',
    'de' => 'Du bist hier auf dem Server zum [visit]. Mal',
    'pl' => 'Odwiedzin: [visit]',
    'font' => '/fonts/Ubuntu-l.ttf',
    'color' => '#fff',
    'needs_teamspeak_server_connection' => true,
);

$img[] = array (
    'x' => "center",
    'y' => 50,
    'size' => 34,
    'en' => 'ping: [ping]',
    'de' => 'Ping: [ping]',
    'font' => '/fonts/Ubuntu-l.ttf',
    'color' => '#fff',
    'needs_teamspeak_server_connection' => true,
);
