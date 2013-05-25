<?php
require_once("pages.php");
require_once("ticketfuncs.php");
require_once("style/include/style.php");
$act = "tlist";
$ref = pageLinkAct();
pageHeader();

if(isset($_REQUEST["purge"]) && !empty($_REQUEST["sel"]))
{
  $list = array();
  $sel = &$_REQUEST["sel"];
  if(!is_array($sel)) $sel = array($sel);

  // purge immediately
  foreach($sel as $id)
  {
    if(!isTicketId($id)) continue;
    $sql = "SELECT * FROM ticket WHERE id = " . $db->quote($id);
    $DATA = $db->query($sql)->fetch();
    if($DATA === false) continue;

    // check for permissions
    if(!$auth["admin"] && $DATA["user_id"] != $auth["id"])
      continue;

    // actually purge the ticket
    $list[] = htmlEntUTF8(ticketStr($DATA));
    ticketPurge($DATA, false);
  }

  if(count($list))
    infoMessage(T_("Purged"), $list);
}

// list active tickets
$totalSize = 0;

$sql = "SELECT t.*, u.name AS user FROM ticket t"
  . " LEFT JOIN user u ON u.id = t.user_id";
if(!$auth["admin"]) $sql .= " WHERE user_id = " . $auth["id"];
$sql .= " ORDER BY (user_id <> " . $auth["id"] . "), user_id, time";

?>
<script type="text/javascript">
  $(document).ready(function() { hideComments(); });
</script>

<form action="<?php echo $ref; ?>" method="post">
  <table id="tickets">
    <tr>
      <th><input class="element checkbox" type="checkbox" onclick="selectAll(this.checked);"/></th>
      <th></th>
      <th></th>
      <th></th>
      <th></th>
      <th><?php echo T_("Ticket"); ?></th>
      <th><?php echo T_("Size"); ?></th>
      <th><?php echo T_("Date"); ?> <img src="style/static/down.png"/></th>
    </tr>
<?php

foreach($db->query($sql) as $DATA)
{
  if(isTicketExpired($DATA)) continue;

  $totalSize += $DATA["size"];
  $our = ($DATA["user_id"] == $auth["id"]);
  $class = "file expanded " . $DATA['id'];
  if(!$our) $class .= " alien";
  echo "<tr class=\"$class\">";

  // selection
  echo "<td><input class=\"element checkbox\" type=\"checkbox\" name=\"sel[]\" value=\"" . $DATA['id'] . "\"/></td>";

  // tick
  echo "<td>";
  if($DATA["downloads"])
  {
    echo '<img title="' . T_("Successfully downloaded")
      . '" src="style/static/tick.png"/>';
  }
  echo "</td>";

  // download
  echo '<td><a href="' . ticketUrl($DATA) . '">'
    . '<img title="' . T_("Download")
    . '" src="style/static/save.png"/></a></td>';

  // delete
  echo "<td><a href=\"" . pageLinkAct(array('purge' => null, 'sel' => $DATA['id'])) . "\">"
    . "<img title=\"" . T_("Purge")
    . "\" src=\"style/static/cross.png\"/></a></td>";

  // edit
  echo "<td><a href=\"" . pageLink('tedit', array('id' => $DATA['id'])) . "\">"
    . "<img title=\"" . T_("Edit")
    . "\" src=\"style/static/edit.png\"/></a></td>";

  // name
  echo "<td onclick=\"toggleComment('" . $DATA['id'] . "');\" "
    . "class=\"filename\">" . htmlEntUTF8($DATA["name"]);
  $maxLen = ($styleTicketMaxLen - strlen($DATA['name']) - 3);
  if($DATA["cmt"] && $maxLen > 0)
  {
    echo ": <span class=\"comment\">";
    echo htmlEntUTF8(truncAtWord($DATA["cmt"], $maxLen));
    echo "</span>";
  }
  echo "</td>";

  // size/date
  echo "<td>" . humanSize($DATA["size"]) . "</td>";
  echo "<td>" . date("d/m/Y", $DATA["time"]) . "</td>";

  echo "</tr>";
  echo "<tr class=\"$class comment\">";
  // note: css madness
  for($i = 0; $i != 5; ++$i) echo "<td></td>";

  // comment
  echo "<td class=\"comment\">";
  if($DATA["cmt"])
    echo htmlEntUTF8(sliceWords($DATA["cmt"], $styleTicketLineLen));
  echo "</td>";

  // parameters */
  echo "<td class=\"fileinfo\" colspan=\"2\"><table>";

  // expire
  echo "<tr><th>" . T_("Expiry:") . " </th><td>"
    . ticketExpiry($DATA) . "</td></tr>";

  // owner
  if(!$our)
    echo "<tr><th>" . T_("User:") . " </th><td>" . htmlEntUTF8($DATA["user"]) . "</td></tr>";
  if(isset($DATA['pass_md5']))
    echo "<tr><th>" . T_("Password:") . " </th><td>" . str_repeat("&bull;", 5) . "</td>";

  // downloads
  if($DATA["downloads"])
  {
    echo "<tr><th>" . T_("Downloads:") . " </th><td>" . $DATA["downloads"] . "</td></tr>"
      . "<tr><th>" . T_("Downloaded:") . " </th><td>" . date("d/m/Y", $DATA["last_stamp"]) . "</td</tr>";
  }

  // notify
  if($DATA["notify_email"])
  {
    echo "<tr><th>" . T_("Notify:") . " </th><td>";
    $first = true;
    foreach(getEMailAddrs($DATA['notify_email']) as $email)
    {
      if($first) $first = false;
      else echo ", ";
      echo "<a href=\"mailto:" . urlencode($email) . "\">" .
	htmlEntUTF8($email) . "</a>";
    }
    echo "</td></tr>";
  }

  // sent-to
  if($DATA["sent_email"])
  {
    echo "<tr><th>" . T_("Sent to:") . " </th><td>";
    $first = true;
    foreach(getEMailAddrs($DATA['sent_email']) as $email)
    {
      if($first) $first = false;
      else echo ", ";
      echo "<a href=\"mailto:" . urlencode($email) . "\">" .
	htmlEntUTF8($email) . "</a>";
    }
    echo "</td></tr>";
  }

  echo "</table></td></tr>";
}

?>
  </table>

  <ul>
    <li class="buttons">
      <input type="submit" name="reload" value="<?php echo T_("Reload"); ?>"/>
      <input type="reset" value="<?php echo T_("Reset"); ?>"/>
      <input type="button" value="<?php echo T_("Select all"); ?>" onclick="selectAll();"/>
      <input type="submit" name="purge" value="<?php echo T_("Purge selected"); ?>"/>
    </li>
  </ul>
</form>

<p><?php printf(T_("Total archive size: %s"), humanSize($totalSize)); ?></p>

<?php
pageFooter();
?>
