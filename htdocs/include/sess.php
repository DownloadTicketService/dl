<?php
// initialize the session
include("init.php");
require_once("admfuncs.php");

// expire tickets before serving any request
if($gcInternal === true
&& ($gcProbability === 1.
 || (mt_rand() / mt_getrandmax() < $gcProbability)))
  runGc();

// start the session and session-global variables
ini_set('session.use_cookies', 1);
ini_set("session.use_only_cookies", 1);
session_set_cookie_params(0, $parsedMasterPath['path']);
session_name('sid');
session_start();
$auth = &$_SESSION['auth'];
$token = &$_SESSION['token'];
if(!isset($token))
  restart_session();

?>
