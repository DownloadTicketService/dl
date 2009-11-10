<?php
// process a file submission

function fail($tmpFile = false)
{
  if($tmpFile) unlink($tmpFile);
  include("submit.php");
  exit();
}

$FILE = $_FILES["file"];
if($FILE['error'] != UPLOAD_ERR_OK)
  fail();

// generate new unique id/file name
if(!file_exists($dataDir)) mkdir($dataDir);
do
{
  $id = md5(rand() . "/" . microtime() . "/" . $FILE["name"]);
  $tmpFile = "$dataDir/$id";
}
while(fopen($tmpFile, "x") === FALSE);
if(!move_uploaded_file($FILE["tmp_name"], $tmpFile))
  fail($tmpFile);

// prepare data
$sql = "INSERT INTO tickets (id, owner, name, path, size, cmt, time"
  . ", expire, expire_last, expire_dln, notify_email) VALUES (";
$sql .= $db->quote($id);
$sql .= ", " . $auth['id'];
$sql .= ", " . $db->quote(basename($FILE["name"]));
$sql .= ", " . $db->quote($tmpFile);
$sql .= ", " . $FILE["size"];
$sql .= ", " . (empty($_POST["cmt"])? 'NULL': $db->quote($_POST["cmt"]));
$sql .= ", " . time();
if(!empty($_POST["nl"]))
{
  $sql .= ", NULL";
  $sql .= ", NULL";
  $sql .= ", NULL";
}
else
{
  $sql .= ", " . (empty($_POST["dn"])? 'NULL': time() + $_POST["dn"] * 3600 * 24);
  $sql .= ", " . (empty($_POST["hra"])? 'NULL': $_POST["hra"] * 3600);
  $sql .= ", " . (empty($_POST["dln"])? 'NULL': (int)$_POST["dln"]);
}
$sql .= ", " . (empty($_POST["nt"])? 'NULL':
    $db->quote(str_replace(array(";", "\n"), ",", $_POST["nt"])));
$sql .= ")";

if($db->exec($sql) != 1)
  fail($tmpFile);

// fetch defaults
$sql = "SELECT * FROM tickets WHERE ROWID = last_insert_rowid()";
$DATA = $db->query($sql)->fetch();

// final url
$url = ticketUrl($DATA);
$subject = 'download link to ' . humanTicketStr($DATA);
$mailto = "mailto:?subject=$subject&body=" . urlencode($url);

// trigger creation hooks
onCreate($DATA);

$title = 'Upload Result';
includeTemplate('style/include/header.php', compact('title'));
?>

<div>
  <label class="description">Your ticket
<?php echo htmlentities(humanTicketStr($DATA)); ?>
  </label>
<p><span class="ticketid"><?php echo htmlentities($url); ?></span></p>
</div>

<span class="buttons">
  <input type="button" onclick="document.location=&quot;<?php echo htmlentities($mailto); ?>&quot;" value="Send via E-Mail"/>
  <input type="button" onclick="document.location=&quot;<?php echo htmlentities($url); ?>&quot;" value="Download"/>
</span>

<div id="footer">
  <a href="<?php echo $adminPath; ?>">Submit another</a>,
  <a href="<?php echo $adminPath; ?>?l">List active tickets</a>,
  <a href="<?php echo $adminPath; ?>?u">Logout</a>
</div>

<?php
includeTemplate('style/include/footer.php');
?>
