<?php
$act = 'grants';
includeTemplate("$style/include/header.php", array('title' => T_("Upload grant")));

if($UPLOAD_ERRNO !== UPLOAD_ERR_OK)
  errorMessage(T_("Upload failed"), uploadErrorStr());
?>

<form enctype="multipart/form-data" method="post"
      action="<?php echo $ref; ?>"
      class="validate autoprogress">
  <ul>
    <li>
      <?php
	$error = ((@$_POST["submit"] === $act) && empty($_FILES["file"]["name"]));
	$class = "description required" . ($error? " error": "");
      ?>
      <label class="<?php echo $class; ?>"><?php echo T_("Upload file/s"); ?></label>
      <div class="file">
	<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $iMaxSize; ?>"/>
	<input name="file[]" class="element file" type="file" multiple required/>
      </div>
      <div class="file">
	<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $iMaxSize; ?>"/>
	<input name="file[]" class="element file" type="file" multiple/>
      </div>
      <div class="file">
	<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $iMaxSize; ?>"/>
	<input name="file[]" class="element file" type="file" multiple/>
      </div>
      <p class="guidelines"><small>
	<?php
	  printf(T_("Choose which file/s to upload. You can upload for a total of %s."),
		 humanSize($iMaxSize));
	?>
      </small></p>
    </li>

    <li class="buttons">
      <input type="hidden" name="submit" value="<?php echo $act; ?>"/>
      <input id="submit" type="submit" value="<?php echo T_("Upload"); ?>"/>
      <div id="uploadprogress"></div>
    </li>
  </ul>
</form>

<div id="footer">
  <?php echo $banner; ?>
</div>

<?php
includeTemplate("$style/include/footer.php");
?>
