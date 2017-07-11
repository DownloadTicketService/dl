<?php
// initialize session _and_ locale
require_once("sess.php");
$locale = &$_SESSION["locale"];
$locale = detectLocale($_SESSION['locale']);
switchLocale($locale);
