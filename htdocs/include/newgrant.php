<?php
// process a grant request

function handleGrant()
{
  global $auth, $locale, $db, $defaults;

  // generate new unique id
  list($usec, $sec) = microtime();
  $id = md5(rand() . "/$usec/$sec/" . $_POST["notify"]);

  // defaults
  if(!isset($_POST["grant_total"]))
    $_POST["grant_total"] = $defaults['grant']['total'] / (3600 * 24);

  // prepare data
  $sql = "INSERT INTO grant (id, user_id, grant_expire, cmt, pass_md5"
    . ", time, expire, last_time, expire_dln, notify_email, sent_email, locale) VALUES (";
  $sql .= $db->quote($id);
  $sql .= ", " . $auth['id'];
  $sql .= ", " . (($_POST["grant_total"] == 0)? 'NULL': time() + $_POST["grant_total"] * 3600 * 24);
  $sql .= ", " . (empty($_POST["comment"])? 'NULL': $db->quote($_POST["comment"]));
  $sql .= ", " . (empty($_POST["pass"])? 'NULL': $db->quote(md5($_POST["pass"])));
  $sql .= ", " . time();
  if(!empty($_POST["ticket_permanent"]))
  {
    $sql .= ", NULL";
    $sql .= ", NULL";
    $sql .= ", NULL";
  }
  else
  {
    if(!isset($_POST["ticket_totaldays"]) && !isset($_POST["ticket_lastdldays"]) && !isset($_POST["ticket_maxdl"]))
    {
      $_POST["ticket_totaldays"] = $defaults['ticket']['total'] / (3600 * 24);
      $_POST["ticket_lastdldays"] = $defaults['ticket']['lastdl'] / (3600 * 24);
      $_POST["ticket_maxdl"] = $defaults['ticket']['maxdl'];
    }
    $sql .= ", " . (empty($_POST["ticket_totaldays"])? 'NULL': time() + $_POST["ticket_totaldays"] * 3600 * 24);
    $sql .= ", " . (empty($_POST["ticket_lastdldays"])? 'NULL': $_POST["ticket_lastdldays"] * 3600 * 24);
    $sql .= ", " . (empty($_POST["ticket_maxdl"])? 'NULL': (int)$_POST["ticket_maxdl"]);
  }
  $sql .= ", " . (empty($_POST["notify"])? 'NULL': $db->quote(fixEMailAddrs($_POST["notify"])));
  $sql .= ", " . (empty($_POST["send_to"])? 'NULL': $db->quote(fixEMailAddrs($_POST["send_to"])));
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
if(!empty($_POST["notify"]) && isset($_POST["grant_total"]))
  $DATA = handleGrant();

if($DATA !== false)
  include("newgrantr.php");
else
  include("newgrants.php");
?>
