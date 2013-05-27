#!/usr/bin/env php
<?php
if(!isset($argc)) die("not running from the command line\n");

// data
require_once("../prelude.php");
require_once("lang.php");

// cycle through configured languages and regenerate them
foreach($langData as $lang => $v)
{
  $dir = '../locale/' . $v['locale'] . '/LC_MESSAGES';
  if($lang == "EN" && !file_exists($dir)) continue;

  echo "checking locale $lang: ";
  $po = "$dir/messages.po";
  $mo = "$dir/messages.mo";
  $poSt = stat($po);
  $moSt = @stat($mo);
  if(!$moSt || $moSt['mtime'] < $poSt['mtime'])
  {
    echo "regenerating... ";
    system("cd " . escapeshellarg($dir) . " && msgfmt messages.po");
  }
  echo "ok\n";
}

// cycle through available guides and regenerate them
foreach($langData as $lang => $v)
{
  $dir = '../../static/guide/' . $v['locale'];
  if($lang == "EN" || !file_exists($dir)) continue;

  echo "checking guide $lang: ";
  $txt = "$dir/index.txt";
  $html = "$dir/index.html";
  $txtSt = stat($txt);
  $htmlSt = @stat($html);
  if(!$htmlSt || $htmlSt['mtime'] < $txtSt['mtime'])
  {
    echo "regenerating... ";
    $lang = strtolower($lang);
    system("cd " . escapeshellarg($dir) . " && rst2html -l '$lang' index.txt > index.html");
  }
  echo "ok\n";
}

echo "done\n";
exit(0);
