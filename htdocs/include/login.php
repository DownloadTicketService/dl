<?php
$act = 'login';
$ref = "$masterPath?";
includeTemplate('style/include/header.php', array('title' => T_("Login")));

$error = ((@$_REQUEST["submit"] === $act) && $auth === false);
$class = "description required" . ($error? " error": "");
?>

<form action="<?php echo $adminPath; ?>" method="post" onsubmit="validate(event);">
  <ul>
    <li>
      <label class="<?php echo $class; ?>"><?php echo T_("User"); ?></label>
      <div>
	<input name="u" class="element text medium required" type="text" maxlength="255"/>
      </div>
      <p class="guidelines"><small>
	  <?php
            echo T_("Type the user name to access the filesharing service.");
          ?>
      </small></p>
    </li>

    <li>
      <label class="<?php echo $class; ?>"><?php echo T_("Password"); ?></label>
      <div>
	<input name="p" class="element text medium required" type="password" maxlength="255"/>
      </div>
      <p class="guidelines"><small>
          <?php
            echo T_("Type the password to access the filesharing service.");
          ?>
      </small></p>
    </li>

    <li class="buttons">
      <input type="hidden" name="submit" value="<?php echo $act; ?>"/>
      <input id="submit" type="submit"/>
    </li>
  </ul>
</form>

<div id="footer">
  <?php echo $banner; ?>
</div>

<?php
includeTemplate('style/include/footer.php');
?>
