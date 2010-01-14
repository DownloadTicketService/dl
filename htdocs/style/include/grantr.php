<?php
includeTemplate('style/include/header.php', array('title' => _("Upload result")));
?>

<label><?php echo _("Your file has been uploaded successfully."); ?></label>
<p>
  <?php echo _("A notification link has been sent to the grant owner. Thanks."); ?>
</p>

<?php
includeTemplate('style/include/footer.php');
?>
