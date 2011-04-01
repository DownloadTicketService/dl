#!/usr/bin/env php
<?php
if(get_magic_quotes_runtime())
  set_magic_quotes_runtime(0);
if(!isset($argc)) die("not running from the command line\n");

system("xgettext -L php -F --keyword=T_:1 --omit-header ../*.php ../../style/include/* -o-");
