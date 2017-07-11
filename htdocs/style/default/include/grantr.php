<?php
includeTemplate("$style/include/header.php", array('title' => T_("Upload result")));
?>

<label><?php echo T_("Your file has been uploaded successfully."); ?></label>
<p>
  <?php
    echo T_("A notification link has been sent to the grant owner.");
  ?>
</p>
<?php
  if(!empty($ref))
  {
    echo "<p>";
    printf(T_("The grant is still valid. You can <a href=\"%s\">upload more files</a>."), $ref);
    echo "</p>";
  }
?>

<?php
includeTemplate("$style/include/footer.php");
