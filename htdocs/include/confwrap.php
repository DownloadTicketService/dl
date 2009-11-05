<?php
// version-indepenent configuration variables
require_once("config.php");
require_once("funcs.php");

// variables
if(!isset($cfgVersion)) $cfgVersion = "0.4";
if(!isset($phpExt)) $phpExt = ".php";
if(!isset($maxSize)) $maxSize = ini_get('upload_max_filesize');
if(!isset($authReal)) $authRealm = "Restricted Area";
if(!isset($dbHandler)) $dbHandler = "db4";
if(!isset($sessionName)) $sessionName = "DL" . md5($masterPath);

// derived data
$useSysLog = (!empty($logFile) && strstr($logFile, "/") === FALSE);
$iMaxSize = returnBytes($maxSize);
$tDbPath = $spoolDir . "/data.db";
$uDbPath = $spoolDir . "/user.db";
$Path = $spoolDir . "/data.db";
$dataDir = $spoolDir . "/data";
$adminPath = $masterPath . "admin$phpExt";
$dPath = $masterPath . "d$phpExt";
