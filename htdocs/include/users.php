<?php
require_once("pages.php");
$act = "users";
$ref = "$adminPath?a=$act";
pageHeader();

echo "<form action=\"$ref\" method=\"post\">";

if(isset($_REQUEST["purge"]) && !empty($_REQUEST["sel"]))
{
  // purge immediately
  echo "<ul><li id=\"error_message\"><table><tr><td class=\"label\">"
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

  echo "</table></ul>";
}

function htmlRole($name, $selected)
{
  // role
  $ret = "<select class=\"element large select\" name=\"$name\">";
  foreach(array("Administrator" => 1, "User" => 0) as $role => $admin)
  {
    $ret .= "<option value=\"1\"";
    if($selected == $admin) $ret .= " selected=\"selected\"";
    $ret .= ">" . T_($role) . "</option>";
  }
  $ret .= "</select>";
  return $ret;
}

// list users
$sql = <<<EOF
  SELECT u.name, admin, t.count as tickets, g.count as grants, t.size
  FROM user u
  LEFT JOIN role r ON r.id = u.role_id
  LEFT JOIN (
      SELECT u.id, count(t.id) as count, sum(t.size) as size
      FROM user u
      LEFT JOIN ticket t ON t.user_id = u.id
      GROUP BY u.id
    ) t ON t.id = u.id
  LEFT JOIN (
      SELECT u.id, count(g.id) as count
      FROM user u
      LEFT JOIN grant g ON g.user_id = u.id
      GROUP BY u.id
    ) g ON g.id = u.id
  ORDER BY u.name
EOF;

?>
  <table id="users">
    <tr>
      <th><input class="element checkbox" type="checkbox" onclick="selectAll(this.checked);"/></th>
      <th><?php echo T_("User"); ?></th>
      <th><?php echo T_("Role"); ?></th>
      <th><?php echo T_("Tickets"); ?></th>
      <th><?php echo T_("Grants"); ?></th>
      <th><?php echo T_("Total size"); ?></th>
    </tr>
<?php

foreach($db->query($sql) as $DATA)
{
  // selection
  echo "<tr><td><input class=\"element checkbox\" type=\"checkbox\" name=\"sel[]\"/></td>";

  // name
  echo "<td><label>" . htmlEntUTF8($DATA['name']) . "</label></td>";

  // role
  echo "<td>" . htmlRole("role[]", $DATA['admin']) . "</td>";

  // sizes
  echo "<td>$DATA[tickets]</td><td>$DATA[grants]</td><td>"
    . humanSize($DATA['size']) . "</td></tr>";
}

?>
    <tr>
      <td></td>
      <td><input class="element large text" type="text"></td>
      <td><?php echo htmlRole("newRole", 0); ?></td>
      <td colspan="3">
	<input class="element" type="submit" name="create" value="<?php echo T_("Create"); ?>"/>
      </td>
    </tr>
  </table>

  <ul>
    <li class="buttons">
      <input type="button" value="<?php echo T_("Reload"); ?>" onclick="document.location.reload();"/>
      <input type="reset" value="<?php echo T_("Reset"); ?>"/>
      <input type="submit" name="delete" value="<?php echo T_("Delete selected"); ?>"/>
      <input type="submit" name="delete" value="<?php echo T_("Update"); ?>"/>
    </li>
  </ul>
</form>

<?php
pageFooter();
?>
