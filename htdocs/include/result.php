<?php
// process a file submission

// import some data
$FILE = $_FILES["file"];

// generate new unique data
if(!file_exists($dataDir)) mkdir($dataDir);
$tmpFile = tempnam($dataDir, "");
$id = md5(rand() . "/" . microtime() . "/" . $tmpFile . "/" . $FILE["name"]);

// move data in the right place
if(!move_uploaded_file($FILE["tmp_name"], $tmpFile))
{
  include("failed.php");
  exit();
}

// prepare data
$DATA["name"] = basename($FILE["name"]);
$DATA["cmt"] = $_REQUEST["cmt"];
$DATA["time"] = time();
$DATA["downloads"] = 0;
$DATA["lastTime"] = 0;
if(!empty($_REQUEST["nl"]))
{
  $DATA["expire"] = 0;
  $DATA["expireLast"] = 0;
  $DATA["expireDln"] = 0;
}
else
{
  $DATA["expire"] = (!empty($_REQUEST["hr"])?
    $DATA["time"] + $_REQUEST["hr"] * 3600: 0);
  $DATA["expireLast"] = (!empty($_REQUEST["hra"])? $_REQUEST["hra"] * 3600: 0);
  $DATA["expireDln"] = (!empty($_REQUEST["dln"])? $_REQUEST["dln"]: 0);
}
$DATA["email"] = str_replace(array(";", "\n"), ",", $_REQUEST["nt"]);
$DATA["path"] = $tmpFile;
$DATA["size"] = $FILE["size"];
dba_insert($id, serialize($DATA), $tDb);

$perm = ($DATA["expire"] == 0 &&
    $DATA["expireLast"] == 0 &&
    $DATA["expireDln"] == 0);


// final url
$url = $masterPath . "?t=" . $id;
?>
<html>
  <body>
    Your ticket (<?php
	echo htmlentities($DATA["name"]) . "): " .
	  htmlentities($DATA["cmt"]); ?>
    <ul>
    <?php if($perm) echo "<li>Is a <strong>permanent</strong> ticket"; ?>
    <li>E-Mail: <a href="<?php echo "mailto:?body=$url"; ?>">send an email</a> with this ticket
    <li>Download: <a href="<?php echo $url; ?>">download directly</a>
    <li>URL: <?php echo $url; ?>
    </ul><hr/>
    <a href="<?php echo $masterPath; ?>">Submit another</a>,
    <a href="<?php echo $masterPath; ?>?l">List active tickets</a>,
    <a href="<?php echo $masterPath; ?>?p">Logout</a>
  </body>
</html>
