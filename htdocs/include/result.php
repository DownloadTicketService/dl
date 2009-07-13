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

// final url
$url = $masterPath . "?t=" . $id;
$subject = "download link to '" . $DATA["name"] . "'";
if(!empty($DATA["cmt"]))
  $subject .= " (" . $DATA["cmt"] . ")";
$mailto = "mailto:?subject=$subject&body=" . urlencode($url);

$title = 'Upload Result';
includeTemplate('style/include/header.php', compact('title'));
?>

<div>
  <label class="description">Your ticket (<?php
echo htmlentities($DATA["name"]) . "): " .
htmlentities($DATA["cmt"]); ?></label>
<p><span class="ticketid"><?php echo htmlentities($url); ?></span></p>
</div>

<span class="buttons">
  <input type="button" onclick="document.location=&quot;<?php echo htmlentities($mailto); ?>&quot;" value="Send via E-Mail"/>
  <input type="button" onclick="document.location=&quot;<?php echo htmlentities($url); ?>&quot;" value="Download"/>
</span>

<div id="footer">
  <a href="<?php echo $masterPath; ?>">Submit another</a>,
  <a href="<?php echo $masterPath; ?>?l">List active tickets</a>,
  <a href="<?php echo $masterPath; ?>?p">Logout</a>
</div>

<?php
includeTemplate('style/include/footer.php');
?>
