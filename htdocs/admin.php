<?php
// download ticket system
include("include/init.php");
include("include/auth.php");

if(!$auth)
  include("include/login.php");
else if(isset($_FILES["file"]) && is_uploaded_file($_FILES["file"]["tmp_name"]))
  include("include/result.php");
else if(isset($_REQUEST["l"]))
  include("include/list.php");
else
  include("include/submit.php");
?>
