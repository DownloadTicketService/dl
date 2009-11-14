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
  echo "<li id=\"error_message\"><table><tr><td class=\"label\">Purged:</td>";

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

foreach($db->query($sql) as $DATA)
{
  $totalSize += $DATA["size"];

  echo "<li class=\"fileinfo\">";

  // name
  echo "<span><input class=\"element checkbox\" type=\"checkbox\" name=\"sel[]\" value=\"" . $DATA['id'] . "\"/>";
  echo "<label class=\"choice\"><a href=\"" . ticketUrl($DATA) . "\">" .
    htmlentities($DATA["name"]) . "</a>";
  if($DATA["cmt"]) echo ' ' . htmlentities($DATA["cmt"]);
  echo "</label></span>";

  // parameters
  echo "<div class=\"fileinfo\"><table>";
  echo "<tr><th>Size: </th><td>" . humanSize($DATA["size"]) . "</td></tr>";
  echo "<tr><th>Date: </th><td> " . date("d/m/Y", $DATA["time"]) . "</td></tr>";
  if($DATA["user_id"] != $auth["id"])
    echo "<tr><th>User: </th><td>" . htmlentities($DATA["user"]) . "</td></tr>";
  if(isset($DATA['pass_md5']))
    echo "<tr><th>Password: </th><td>" . str_repeat("&bull;", 5) . "</td>";

  // expire
  echo "<tr><th>Expiry: </th><td>";
  if($DATA["expire_dln"] || $DATA["last_time"])
  {
    if($DATA["expire_last"])
      echo "Maybe in " . humanTime($DATA["expire_last"] - time());
    elseif($DATA["expire_dln"] && $DATA["downloads"])
      echo "Maybe in " . ($DATA["expire_dln"] - $DATA["downloads"]) . " downloads";
    elseif($DATA["expire"])
      echo "Maybe in " . humanTime($DATA["expire"] - time());
    elseif($DATA["expire_dln"])
      echo "After " . $DATA["expire_dln"] . " downloads";
    else
      echo "After next download, in " . humanTime($DATA["last_time"]);
  }
  elseif($DATA["expire"])
    echo "In " . humanTime($DATA["expire"] - time());
  else
    echo "<strong>never</strong>";
  echo "</td></tr>";

  // downloads
  if($DATA["downloads"])
  {
    echo "<tr><th>Downloads: </th><td>" . $DATA["downloads"] . "</td></tr>"
      . "<tr><th>Downloaded: </th><td>" . date("d/m/Y", $DATA["last_stamp"]) . "</td</tr>";
  }

  // notify
  if($DATA["notify_email"])
  {
    echo "<tr><th>Notify: </th><td>";
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
      <input type="reset" value="Reload" onclick="document.location.reload();"/>
      <input type="reset" value="Reset"/>
      <input type="submit" name="purge" value="Purge selected"/>
    </li>
  </ul>
</form>

<p>Total archive size: <?php echo humanSize($totalSize); ?></p>

<?php
pageFooter();
?>
