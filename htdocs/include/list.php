<?php
$title = 'Active Tickets';
includeTemplate('style/include/header.php', compact('title'));
?>

<form action="<?php echo $adminPath; ?>?l" method="post">
  <input type="hidden" name="l"/>
  <ul>

<?php
if(isset($_REQUEST["purge"]) && !empty($_REQUEST["sel"]))
{
  // purge immediately
  echo "<li id=\"error_message\"><table><tr><td class=\"label\">Purged:</td>";

  $first = true;
  foreach($_REQUEST["sel"] as $id)
  {
    $sql = "SELECT * FROM tickets WHERE id = " . $db->quote($id);
    $DATA = $db->query($sql)->fetch();
    if($DATA === false) continue;

    if($first) $first = false;
    else echo "<tr><td></td>";
    echo "<td>" . htmlentities($DATA["name"]);
    if($DATA["cmt"])
      echo ": " . htmlentities($DATA["cmt"]);
    echo "</td></tr>";
    purgeDl($DATA, false);
  }

  echo "</table></li>";
}

// list active tickets
$totalSize = 0;

$sql = "SELECT t.*, u.name AS user FROM tickets t"
  . " LEFT JOIN users u ON u.id = t.owner";
if(!$auth["admin"]) $sql .= " WHERE owner = " . $auth["id"];

foreach($db->query($sql) as $DATA)
{
  $totalSize += $DATA["size"];

  echo "<li class=\"fileinfo\">";

  // name
  echo "<span><input class=\"element checkbox\" type=\"checkbox\" name=\"sel[]\" value=\"" . $DATA['id'] . "\"/>";
  echo "<label class=\"choice\"><a href=\"" . ticketUrl($DATA) . "\">" .
    htmlentities($DATA["name"]) . "</a>";
  if($DATA["cmt"])
    echo ": " . htmlentities($DATA["cmt"]);
  echo "</label></span>";

  // parameters
  echo "<div class=\"fileinfo\"><table>";
  echo "<tr><th>Size: </th><td>" . humanSize($DATA["size"]) . "</td></tr>";
  echo "<tr><th>Date: </th><td> " . date("d/m/Y", $DATA["time"]) . "</td></tr>";
  if($DATA["owner"] != $auth["id"])
    echo "<tr><th>User: </th><td>" . htmlentities($DATA["user"]) . "</td></tr>";

  // expire
  echo "<tr><th>Expiry: </th><td>";
  if($DATA["expire_dln"] || $DATA["expire_last"])
  {
    if($DATA["expire_last"] && $DATA["last_time"])
      echo "Maybe in " . humanTime($DATA["last_time"] + $DATA["expire_last"]- time());
    else if($DATA["expire_dln"] && $DATA["downloads"])
      echo "Maybe in " . ($DATA["expire_dln"] - $DATA["downloads"]) . " downloads";
    else if($DATA["expire"])
      echo "Maybe in " . humanTime($DATA["expire"] - time());
    else if($DATA["expire_dln"])
      echo "After " . $DATA["expire_dln"] . " downloads";
    else
      echo "After next download, in " . humanTime($DATA["expire_last"]);
  }
  else if($DATA["expire"])
    echo "In " . humanTime($DATA["expire"] - time());
  else
    echo "<strong>never</strong>";
  echo "</td></tr>";

  // downloads
  if($DATA["downloads"])
  {
    echo "<tr><th>Downloads: </th><td>" . $DATA["downloads"] . "</td></tr>"
      . "<tr><th>Downloaded: </th><td>" . date("d/m/Y", $DATA["last_time"]) . "</td</tr>";
  }

  // notify
  if($DATA["email"])
  {
    echo "<tr><th>Notify: </th><td>";
    $first = true;
    foreach(explode(",", $DATA["email"]) as $email)
    {
      if($first) $first = false;
      else echo ", ";
      $email = trim($email);
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

<div id="footer">
  <a href="<?php echo $adminPath; ?>">Submit new ticket</a>,
  <a href="<?php echo $adminPath; ?>?u">Logout</a>
</div>

<?php
includeTemplate('style/include/footer.php');
?>
