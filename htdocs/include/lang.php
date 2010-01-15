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
if(!isset($locale) && !empty($_SERVER["HTTP_ACCEPT_LANGUAGE"]))
{
  // TODO: shoud use something like PECL's http_negotiate_language
  $accept = split(",", $_SERVER["HTTP_ACCEPT_LANGUAGE"]);
  foreach($accept as $al)
  {
    if(array_search($all, $langData))
    {
      $locale = $al;
      break;
    }
  }
}

// initialize language support
if(!isset($locale)) $locale = $defLocale;
setlocale(LC_ALL, $locale . ".utf8");
bindtextdomain('messages', 'include/locale');
textdomain('messages');
