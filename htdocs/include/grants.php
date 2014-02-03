<?php
$act = 'grants';
includeTemplate('style/include/header.php', array('title' => T_("Upload grant")));

require_once("progress.php");
$up = newUploadProgress();
uploadProgressHdr($up);

if(!empty($_FILES["file"]) && !empty($_FILES["file"]["name"]))
  errorMessage(T_("Upload failed"), uploadErrorStr($_FILES["file"]));
?>

<form enctype="multipart/form-data" method="post"
      onsubmit="validate(event);" action="<?php echo $ref; ?>" >
  <ul>
    <li>
      <?php
	$error = ((@$_POST["submit"] === $act) && empty($_FILES["file"]["name"]));
	$class = "description required" . ($error? " error": "");
      ?>
      <label class="<?php echo $class; ?>"><?php echo T_("Upload a file"); ?></label>
      <div>
	<input type="hidden" name="max_file_size" value="<?php echo $iMaxSize; ?>"/>
	<?php uploadProgressField($up); ?>
	<input name="file" class="element file" required type="file"/>
      </div>
      <p class="guidelines"><small>
	  <?php
	    printf(T_("Choose which file to upload. You can upload up to %s."),
		humanSize($iMaxSize));
	  ?>
      </small></p>
    </li>

    <li class="buttons">
      <input type="hidden" name="submit" value="<?php echo $act; ?>"/>
      <input id="submit" type="submit" value="<?php echo T_("Upload"); ?>"/>
      <?php uploadProgressHtml($up); ?>
    </li>
  </ul>
</form>

<div id="footer">
  <?php echo $banner; ?>
</div>

<?php
includeTemplate('style/include/footer.php');
?>
