<?php
require_once("include/pages.php");
$act = "tlist";
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
    $sql = "SELECT * FROM ticket WHERE id = " . $db->quote($id);
    $DATA = $db->query($sql)->fetch();
    if($DATA === false) continue;

    // check for permissions
    if(!$auth["admin"] && $DATA["user_id"] != $auth["id"])
      continue;

    // actually purge the ticket
    if($first) $first = false;
    else echo "<tr><td></td>";
    echo "<td>" . htmlEntUTF8(humanTicketStr($DATA)) . "</td></tr>";
    echo "</td></tr>";
    ticketPurge($DATA, false);
  }

  echo "</table></li>";
}

// list active tickets
$totalSize = 0;

$sql = "SELECT t.*, u.name AS user FROM ticket t"
  . " LEFT JOIN user u ON u.id = t.user_id";
if(!$auth["admin"]) $sql .= " WHERE user_id = " . $auth["id"];
$sql .= " ORDER BY (user_id <> " . $auth["id"] . "), user_id, time";

foreach($db->query($sql) as $DATA)
{
  $totalSize += $DATA["size"];
  $our = ($DATA["user_id"] == $auth["id"]);
  $class = ($our? "fileinfo": "fileinfo alien");
  echo "<li class=\"$class\">";

  // name
  echo "<span><input class=\"element checkbox\" type=\"checkbox\" name=\"sel[]\" value=\"" . $DATA['id'] . "\"/>";
  echo "<label class=\"choice\"><a href=\"" . ticketUrl($DATA) . "\">" . htmlEntUTF8($DATA["name"]) . "</a>";
  if($DATA["cmt"]) echo ' ' . htmlEntUTF8($DATA["cmt"]);
  echo "</label></span>";

  // parameters
  echo "<div class=\"fileinfo\"><table>";
  echo "<tr><th>" . T_("Size:") . " </th><td>" . humanSize($DATA["size"]) . "</td></tr>";
  echo "<tr><th>" . T_("Date:") . " </th><td>" . date("d/m/Y", $DATA["time"]) . "</td></tr>";
  if(!$our)
    echo "<tr><th>" . T_("User:") . " </th><td>" . htmlEntUTF8($DATA["user"]) . "</td></tr>";
  if(isset($DATA['pass_md5']))
    echo "<tr><th>" . T_("Password:") . " </th><td>" . str_repeat("&bull;", 5) . "</td>";

  // expire
  echo "<tr><th>" . T_("Expiry:") . " </th><td>";
  if($DATA["expire_dln"] || $DATA["last_time"])
  {
    if($DATA["expire_last"])
      printf(T_("Maybe in %s"), humanTime($DATA["expire_last"] - time()));
    elseif($DATA["expire_dln"] && $DATA["downloads"])
      printf(T_("Maybe in %d downloads"), ($DATA["expire_dln"] - $DATA["downloads"]));
    elseif($DATA["expire"])
      printf(T_("Maybe in %s"), humanTime($DATA["expire"] - time()));
    elseif($DATA["expire_dln"])
      printf(T_("After %d downloads"), $DATA["expire_dln"]);
    else
      printf(T_("After next download, in %s"), humanTime($DATA["last_time"]));
  }
  elseif($DATA["expire"])
    printf(T_("In %s"), humanTime($DATA["expire"] - time()));
  else
    echo "<strong>" . T_("never") . "</strong>";
  echo "</td></tr>";

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

<p><?php printf(T_("Total archive size: %s"), humanSize($totalSize)); ?></p>

<?php
pageFooter();
?>
