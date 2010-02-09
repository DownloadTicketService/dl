<?php
// language initialization and support
require_once("gettext/gettext.inc");


// list of available and supported translations
$langData = array
(
  "EN" => "en_EN",
  "IT" => "it_IT",
  "DE" => "de_DE"
);


function detectLocale($locale)
{
  global $defLocale, $langData;

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

  return (isset($locale)? $locale: $defLocale);
}


function switchLocale($locale)
{
  T_setlocale(LC_ALL, $locale . ".utf8");
  T_bindtextdomain('messages', 'include/locale');
  T_textdomain('messages');
}


// internal encoding is always UTF-8
mb_internal_encoding("UTF-8");
