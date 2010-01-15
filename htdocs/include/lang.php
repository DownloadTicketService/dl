<?php
// language initialization and support

$langData = array
(
  "EN" => "en_EN",
  "IT" => "it_IT",
  "DE" => "de_DE"
);


// language selection/detection
if(!empty($_REQUEST['lang']))
{
  // abide to user preferences
  if(isset($langData[$_REQUEST['lang']]))
    $locale = $langData[$_REQUEST['lang']];
}

// try to detect browser preferences
if(!isset($locale))
{
  $supported = array_values($langData);
  array_unshift($langData[$defLocale]);
  $locale = http_negotiate_language($supported);
}

// initialize language support
setlocale(LC_ALL, $locale . ".utf8");
bindtextdomain('messages', 'include/locale');
textdomain('messages');
