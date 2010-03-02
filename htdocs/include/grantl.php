<?php
require_once("include/pages.php");
$act = "glist";
$ref = "$adminPath?a=$act";
pageHeader();

echo "<form action=\"$ref\" method=\"post\"><ul>";

if(isset($_REQUEST["purge"]) && !empty($_REQUEST["sel"]))
{
  // purge immediately
  echo "<li id=\"error_message\"><table><tr><td class=\"label\">"
    . T_("Purged:") . "</td>";

  $first = true;
  foreach($_REQUEST["sel"] as $id)
  {
    $sql = "SELECT * FROM grant WHERE id = " . $db->quote($id);
    $DATA = $db->query($sql)->fetch();
    if($DATA === false) continue;

    // check for permissions
    if(!$auth["admin"] && $DATA["user_id"] != $auth["id"])
      continue;

    // actually purge the grant
    if($first) $first = false;
    else echo "<tr><td></td>";
    echo "<td>" . htmlEntUTF8(grantStr($DATA)) . "</td></tr>";
    grantPurge($DATA, false);
  }

  echo "</table></li>";
}

// list active grants
$sql = "SELECT g.*, u.name AS user FROM grant g"
  . " LEFT JOIN user u ON u.id = g.user_id";
if(!$auth["admin"]) $sql .= " WHERE user_id = " . $auth["id"];
$sql .= " ORDER BY (user_id <> " . $auth["id"] . "), user_id, time";

foreach($db->query($sql) as $DATA)
{
  if(isGrantExpired($DATA)) continue;

  $our = ($DATA["user_id"] == $auth["id"]);
  $class = ($our? "fileinfo": "fileinfo alien");
  echo "<li class=\"$class\">";

  // name
  echo "<span><input class=\"element checkbox\" type=\"checkbox\" name=\"sel[]\" value=\"" . $DATA['id'] . "\"/>";
  echo "<label class=\"choice\"><a href=\"" . grantUrl($DATA) . "\">" . htmlEntUTF8($DATA["id"]) . "</a>";
  if($DATA["cmt"]) echo ' ' . htmlEntUTF8($DATA["cmt"]);
  echo "</label></span>";

  // parameters
  echo "<div class=\"fileinfo\"><table>";
  echo "<tr><th>" . T_("Date:") . " </th><td> " . date("d/m/Y", $DATA["time"]) . "</td></tr>";
  if(!$our)
    echo "<tr><th>" . T_("User:") . " </th><td>" . htmlEntUTF8($DATA["user"]) . "</td></tr>";
  if(isset($DATA['pass_md5']))
    echo "<tr><th>" . T_("Password:") . " </th><td>" . str_repeat("&bull;", 5) . "</td>";

  // expire
  echo "<tr><th>" . T_("Expiry:") . " </th><td>";
  if($DATA["grant_expire"])
    echo "In " . humanTime($DATA["grant_expire"] - time());
  else
    echo "<strong>" . T_("never") . "</strong>";
  echo "</td></tr>";

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

  echo "</table></div></li>";
}

?>

    <li class="buttons">
      <input type="reset" value="<?php echo T_("Reload"); ?>" onclick="document.location.reload();"/>
      <input type="reset" value="<?php echo T_("Reset"); ?>"/>
      <input type="submit" name="purge" value="<?php echo T_("Purge selected"); ?>"/>
    </li>
  </ul>
</form>

<?php
pageFooter();
?>
