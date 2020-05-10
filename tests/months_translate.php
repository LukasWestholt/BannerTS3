<?php
$config = [];
$config['lang'] = "ru";
// you can add here a language
# TODO: find a better way

function get_translated_name_of_month($language)
{
    $currentLocale = setlocale(LC_ALL, 0);
    echo $currentLocale . PHP_EOL; //outputs C/en_US.UTF-8/C/C/C/C on my machine
    setlocale(LC_TIME, $language);
    $month = utf8_encode(strftime('%B'));
    setlocale(LC_TIME, "C");
    $currentLocale = setlocale(LC_ALL, 0);
    echo $currentLocale . PHP_EOL;
    return $month;
}
echo(utf8_encode(strftime('%B')).PHP_EOL);
echo(get_translated_name_of_month("pl").PHP_EOL);
echo(utf8_encode(strftime('%B')));