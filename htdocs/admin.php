<?php
// download ticket system
include("include/sesslang.php");
include("include/sessauth.php");
include("include/entry.php");

$act = (empty($_REQUEST["a"])? false: $_REQUEST["a"]);

if(!$auth || $act == $entryAuth || !check_token())
  include($entry[$entryAuth]['entry']);
elseif(isset($entry[$act]) && (!$entry[$act]['admin'] || $auth['admin']))
  include($entry[$act]['entry']);
else
  include($entry[$entryDefault]['entry']);
?>
