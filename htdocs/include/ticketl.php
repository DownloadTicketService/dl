<?php
require_once("include/pages.php");
$act = "tlist";
pageHeader();
?>

<form action="<?php echo currentPage(); ?>" method="post">
  <ul>

<?php
if(isset($_REQUEST["purge"]) && !empty($_REQUEST["sel"]))
{
  // purge immediately
  echo "<li id=\"error_message\"><table><tr><td class=\"label\">"
    . _("Purged:") . "</td>";

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
    echo "<td>" . htmlentities(humanTicketStr($DATA)) . "</td></tr>";
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
  echo "<label class=\"choice\"><a href=\"" . ticketUrl($DATA) . "\">" .
    htmlentities($DATA["name"]) . "</a>";
  if($DATA["cmt"]) echo ' ' . htmlentities($DATA["cmt"]);
  echo "</label></span>";

  // parameters
  echo "<div class=\"fileinfo\"><table>";
  echo "<tr><th>" . _("Size:") . " </th><td>" . humanSize($DATA["size"]) . "</td></tr>";
  echo "<tr><th>" . _("Date:") . " </th><td>" . date("d/m/Y", $DATA["time"]) . "</td></tr>";
  if(!$our)
    echo "<tr><th>" . _("User:") . " </th><td>" . htmlentities($DATA["user"]) . "</td></tr>";
  if(isset($DATA['pass_md5']))
    echo "<tr><th>" . _("Password:") . " </th><td>" . str_repeat("&bull;", 5) . "</td>";

  // expire
  echo "<tr><th>" . _("Expiry:") . " </th><td>";
  if($DATA["expire_dln"] || $DATA["last_time"])
  {
    if($DATA["expire_last"])
      printf(_("Maybe in %s"), humanTime($DATA["expire_last"] - time()));
    elseif($DATA["expire_dln"] && $DATA["downloads"])
      printf(_("Maybe in %d downloads"), ($DATA["expire_dln"] - $DATA["downloads"]));
    elseif($DATA["expire"])
      printf(_("Maybe in %s"), humanTime($DATA["expire"] - time()));
    elseif($DATA["expire_dln"])
      printf(_("After %d downloads"), $DATA["expire_dln"]);
    else
      printf(_("After next download, in %s"), humanTime($DATA["last_time"]));
  }
  elseif($DATA["expire"])
    printf(_("In %s"), humanTime($DATA["expire"] - time()));
  else
    echo "<strong>" . _("never") . "</strong>";
  echo "</td></tr>";

  // downloads
  if($DATA["downloads"])
  {
    echo "<tr><th>" . _("Downloads:") . " </th><td>" . $DATA["downloads"] . "</td></tr>"
      . "<tr><th>" . _("Downloaded:") . " </th><td>" . date("d/m/Y", $DATA["last_stamp"]) . "</td</tr>";
  }

  // notify
  if($DATA["notify_email"])
  {
    echo "<tr><th>" . _("Notify:") . " </th><td>";
    $first = true;
    foreach(getEMailAddrs($DATA['notify_email']) as $email)
    {
      if($first) $first = false;
      else echo ", ";
      echo "<a href=\"mailto:" . htmlentities($email) . "\">" .
	htmlentities($email) . "</a>";
    }
    echo "</td></tr>";
  }

  echo "</table></div></li>";
}

?>

    <li class="buttons">
      <input type="reset" value="<?php echo _("Reload"); ?>" onclick="document.location.reload();"/>
      <input type="reset" value="<?php echo _("Reset"); ?>"/>
      <input type="submit" name="purge" value="<?php echo _("Purge selected"); ?>"/>
    </li>
  </ul>
</form>

<p><?php printf(_("Total archive size: %s"), humanSize($totalSize)); ?></p>

<?php
pageFooter();
?>
