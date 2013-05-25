<?php
require_once("pages.php");
$act = "prefs";
$ref = pageLinkAct();
pageHeader();

// password update
if(!$authRealm && isset($_POST['changepw'])
&& !empty($_POST['oldpw']) && !empty($_POST['newpw']) && !empty($_POST['newpw2']))
{
  // create user
  if($_POST['newpw'] !== $_POST['newpw2'])
    errorMessage(T_("Password change"), T_("New passwords don't match! Password unchanged."));
  elseif(!userCheck($auth['name'], $_POST['oldpw']))
    errorMessage(T_("Password change"), T_("Old password doesn't match! Password unchanged."));
  else
  {
    userUpd($auth['name'], $_POST['newpw']);
    infoMessage(T_("Password change"), T_("Password successfully changed."));
  }
}

?>
<form action="<?php echo $ref; ?>" method="post" onsubmit="validate(event);">
  <ul>
<?php

// password update
if($authRealm)
  echo '<label class="description">' . T_("No configurable preferences") . '</label>';
else
{
?>
    <h3><?php echo T_("Password"); ?></h3>

    <li>
      <?php
        $error = ((@$_POST["submit"] === $act) && empty($_POST["oldpw"]));
        $class = "description required" . ($error? " error": "");
      ?>
      <label class="<?php echo $class; ?>"><?php echo T_("Old password"); ?></label>
      <div>
	<input name="oldpw" class="element text required" type="password" maxlength="255" value=""/>
      </div>
      <p class="guidelines"><small>
	  <?php
            echo T_("Please type your current password.");
          ?>
      </small></p>
    </li>

    <li>
      <?php
        $error = ((@$_POST["submit"] === $act) && empty($_POST["newpw"]));
        $class = "description required" . ($error? " error": "");
      ?>
      <label class="<?php echo $class; ?>"><?php echo T_("New password"); ?></label>
      <div>
	<input name="newpw" class="element text required" type="password" maxlength="255" value=""/>
      </div>
      <p class="guidelines"><small>
	  <?php
            echo T_("Please type the new password.");
          ?>
      </small></p>
    </li>

    <li>
      <?php
        $error = ((@$_POST["submit"] === $act) && empty($_POST["newpw2"]));
        $class = "description required" . ($error? " error": "");
      ?>
      <label class="<?php echo $class; ?>"><?php echo T_("New password (retype)"); ?></label>
      <div>
	<input name="newpw2" class="element text required" type="password" maxlength="255" value=""/>
      </div>
      <p class="guidelines"><small>
	  <?php
            echo T_("Please <em>retype</em> the new password again.");
          ?>
      </small></p>
    </li>

    <li class="buttons">
      <input type="hidden" name="submit" value="<?php echo $act; ?>"/>
      <input type="submit" name="changepw" value="<?php echo T_("Change password"); ?>"/>
    </li>
<?php
}
?>

  </ul>
</form>

<?php
pageFooter();
?>
