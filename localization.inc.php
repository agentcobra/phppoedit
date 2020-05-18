<?php
$lang = current(str_replace('-', '_', explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']))).".UTF-8";

echo '<pre>' . print_r($lang, true) . '</pre>';

putenv('LC_ALL=' . $lang);

setlocale(LC_ALL, $lang);

bindtextdomain("phppoedit", "./locale");
textdomain("phppoedit");
