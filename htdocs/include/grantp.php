<?php
$act = 'grantp';
$title = T_("Password required");
includeTemplate('style/include/header.php', compact('title'));

echo "<p>";
printf(T_("The grant %s is protected. Please enter the password to"
	. " proceed to the upload."), "<span class=\"ticketid\">$id</span>");
echo "</p>";
?>

<form action="<?php echo $ref; ?>" method="post">
  <ul>
    <li>
      <?php
        $error = ((@$_POST["submit"] === $act) && !isset($_SESSION['g'][$id]));
        $class = "description" . ($error? " required": "");
      ?>
      <label class="<?php echo $class; ?>"><?php echo T_("Password"); ?></label>
      <div>
	<input name="p" class="element text medium" type="password" maxlength="255"/>
      </div>
      <p class="guidelines"><small>
	  <?php
	    echo T_("Type the password required for the upload.");
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
