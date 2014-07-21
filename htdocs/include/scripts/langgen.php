#!/usr/bin/env php
<?php
if(!isset($argc)) die("not running from the command line\n");

// data
require_once("../prelude.php");
require_once("lang.php");

// regenerate reference strings
$file = tempnam(sys_get_temp_dir(), 'dl');
system("xgettext -L php -F --keyword=T_:1"
    . " --from-code UTF-8 --omit-header -w 1 --no-wrap -F"
    . " ../*.php ../../style/include/*"
    . " -o " . escapeshellarg($file), $ret);
if($ret)
{
  unlink($file);
  die("error while running xgettext!\n");
}

// cycle through configured languages and update strings
foreach($langData as $lang => $v)
{
  $dir = '../locale/' . $v['locale'] . '/LC_MESSAGES';
  if($lang == "EN" && !file_exists($dir)) continue;

  echo "updating strings for locale $lang: ";
  system("msgmerge --previous --no-wrap -w 1 -F -N"
      . " --lang " . escapeshellarg($v['locale'])
      . " -U " . escapeshellarg("$dir/messages.po")
      . " " . escapeshellarg($file), $ret);
}

unlink($file);
