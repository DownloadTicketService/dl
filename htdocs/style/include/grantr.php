<?php
includeTemplate('style/include/header.php', array('title' => T_("Upload result")));
?>

<label><?php echo T_("Your file has been uploaded successfully."); ?></label>
<p>
  <?php echo T_("A notification link has been sent to the grant owner. Thanks."); ?>
</p>

<?php
includeTemplate('style/include/footer.php');
?>
