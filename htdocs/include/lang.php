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
  global $defLocale, $langData, $cookieLifetime;

  // language selection/detection
  if(!empty($_REQUEST['lang']))
  {
    if(!empty($_GET['lang']))
      $lang = $_GET['lang'];
    elseif(!empty($_POST['lang']))
      $lang = $_POST['lang'];
    else
      $lang = $_COOKIE['lang'];

    if(isset($langData[$lang]))
    {
      // abide to user preferences
      $locale = $langData[$lang];
      if(!empty($_GET['lang']) || !empty($_POST['lang']))
	setcookie('lang', $lang, time() + $cookieLifetime);
    }
  }
  
  // try to detect browser preferences
  if(!isset($locale) && !empty($_SERVER["HTTP_ACCEPT_LANGUAGE"]))
  {
    // TODO: shoud use something like PECL's http_negotiate_language
    $accept = split(",", $_SERVER["HTTP_ACCEPT_LANGUAGE"]);
    foreach($accept as $al)
    {
      if(array_search($al, $langData))
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


function withLocale($locale, $func, $params)
{
  $curLocale = $GLOBALS['locale'];
  switchLocale($locale);
  call_user_func_array($func, $params);
  switchLocale($curLocale);
}


function withDefLocale($func, $params)
{
  withLocale($GLOBALS['defLocale'], $func, $params);
}


// internal encoding is always UTF-8
mb_internal_encoding("UTF-8");
