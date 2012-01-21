<?php
// process a grant request

function handleGrant()
{
  global $auth, $locale, $db, $defaults;

  // generate new unique id
  list($usec, $sec) = microtime();
  $id = md5(rand() . "/$usec/$sec/" . $_POST["nt"]);

  // defaults
  if(!isset($_POST["gn"]))
    $_POST["gn"] = $defaults['grant']['total'] / (3600 * 24);

  // prepare data
  $sql = "INSERT INTO grant (id, user_id, grant_expire, cmt, pass_md5"
    . ", time, last_time, expire, expire_dln, notify_email, sent_email, locale) VALUES (";
  $sql .= $db->quote($id);
  $sql .= ", " . $auth['id'];
  $sql .= ", " . (($_POST["gn"] == 0)? 'NULL': time() + $_POST["gn"] * 3600 * 24);
  $sql .= ", " . (empty($_POST["cmt"])? 'NULL': $db->quote($_POST["cmt"]));
  $sql .= ", " . (empty($_POST["pass"])? 'NULL': $db->quote(md5($_POST["pass"])));
  $sql .= ", " . time();
  if(!empty($_POST["nl"]))
  {
    $sql .= ", NULL";
    $sql .= ", NULL";
    $sql .= ", NULL";
  }
  else
  {
    if(!isset($_POST["hra"]) && !isset($_POST["dn"]) && !isset($_POST["dln"]))
    {
      $_POST["dn"] = $defaults['ticket']['total'] / (3600 * 24);
      $_POST["hra"] = $defaults['ticket']['lastdl'] / 3600;
      $_POST["dln"] = $defaults['ticket']['maxdl'];
    }
    $sql .= ", " . (empty($_POST["hra"])? 'NULL': $_POST["hra"] * 3600);
    $sql .= ", " . (empty($_POST["dn"])? 'NULL': time() + $_POST["dn"] * 3600 * 24);
    $sql .= ", " . (empty($_POST["dln"])? 'NULL': (int)$_POST["dln"]);
  }
  $sql .= ", " . (empty($_POST["nt"])? 'NULL': $db->quote(fixEMailAddrs($_POST["nt"])));
  $sql .= ", " . (empty($_POST["st"])? 'NULL': $db->quote(fixEMailAddrs($_POST["st"])));
  $sql .= ", " . $db->quote($locale);
  $sql .= ")";

  if($db->exec($sql) != 1)
    return false;

  // fetch defaults
  $sql = "SELECT * FROM grant WHERE id = " . $db->quote($id);
  $DATA = $db->query($sql)->fetch();
  $DATA['pass'] = (empty($_POST["pass"])? NULL: $_POST["pass"]);

  // trigger creation hooks
  onGrantCreate($DATA);

  return $DATA;
}


// resulting page
$DATA = false;
if(!empty($_POST["nt"]) && isset($_POST["gn"]))
  $DATA = handleGrant();

if($DATA !== false)
  include("newgrantr.php");
else
  include("newgrants.php");
?>
