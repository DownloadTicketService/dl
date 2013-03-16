<?php
// language initialization and support
require_once("gettext/gettext.inc");


// list of available and supported translations
$langData = array
(
  "DE" => "de_DE",
  "EN" => "en_EN",
  "ES" => "es_ES",
  "FR" => "fr_FR",
  "IT" => "it_IT",
);


function detectLocale($locale)
{
  global $defLocale, $langData, $cookieLifetime, $langCookie;

  // language selection/detection
  if(!empty($_REQUEST['lang']) || !empty($_COOKIE[$langCookie]))
  {
    if(!empty($_GET['lang']))
      $lang = $_GET['lang'];
    elseif(!empty($_POST['lang']))
      $lang = $_POST['lang'];
    else
      $lang = $_COOKIE[$langCookie];

    if(isset($langData[$lang]))
    {
      // abide to user preferences
      $locale = $langData[$lang];
      if(!empty($_GET['lang']) || !empty($_POST['lang']))
	setcookie($langCookie, $lang, time() + $cookieLifetime);
    }
  }

  // try to detect browser preferences
  if(!isset($locale) && !empty($_SERVER["HTTP_ACCEPT_LANGUAGE"]))
  {
    // TODO: shoud use something like PECL's http_negotiate_language
    $accept = explode(",", $_SERVER["HTTP_ACCEPT_LANGUAGE"]);
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
  global $helpPath, $helpRoot, $masterPath, $defLocale;

  T_setlocale(LC_ALL, $locale . ".utf8");
  T_bindtextdomain('messages', 'include/locale');
  T_textdomain('messages');

  if(file_exists("$helpRoot/$locale"))
    $helpPath = "$masterPath$helpRoot/$locale/";
  elseif(file_exists("$helpRoot/$defLocale"))
    $helpPath = "$masterPath$helpRoot/$defLocale/";
  else
    $helpPath = "$masterPath$helpRoot/en_EN/";
}


function withLocale($locale, $func, $params)
{
  $curLocale = $GLOBALS['locale'];
  if(!$locale) $locale = $GLOBALS['defLocale'];
  if($curLocale == $locale)
    call_user_func_array($func, $params);
  else
  {
    switchLocale($locale);
    call_user_func_array($func, $params);
    switchLocale($curLocale);
  }
}
