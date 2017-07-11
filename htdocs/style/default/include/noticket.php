<?php
includeTemplate("$style/include/header.php", array('title' =>
      ($id === false? T_("Invalid ticket"): T_("Unknown ticket"))));
?>

<label class="description">
  <?php
    if($id === false)
      printf(T_("Sorry, the ticket is invalid."));
    else
      printf(T_("Sorry, the ticket %s does not exist or is expired."),
	  "<span class=\"ticketid\">$id</span>");
  ?>
</label>

<?php
includeTemplate("$style/include/footer.php");
