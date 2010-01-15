<?php
$act = 'login';
$ref = "$masterPath?";
$title = _("Login");
includeTemplate('style/include/header.php', compact('title'));

$error = ((@$_REQUEST["submit"] === $act) && $auth === false);
$class = "description" . ($error? " required": "");
?>

<form action="<?php echo $adminPath; ?>" method="post">
  <ul>
    <li>
      <label class="<?php echo $class; ?>"><?php echo _("User"); ?></label>
      <div>
	<input name="u" class="element text medium" type="text" maxlength="255"/>
      </div>
      <p class="guidelines"><small>
	  <?php
            echo _("Type the user name to access the filesharing service.");
          ?>
      </small></p>
    </li>

    <li>
      <label class="<?php echo $class; ?>"><?php echo _("Password"); ?></label>
      <div>
	<input name="p" class="element text medium" type="password" maxlength="255"/>
      </div>
      <p class="guidelines"><small>
          <?php
            echo _("Type the password to access the filesharing service.");
          ?>
      </small></p>
    </li>

    <li class="buttons">
      <input type="hidden" name="submit" value="<?php echo $act; ?>"/>
      <input type="submit"/>
    </li>
  </ul>
</form>

<div id="footer">
  <?php echo $banner; ?>
</div>

<?php
includeTemplate('style/include/footer.php');
?>
