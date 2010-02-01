<?php
$act = 'grants';
$title = T_("Upload grant");
includeTemplate('style/include/header.php', compact('title'));

require_once("progress.php");
$up = newUploadProgress();
uploadProgressHdr($up);
?>

<form enctype="multipart/form-data" method="post"
      onsubmit="document.getElementById('submit').disabled = true;"
      action="<?php echo $ref; ?>" >
  <ul>

<?php
  if(!empty($_FILES["file"]) && !empty($_FILES["file"]["name"]))
  {
    echo "<li id=\"error_message\"><label>"
      . T_("Upload failed:") . "</label> ";

    switch($_FILES["file"]["error"])
    {
    case UPLOAD_ERR_INI_SIZE:
    case UPLOAD_ERR_FORM_SIZE:
      echo T_("file too big");
      break;

    case UPLOAD_ERR_PARTIAL:
    case UPLOAD_ERR_NO_FILE:
      echo T_("upload interrupted");
      break;

    default:
      echo T_("internal error");
    }
    echo "</li>";
  }
?>

    <li>
      <?php
        $error = ((@$_POST["submit"] === $act) && empty($_FILES["file"]["name"]));
        $class = "description" . ($error? " required": "");
      ?>
      <label class="<?php echo $class; ?>"><?php echo T_("Upload a file"); ?></label>
      <div>
        <input type="hidden" name="max_file_size" value="<?php echo $iMaxSize; ?>"/>
        <?php uploadProgressField($up); ?>
	<input name="file" class="element file" type="file"/>
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
