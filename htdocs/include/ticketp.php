<?php
$act = 'ticketp';
includeTemplate('style/include/header.php', array('title' => T_("Password required")));

echo "<p>";
printf(T_("The ticket %s is protected. Please enter the password to"
	. " unlock the content."), "<span class=\"ticketid\">$id</span>");
echo "</p>";
?>

<form action="<?php echo $ref; ?>" method="post" onsubmit="validate(event);">
  <ul>
    <li>
      <?php
        $error = ((@$_POST["submit"] === $act) && !isset($_SESSION['t'][$id]));
        $class = "description required" . ($error? " error": "");
      ?>
      <label class="<?php echo $class; ?>"><?php echo T_("Password"); ?></label>
      <div>
	<input name="p" class="element text" type="password" required maxlength="<?php echo $maxPassLen; ?>"/>
      </div>
      <p class="guidelines"><small>
	  <?php
	    echo T_("Type the password required to unlock this ticket.");
          ?>
      </small></p>
    </li>

    <li class="buttons">
      <input type="hidden" name="submit" value="<?php echo $act; ?>"/>
      <input id="submit" type="submit" value="<?php echo T_("Download"); ?>"/>
    </li>
  </ul>
</form>

<div id="footer">
  <?php echo $banner; ?>
</div>

<?php
includeTemplate('style/include/footer.php');
?>
