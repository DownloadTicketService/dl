<?php
require_once("pages.php");
$act = "users";
$ref = "$adminPath?a=$act";
pageHeader();

if(isset($_REQUEST['create']) && !empty($_REQUEST['newUser'])
&& isset($_REQUEST['newRole']))
{
  // create user
  $user = $_REQUEST['newUser'];
  $pass = (!empty($_REQUEST['newPass'])? $_REQUEST['newPass']: false);
  $admin = ($_REQUEST['newRole'] == 1);
  if(userAdd($user, $pass, $admin))
    infoMessage(T_("Created"), htmlEntUTF8($user));
  else
    errorMessage(T_("Creation failed"),
	    sprintf(T_("user \"%s\" already exists"),
		htmlEntUTF8($user)));
}

if(isset($_REQUEST["delete"]) && !empty($_REQUEST["sel"]))
{
  $list = array();

  // delete users
  foreach($_REQUEST["sel"] as $name)
    if(userDel($name)) $list[] = htmlEntUTF8($name);

  if(count($list))
    infoMessage(T_("Deleted"), $list);
}

if(isset($_REQUEST['apply'])
&& !empty($_REQUEST['user']) && is_array($_REQUEST['user'])
&& !empty($_REQUEST['role']) && is_array($_REQUEST['role'])
&& !empty($_REQUEST['pass']) && is_array($_REQUEST['pass'])
&& count($_REQUEST['user']) == count($_REQUEST['role'])
&& count($_REQUEST['role']) == count($_REQUEST['pass']))
{
  $user = $_REQUEST['user'];
  $role = $_REQUEST['role'];
  $pass = $_REQUEST['pass'];
  $list = array();

  for($i = 0; $i != count($user); ++$i)
  {
    $o = userAdm($user[$i]);
    if(is_null($o)) continue;

    $role[$i] = ($role[$i] == 1);
    $sameRole = ($o == $role[$i]);
    $samePass = empty($pass[$i]);
    if($sameRole && $samePass) continue;

    if(userUpd($user[$i],
	    ($samePass? null: $pass[$i]),
	    ($sameRole? null: $role[$i])))
      $list[] = htmlEntUTF8($user[$i]);
  }

  if(count($list))
    errorMessage(T_("Updated"), $list);
}

function htmlRole($name, $selected)
{
  // role
  $ret = "<select class=\"element select\" name=\"$name\">";
  foreach(array("Administrator" => 1, "User" => 0) as $role => $admin)
  {
    $ret .= "<option value=\"$admin\"";
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
<form action="<?php echo $ref; ?>" method="post">
  <table id="users">
    <tr>
      <th><input class="element checkbox" type="checkbox" onclick="selectAll(this.checked);"/></th>
      <th><?php echo T_("User"); ?></th>
      <th><?php echo T_("Password"); ?></th>
      <th><?php echo T_("Role"); ?></th>
      <th><?php echo T_("Tickets"); ?></th>
      <th><?php echo T_("Grants"); ?></th>
      <th><?php echo T_("Total size"); ?></th>
    </tr>
<?php

foreach($db->query($sql) as $DATA)
{
  // selection
  echo "<tr><td><input class=\"element checkbox\" type=\"checkbox\" name=\"sel[]\" value=\""
    . htmlEntUTF8($DATA['name']) . "\"/></td>";

  // name/password
  echo "<td><label>" . htmlEntUTF8($DATA['name']) . "</label></td>";
  echo "<td><input type=\"hidden\" name=\"user[]\" value=\""
    . htmlEntUTF8($DATA['name']) . "\"/><input class=\"element text\""
    . " type=\"text\" name=\"pass[]\"></td>";

  // role
  echo "<td>" . htmlRole("role[]", $DATA['admin']) . "</td>";

  // sizes
  echo "<td>$DATA[tickets]</td><td>$DATA[grants]</td><td>"
    . humanSize($DATA['size']) . "</td></tr>";
}

?>
    <tr>
      <td></td>
      <td><input class="element text" type="text" name="newUser"></td>
      <td><input class="element text" type="text" name="newPass"></td>
      <td><?php echo htmlRole("newRole", 0); ?></td>
      <td colspan="3">
	<input class="element" type="submit" name="create" value="<?php echo T_("Create"); ?>"/>
      </td>
    </tr>
  </table>

  <ul>
    <li class="buttons">
      <input type="submit" name="reload" value="<?php echo T_("Reload"); ?>"/>
      <input type="reset" value="<?php echo T_("Reset"); ?>"/>
      <input type="submit" name="delete" value="<?php echo T_("Delete selected"); ?>"/>
      <input type="submit" name="apply" value="<?php echo T_("Apply changes"); ?>"/>
    </li>
  </ul>
</form>

<?php
pageFooter();
?>
