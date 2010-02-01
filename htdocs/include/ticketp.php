<?php
$act = 'ticketp';
$title = T_("Password required");
includeTemplate('style/include/header.php', compact('title'));

echo "<p>";
printf(T_("The ticket %s is protected. Please enter the password to"
	. " unlock the content."), "<span class=\"ticketid\">$id</span>");
echo "</p>";
?>

<form action="<?php echo $ref; ?>" method="post">
  <ul>
    <li>
      <?php
        $error = ((@$_POST["submit"] === $act) && !isset($_SESSION['t'][$id]));
        $class = "description" . ($error? " required": "");
      ?>
      <label class="<?php echo $class; ?>"><?php echo T_("Password"); ?></label>
      <div>
	<input name="p" class="element text medium" type="password" maxlength="255"/>
      </div>
      <p class="guidelines"><small>
	  <?php
	    echo T_("Type the password required to unlock this ticket.");
          ?>
      </small></p>
    </li>

    <li class="buttons">
      <input type="hidden" name="submit" value="<?php echo $act; ?>"/>
      <input type="submit" value="<?php echo T_("Download"); ?>"/>
    </li>
  </ul>
</form>

<div id="footer">
  <?php echo $banner; ?>
</div>

<?php
includeTemplate('style/include/footer.php');
?>
