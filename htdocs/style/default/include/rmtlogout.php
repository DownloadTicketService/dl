<?php
includeTemplate("$style/include/header.php", array('title' => T_("Logged-out")));
?>

<label class="description">
  <?php echo T_("<em>Close the browser</em> to complete the logout."); ?>
</label>

<?php
includeTemplate("$style/include/footer.php");
?>
