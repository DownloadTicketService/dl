<html>
  <head>
    <link href="style.css" rel="stylesheet" type="text/css"/>
  </head>
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
  echo "<div class=\"action\"><table><tr><td class=\"label\">Purged:</td>";
  $first = true;
  foreach($ids as $key => $value)
  {
    if($first) $first = false;
    else echo "<tr><td></td>";
    echo "<td class=\"id\">" . $value["id"] . ":</td><td>" .
      htmlentities($value["name"]) . "</td></tr>";
    purgeDl($value["id"], $value);
  }

  echo "</table></div><br/>";
}

?>
  <body>
    <form action="<?php echo $masterPath . "?l"; ?>" method="post">
      <input type="hidden" name="l"/>
      <table class="list" cellpadding="5">
       <tr>
	<th></th>
	<th>Name</th>
	<th>Size</th>
	<th>Date</th>
	<th>Expiration</th>
	<th>Downloads - last</th>
	<th>Notify</th>
	<th>Comment</th>
       </tr>
<?php

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

  // check
  $color = ($n % 2? "": " class=\"odd\"");
  echo "<tr$color><td>" .
    "<input type=\"checkbox\" name=\"sel$n\" value=\"$key\"/>" .
    "</td>";

  // name
  echo "<td><a href=\"$masterPath?t=$key\">" .
    htmlentities($DATA["name"]) . "</a></td>";

  // size
  echo "<td>" . humanSize($DATA["size"]) . "</td>";

  // date
  echo "<td>" . date("d/m/Y", $DATA["time"]) . "</td>";

  // expire
  echo "<td>";
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
  echo "</td>";

  // downloads
  echo "<td>";
  if($DATA["downloads"])
    echo $DATA["downloads"] . " - " . date("d/m/Y", $DATA["lastTime"]);
  echo "</td>";

  // notify
  echo "<td>";
  $first = true;
  foreach(explode(",", $DATA["email"]) as $email)
  {
    if($first) $first = false;
    else echo ", ";
    $email = trim($email);
    echo "<a href=\"mailto:" . htmlentities($email) . "\">" .
      htmlentities($email) . "</a>";
  }

  // comments
  echo "<td>";
  if($DATA["cmt"]) echo htmlentities($DATA["cmt"]);
  echo "</td>";

  echo "</tr>";
}

?>
     </table>
     <input type="reset" value="Reload" onclick="document.location.reload();"/>
     <input type="reset" value="Reset"/>
     <input type="submit" value="Purge selected"/>
    </form>
    <p>Total archive size: <?php echo humanSize($totalSize); ?></p>
    <div class="nav">
      <a href="<?php echo $masterPath; ?>">Submit new ticket</a>,
      <a href="<?php echo $masterPath; ?>?p">Logout</a>
    </div>
  </body>
</html>
