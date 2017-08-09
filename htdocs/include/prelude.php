<?php
// check and setup the PHP runtime
if(version_compare(phpversion(), "5.5", "<"))
  die("outdated PHP version: please upgrade to 5.5 or higher\n");
set_include_path("." . PATH_SEPARATOR . dirname(realpath(__FILE__)));
mb_internal_encoding("UTF-8");
