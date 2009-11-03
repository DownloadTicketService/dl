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
 * Advanced settings (defaults commented)
 */

// cfgVersion: configuration file version
$cfgVersion = "0.3";

// phpExt: external PHP extension
//         you can use "" to hide PHP exposure if you enable apache's
//         MultiViews option or use an URL rewriting mechanism
//$phpExt = ".php";

// maxSize: maximum upload filesize (defaulting to PHP's setting)
//          note that changing maxSize does *not* enforce upload_max_filesize
//$maxSize = ini_get('upload_max_filesize');

// authRealm: HTTP authentication realm
//            When using HTTP authentication, authRealm should match the HTTP
//            realm name in order for "logout" to have any effect.
//$authRealm = "Restricted Area";

// dbHandler: default dba backend (db4 works for most PHP versions)
//            change to db3/db2/dbm/flatfile depending on your php version
//            see http://us.php.net/manual/en/dba.requirements.php
//$dbHandler = "db4";

// sessionName: PHP session name (to ensure session uniqueness)
//		The default name is generated thus:
//$sessionName = "DL" . md5($masterPath);
?>
