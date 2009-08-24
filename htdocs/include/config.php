<?php
/*
 * Basic settings
 */

// masterPath: externally visible URL
$masterPath = "http://dl.example.com/";

// fromAddr: "From:" address for outgoing e-mails
$fromAddr = "ticket service <nobody@example.com>";

// spoolDir: spool directory for uploaded files, ticket and user databases
$spoolDir = "/var/spool/dl/";


/*
 * Advanced settings
 */

// phpExt: external PHP extension
//         you can use "" to hide PHP exposure if you enable apache's
//         MultiViews option or use an URL rewriting mechanism
$phpExt = ".php";

// maxSize: maximum upload filesize (defaulting to PHP's setting)
//          note that changing maxSize does *not* enforce upload_max_filesize
$maxSize = ini_get('upload_max_filesize');

// dbHandler: default dba backend (db4 works for most PHP versions)
//            change to db3/db2/dbm/flatfile depending on your php version
//            see http://us.php.net/manual/en/dba.requirements.php
$dbHandler = "db4";

// sessionName: PHP session name (to ensure session uniqueness)
$sessionName = "DL" . md5($masterPath);
?>
