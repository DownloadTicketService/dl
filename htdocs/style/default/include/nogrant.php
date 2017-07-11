<?php
includeTemplate("$style/include/header.php", array('title' =>
      ($id === false? T_("Invalid grant"): T_("Unknown grant"))));
?>

<label class="description">
  <?php
    if($id === false)
      printf(T_("Sorry, the grant is invalid."));
    else
      printf(T_("Sorry, the grant %s does not exist or is expired."),
	  "<span class=\"ticketid\">$id</span>");
  ?>
</label>

<?php
includeTemplate("$style/include/footer.php");
