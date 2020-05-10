# BannerTS3

<hr>

**Banner** - version 1.4<br>

<hr>

#### Installation
Put the whole BannerTS3\ directory into your webspace.

Rename the <code>config.php.dist</code> file to <code>config.php</code> and put your values in it.
You will need an API token by https://ipinfo.io/account and an API token by https://openweathermap.org/api.
And for the data request from your ts3 server you will need your login credential for the query login.
You need "Host IP", "Port Server", "Port Query", "Login Query", "Password Query".

Make sure that the "cache/" folder has the permission the read/write and create new folders (0777). 

That is all. 



#### Function
- Username
- Admins online
- Channel online
- Uptime server
- Server visit
- Max slots server
- Online users
- Ping server
- Time
- Date
- Weather system (get the location of the user by IP-API)
- Caching of ipadress/location relationship for less api usage
- Caching of ts3server data for less data requests to the server

#### Changelog in v 1.4
- I have rewritten the whole code. Big update. New features. Better Apis.

#### Changelog in v 1.3
- add Option screen bot refresh or refresh web


if option "bot" start bot:
- <code>chmod 777 run</code>
- <code>./run start</code> start bot
- <code>./run stop</code> stop bot

[![Website screenshot](https://i.imgur.com/EFAzDD8.jpg)](https://i.imgur.com/EFAzDD8.jpg)
