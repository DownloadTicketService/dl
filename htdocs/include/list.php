<?php
$title = 'Active Tickets';
includeTemplate('style/include/header.php', compact('title'));
?>

<div class="form_description">
  <h2><?php echo $title; ?></h2>
  <p>dl: minimalist download ticket service</p>
</div>

<form action="<?php echo $masterPath; ?>?l" method="post">
  <input type="hidden" name="l"/>
  <ul>

<?php
// extract requested tickets
$ids = array();
foreach($_REQUEST as $key => $value)
{
  if(strncmp($key, "sel", 2)) continue;
  $DATA = dba_fetch($value, $tDb);
  if($DATA === false) continue;
  $DATA = unserialize($DATA);
  $DATA["id"] = $value;
  $ids[$key] = $DATA;
}

if(count($ids))
{
  // purge immediately
  echo "<li id=\"error_message\"><table><tr><td class=\"label\">Purged:</td>";
  $first = true;
  foreach($ids as $key => $value)
  {
    if($first) $first = false;
    else echo "<tr><td></td>";
    echo "<td>" . htmlentities($value["name"]);
    if($DATA["cmt"])
      echo ": " . htmlentities($DATA["cmt"]);
    echo "</td></tr>";
    purgeDl($value["id"], $value);
  }
  
  echo "</table></li>";
}

// list active tickets
$totalSize = 0;
$n = 0;
for($key = dba_firstkey($tDb); $key; $key = dba_nextkey($tDb))
{
  $DATA = dba_fetch($key, $tDb);
  if($DATA === false) continue;
  $DATA = unserialize($DATA);
  $totalSize += $DATA["size"];
  ++$n;

  echo "<li class=\"fileinfo\">";

  // name
  echo "<span><input class=\"element checkbox\" type=\"checkbox\" name=\"sel$n\" value=\"$key\"/>";
  echo "<label class=\"choice\"><a href=\"$masterPath?t=$key\">" .
    htmlentities($DATA["name"]) . "</a>";
  if($DATA["cmt"])
    echo ": " . htmlentities($DATA["cmt"]);
  echo "</label></span>";

  // parameters
  echo "<div class=\"fileinfo\"><table>";
  echo "<tr><th>Size: </th><td>" . humanSize($DATA["size"]) . "</td></tr>";
  echo "<tr><th>Date: </th><td> " . date("d/m/Y", $DATA["time"]) . "</td></tr>";

  // expire
  echo "<tr><th>Expiry: </th><td>";
  if($DATA["expireDln"] || $DATA["expireLast"])
  {
    if($DATA["expireLast"] && $DATA["lastTime"])
      echo "Maybe in " . humanTime($DATA["lastTime"] + $DATA["expireLast"]- time());
    else if($DATA["expireDln"] && $DATA["downloads"])
      echo "Maybe in " . ($DATA["expireDln"] - $DATA["downloads"]) . " downloads";
    else if($DATA["expire"])
      echo "Maybe in " . humanTime($DATA["expire"] - time());
    else if($DATA["expireDln"])
      echo "After " . $DATA["expireDln"] . " downloads";
    else
      echo "After next download, in " . humanTime($DATA["expireLast"]);
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
      . "<tr><th>Downloaded: </th><td>" . date("d/m/Y", $DATA["lastTime"]) . "</td</tr>";
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
      <input type="submit" value="Purge selected"/>
    </li>
  </ul>
</form>

<p>Total archive size: <?php echo humanSize($totalSize); ?></p>

<div id="footer">
  <a href="<?php echo $masterPath; ?>">Submit new ticket</a>,
  <a href="<?php echo $masterPath; ?>?p">Logout</a>
</div>

<?php
includeTemplate('style/include/footer.php');
?>
