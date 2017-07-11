<?php
$act = 'login';
$ref = "$masterPath?";
includeTemplate("$style/include/header.php", array('title' => T_("Login")));

$error = ((@$_POST["submit"] === $act) && $auth === false);
$class = "description required" . ($error? " error": "");
?>

<form action="<?php echo $adminPath; ?>" method="post" onsubmit="validate(event);">
  <ul>
    <li>
      <label class="<?php echo $class; ?>"><?php echo T_("User"); ?></label>
      <div>
        <input tabindex="1" autofocus="autofocus" name="u" class="element text" type="text" required maxlength="<?php echo $maxUserLen; ?>"/>
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
        <input tabindex="2" name="p" class="element text" type="password" required maxlength="<?php echo $maxPassLen; ?>"/>
      </div>
      <p class="guidelines"><small>
          <?php
            echo T_("Type the password to access the filesharing service.");
          ?>
      </small></p>
    </li>

    <li class="buttons">
      <input type="hidden" name="submit" value="<?php echo $act; ?>"/>
      <input tabindex="3" id="submit" type="submit" value="<?php echo T_("Login"); ?>"/>
    </li>
  </ul>
</form>

<?php
includeTemplate("$style/include/footer.php");
