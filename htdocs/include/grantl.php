<?php
require_once("pages.php");
require_once("style/include/style.php");
$act = "glist";
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
    if(!isGrantId($id)) continue;
    $sql = "SELECT * FROM \"grant\" WHERE id = " . $db->quote($id);
    $DATA = $db->query($sql)->fetch();
    if($DATA === false) continue;

    // check for permissions
    if(!$auth["admin"] && $DATA["user_id"] != $auth["id"])
      continue;

    // actually purge the grant
    $list[] = htmlEntUTF8(grantStr($DATA));
    grantPurge($DATA, false);
  }

  if(count($list))
    infoMessage(T_("Purged"), $list);
}

// list active grants
$sql = 'SELECT g.*, u.name AS "user" FROM "grant" g'
  . ' LEFT JOIN "user" u ON u.id = g.user_id';
if(!$auth["admin"]) $sql .= " WHERE user_id = " . $auth["id"];
$sql .= " ORDER BY (user_id <> " . $auth["id"] . "), user_id, time";

?>
<script type="text/javascript">
  $(document).ready(function() { hideComments(); });
</script>

<form action="<?php echo $ref; ?>" method="post">
  <table id="grants">
    <tr>
      <th><input class="element checkbox" type="checkbox" onclick="selectAll(this.checked);"/></th>
      <th></th>
      <th></th>
      <th><?php echo T_("Grant"); ?></th>
      <th><?php echo T_("Expiry"); ?></th>
      <th><?php echo T_("Date"); ?> <img src="style/static/down.png"/></th>
    </tr>
<?php

foreach($db->query($sql) as $DATA)
{
  if(isGrantExpired($DATA)) continue;

  $our = ($DATA["user_id"] == $auth["id"]);
  $class = "file expanded " . $DATA['id'];
  if(!$our) $class .= " alien";
  echo "<tr class=\"$class\">";

  // selection
  echo "<td><input class=\"element checkbox\" type=\"checkbox\" name=\"sel[]\" value=\"" . $DATA['id'] . "\"/></td>";

  // upload
  echo '<td><a href="' . grantUrl($DATA) . '">'
    . '<img title="' . T_("Upload")
    . '" src="style/static/upload.png"/></a></td>';

  // delete
  echo "<td><a href=\"" . pageLinkAct(array('purge' => null, 'sel' => $DATA['id'])) . "\">"
    . "<img title=\"" . T_("Purge")
    . "\" src=\"style/static/cross.png\"/></a></td>";

  // name
  echo "<td onclick=\"toggleComment('" . $DATA['id'] . "');\" "
    . "class=\"filename\"><span class=\"ticketid\">"
    . htmlEntUTF8($DATA['id']) . "</span>";
  $maxLen = ($styleGrantMaxLen - strlen($DATA['id']) - 3);
  if($DATA["cmt"] && $maxLen > 0)
  {
    echo ": <span class=\"comment\">";
    echo htmlEntUTF8(truncAtWord($DATA["cmt"], $maxLen));
    echo "</span>";
  }
  echo "</td>";

  // expire
  echo "<td>" . grantExpiry($DATA) . "</td>";

  // date
  echo "<td>" . date("d/m/Y T", $DATA["time"]) . "</td>";

  echo "</tr>";
  echo "<tr class=\"$class comment\">";
  // note: css madness
  for($i = 0; $i != 3; ++$i) echo "<td></td>";

  // comment
  echo "<td class=\"comment\">";
  if($DATA["cmt"])
    echo htmlEntUTF8(sliceWords($DATA["cmt"], $styleGrantLineLen));
  echo "</td>";

  // parameters
  echo "<td class=\"fileinfo\" colspan=\"2\"><table>";

  // owner
  if(!$our)
    echo "<tr><th>" . T_("User:") . " </th><td>" . htmlEntUTF8($DATA["user"]) . "</td></tr>";
  if(hasPassHash($DATA))
    echo "<tr><th>" . T_("Password:") . " </th><td>" . str_repeat("&bull;", 5) . "</td>";

  // notify
  echo "<tr><th>" . T_("Notify:") . " </th><td>";
  $first = true;
  foreach(getEMailAddrs($DATA['notify_email']) as $email)
  {
    if($first) $first = false;
    else echo ", ";
    echo "<a href=\"mailto:" . urlencode($email) . "\">" .
      htmlEntUTF8($email) . "</a>";
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
  }

  echo "</table></td></tr>";
}

?>
  </table>

  <ul>
    <li class="buttons">
      <input type="button" value="<?php echo T_("Reload"); ?>" onclick="document.location.reload();"/>
      <input type="reset" value="<?php echo T_("Reset"); ?>"/>
      <input type="button" value="<?php echo T_("Select all"); ?>" onclick="selectAll();"/>
      <input type="submit" name="purge" value="<?php echo T_("Purge selected"); ?>"/>
    </li>
  </ul>
</form>

<?php
pageFooter();
?>
