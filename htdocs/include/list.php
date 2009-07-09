<html>
  <head>
  </head>
  <body>
    <form action="<?php echo $masterPath . "?l"; ?>" method="post">
      <input type="hidden" name="l"/>
      <table cellpadding="5">
       <tr bgcolor="#CCCCCC">
	<th></th>
	<th>ID</th>
	<th>Name</th>
	<th>Size</th>
	<th>Date</th>
	<th>Expiration</th>
	<th>Notify</th>
	<th>Downloads/last</th>
       </tr>

<?php
function humanSize($size)
{
  if($size > 1073741824)
    return intval($size / 1073741824) . " gb";
  else if($size > 1048576)
    return intval($size / 1048576) . " mb";
  else if($size > 1024)
    return intval($size / 1024) . " kb";
  return $size;
}


function humanTime($seconds)
{
  if($seconds > 86400)
    return intval($seconds / 86400) . " days";
  else if($seconds > 3600)
    return intval($seconds / 3600) . " hours";
  else if($seconds > 60)
    return intval($seconds / 60) . " minutes";
  return $seconds . " seconds";
}


// purge requested tickets
foreach($_REQUEST as $key => $value)
{
  if(strncmp($key, "rm", 2)) continue;
  $DATA = dba_fetch($value, $tDb);
  if($DATA === false) continue;
  $DATA = unserialize($DATA);
  purgeDl($value, $DATA);
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

  // check
  echo "<tr><td><input type=\"checkbox\" name=\"rm$n\" value=\"$key\"/></td>";

  // id
  echo "<td><small>$key</small></td>";

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

  // notify
  echo "<td>" . htmlentities($DATA["email"]) . "</td>";

  // downloads
  echo "<td>";
  if($DATA["downloads"])
    echo $DATA["downloads"] . " - " . date("d/m/Y", $DATA["lastTime"]);
  echo "</td>";

  echo "</tr>";
}
?>

     </table>
     <input type="reset" value="Reload" onclick="document.location.reload();"/>
     <input type="reset" value="Reset"/>
     <input type="submit" value="Purge selected"/>
    </form>
    Total archive size: <?php echo humanSize($totalSize); ?>
    <hr/>
    <a href="<?php echo $masterPath; ?>">Submit new ticket</a>,
    <a href="<?php echo $masterPath; ?>?p">Logout</a>
  </body>
</html>
