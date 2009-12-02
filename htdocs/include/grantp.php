<?php
$act = 'grantp';
$title = 'Password required';
includeTemplate('style/include/header.php', compact('title'));
?>

<p>
  The grant <span class="ticketid"><?php echo $id; ?></span> is
  protected. Please enter the password to proceed to the upload.
</p>

<form action="<?php echo "$masterPath?g=$id"; ?>" method="post">
  <ul>
    <li>
      <?php
        $error = ((@$_POST["submit"] === $act) && !isset($_SESSION['g'][$id]));
        $class = "description" . ($error? " required": "");
      ?>
      <label class="<?php echo $class; ?>">Password</label>
      <div>
	<input name="p" class="element text medium" type="password" maxlength="255"/>
      </div><p class="guidelines"><small>Type in the password required for the upload.</small></p>
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
