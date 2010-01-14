<?php
includeTemplate('style/include/header.php', compact('title'));
?>

<label class="description">
  <?php
    printf(_("Sorry, the grant %s does not exist or is expired."),
	"<span class=\"ticketid\">$id</span>");
  ?>
</label>

<?php
includeTemplate('style/include/footer.php');
?>
