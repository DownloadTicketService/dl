<?php
$title = 'Upload Request';
includeTemplate('style/include/header.php', compact('title'));
?>

<script type="text/javascript">
  window.addEventListener("load", loadDefaults, false)
</script>

<form enctype="multipart/form-data" method="post"
      onsubmit="document.getElementById('submit').disabled = true;"
      action="<?php echo $adminPath; ?>" >
  <ul>

<?php
  if(!empty($_FILES["file"]) && !empty($_FILES["file"]["name"]))
  {
    echo "<li id=\"error_message\"><label>Upload failed:</label> ";
    switch($_FILES["file"]["error"])
    {
    case UPLOAD_ERR_INI_SIZE:
    case UPLOAD_ERR_FORM_SIZE:
      echo "file too big";
      break;

    case UPLOAD_ERR_PARTIAL:
    case UPLOAD_ERR_NO_FILE:
      echo "upload interrupted";
      break;

    default:
      echo "internal error";
    }
    echo "</li>";
  }
?>

    <li>
      <label class="description">Upload a File</label>
      <div>
        <input type="hidden" name="max_file_size" value="<?php echo $iMaxSize; ?>"/>
	<input name="file" class="element file" type="file"/>
      </div>
      <p class="guidelines"><small>
	  Choose which file to upload. You can upload up to
          <?php echo humanSize($iMaxSize); ?>.
      </small></p>
    </li>

    <li>
      <label class="description">Comment</label>
      <div>
	<input name="cmt" class="element text medium" type="text" maxlength="255" value=""/>
      </div>
      <p class="guidelines"><small>
	  Type an <em>optional</em> comment for your
	  uploaded file. The comment will be shown along with the file name.
      </small></p>
    </li>

    <li>
      <label class="description">Expire in total # of days</label>
      <div>
	<input name="dn" value="7" class="element text medium" type="text" maxlength="255" value=""/>
      </div>
      <p class="guidelines"><small>
	  Type the <strong>maximal number of days</strong> the uploaded file is allowed to be
	  kept on the server. After this period is passed the file will be deleted from
	  the server.
      </small></p>
    </li>

    <li>
      <label class="description">Expire in # of hours after last dl</label>
      <div>
	<input name="hra" value="24" class="element text medium" type="text" maxlength="255" value=""/>
      </div>
      <p class="guidelines"><small>
	  Type the number of hours the uploaded file is allowed to be kept on
	  the server <strong>after being downloaded</strong>. After this period
	  is passed without activity, the file will be deleted from
	  the server.
      </small></p>
    </li>

    <li>
      <label class="description">Expire after # of downloads</label>
      <div>
	<input name="dln" value="0" class="element text medium" type="text" maxlength="255" value=""/>
      </div>
      <p class="guidelines"><small>
	  Type the number of times the uploaded file is <strong>allowed to be
	  downloaded</strong> in total. After this amount is reached the file will be
	  deleted from the server.
      </small></p>
    </li>

    <li>
      <label class="description">Permanent ticket / upload</label>
      <span>
	<input name="nl" class="element checkbox" type="checkbox" value="1"/>
	<label class="choice">Do not expire</label>
      </span>
      <p class="guidelines"><small>
	  Set this checkmark if you do not want the uploaded file to expire.
      </small></p>
    </li>

    <li>
      <label class="description">Get notified by email</label>
      <div>
	<input name="nt" class="element text medium" type="text" maxlength="255" value=""/>
      </div>
      <p class="guidelines"><small>
	  Type an <em>optional</em> email address (or addresses) that should be
	  notified when the file is downloaded from the server. You can
	  separate multiple addresses with commas.
      </small></p>
    </li>

    <li>
      <label class="description">Send link to email</label>
      <div>
	<input name="st" class="element text medium" type="text" maxlength="255" value=""/>
      </div>
      <p class="guidelines"><small>
	  Type an <em>optional</em> email address (or addresses) that should
	  immediately receive the link to the ticket. You can
	  separate multiple addresses with commas.
      </small></p>
    </li>

    <li class="buttons">
      <input id="submit" type="submit" name="submit" value="Upload"/>
      <input type="reset" name="submit" value="Reset"/>
      <input type="button" name="submit" value="Set as defaults "onclick="setDefaults();"/>
    </li>
  </ul>
</form>

<div id="footer">
  <a href="<?php echo $adminPath; ?>?l">List active tickets</a>,
  <a href="<?php echo $adminPath; ?>?u">Logout</a>
</div>

<?php
includeTemplate('style/include/footer.php');
?>
