<?php
$title = 'Login';
includeTemplate('style/include/header.php', compact('title'));
?>

<form action="<?php echo $adminPath; ?>" method="post">
  <ul>
    <li>
      <label class="description">User</label>
      <div>
	<input name="u" class="element text medium" type="text" maxlength="255"/>
      </div><p class="guidelines"><small>Type in the user name to access the filesharing service.</small></p>
    </li>

    <li>
      <label class="description">Password</label>
      <div>
	<input name="p" class="element text medium" type="password" maxlength="255"/>
      </div><p class="guidelines"><small>Type in the password to access the filesharing service.</small></p>
    </li>

    <li class="buttons">
      <input type="submit" name="submit" value="Login"/>
    </li>
  </ul>
</form>

<div id="footer">
  <?php echo $banner; ?>
</div>

<?php
includeTemplate('style/include/footer.php');
?>
