<?php
// language initialization and support
require_once("gettext/gettext.inc");


// list of available and supported translations
$langData = array
(
  'DE' => array('locale' => 'de_DE', 'name' => 'Deutsch'),
  'EN' => array('locale' => 'en_EN', 'name' => 'English'),
  'ES' => array('locale' => 'es_ES', 'name' => 'Español'),
  'FR' => array('locale' => 'fr_FR', 'name' => 'Français'),
  'IT' => array('locale' => 'it_IT', 'name' => 'Italiano'),
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
      $locale = $langData[$lang]['locale'];
      if(!empty($_GET['lang']) || !empty($_POST['lang']))
	setcookie('lang', $lang, time() + $cookieLifetime);
    }
  }

  // try to detect browser preferences
  if(!isset($locale) && !empty($_SERVER["HTTP_ACCEPT_LANGUAGE"]))
  {
    // TODO: shoud use something like PECL's http_negotiate_language
    $accept = explode(",", $_SERVER["HTTP_ACCEPT_LANGUAGE"]);

    $localeList = array();
    foreach($langData as $k => $v)
      $localeList[$v['locale']] = $k;

    foreach($accept as $al)
    {
      if(isset($localeList[$al]))
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
  global $helpPath, $helpRoot, $masterPath, $defLocale, $incPath;

  T_setlocale(LC_ALL, $locale . ".utf8");
  T_bindtextdomain('messages', "$incPath/locale");
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
