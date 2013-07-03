<?php
// download ticket system
include("include/sesslang.php");
include("include/sessauth.php");
include("include/entry.php");

$act = (empty($_REQUEST["a"]) || !is_string($_REQUEST["a"])? false: $_REQUEST["a"]);

if($act != false && (!check_referer() || !check_token()))
  header("Location: $adminPath");
elseif(!$auth || $act == $entryAuth)
  include($entry[$entryAuth]['entry']);
elseif(isset($entry[$act]) && (!$entry[$act]['admin'] || $auth['admin']))
  include($entry[$act]['entry']);
else
  include($entry[$entryDefault]['entry']);
?>
