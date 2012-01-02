#!/usr/bin/env php
<?php
if(!isset($argc)) die("not running from the command line\n");
require_once("../prelude.php");
system("xgettext -L php -F --keyword=T_:1 --omit-header ../*.php ../../style/include/* -o-");
