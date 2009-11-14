<?php
$title = 'Password required';
includeTemplate('style/include/header.php', compact('title'));
?>

<p>
  The ticket <span class="ticketid"><?php echo $id; ?></span> is
  protected. Please enter the password to unlock the content.
</p>

<form action="<?php echo "$masterPath?t=$id"; ?>" method="post">
  <ul>
    <li>
      <label class="description">Password</label>
      <div>
	<input name="p" class="element text medium" type="password" maxlength="255"/>
      </div><p class="guidelines"><small>Type in the password required to unlock this ticket.</small></p>
    </li>

    <li class="buttons">
      <input type="submit" name="submit" value="Download"/>
    </li>
  </ul>
</form>

<div id="footer">
  <?php echo $banner; ?>
</div>

<?php
includeTemplate('style/include/footer.php');
?>
