<?php
// download ticket system
include("include/sess.php");
include("include/auth.php");
include("include/entry.php");

$act = (empty($_REQUEST["a"])? false: $_REQUEST["a"]);

if(!$auth || $act == $entryAuth)
  include($entry[$entryAuth]['entry']);
elseif(isset($entry[$act]) && (!$entry[$act]['admin'] || $auth['admin']))
  include($entry[$act]['entry']);
else
  include($entry[$entryDefault]['entry']);
?>
