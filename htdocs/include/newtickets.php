<?php
require_once("pages.php");
$act = "newt";
$ref = "$adminPath?a=$act";
pageHeader();

require_once("progress.php");
$up = newUploadProgress();
?>

<script type="text/javascript" src="static/defaults.js"></script>
<?php uploadProgressHdr($up); ?>

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
      <label class="<?php echo $class; ?>"><? echo T_("Upload a file"); ?></label>
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

    <li>
      <label class="description"><?php echo T_("Comment"); ?></label>
      <div>
	<input name="cmt" class="element text medium" type="text" maxlength="255" value=""/>
      </div>
      <p class="guidelines"><small>
	  <?php
	    echo T_("Type an <em>optional</em> comment for your uploaded file."
		. " The comment will be shown along with the file name.");
	  ?>
      </small></p>
    </li>

    <li>
      <label class="description"><?php echo T_("Password"); ?></label>
      <div>
	<input name="pass" class="element text medium" type="text" maxlength="255" value=""/>
        <input class="element button" type="button" value="<?php echo T_("Generate"); ?>" onclick="passGen();"/>
      </div>
      <p class="guidelines"><small>
	  <?php
	    echo T_("Type an <em>optional</em> password that will be required"
		. " to download the file, as an additional security measure.");
	  ?>
      </small></p>
    </li>

    <li>
      <label class="description"><?php echo T_("Expire in total # of days"); ?></label>
      <div>
	<input name="dn" value="7" class="element text medium" type="text" maxlength="255" value=""/>
      </div>
      <p class="guidelines"><small>
	  <?php
	    echo T_("Type the <strong>maximal number of days</strong> the"
		. " uploaded file is allowed to be kept on the server. After"
		. " this period is passed the file will be deleted from the"
		. " server.");
	  ?>
      </small></p>
    </li>

    <li>
      <label class="description"><?php echo T_("Expire in # of hours after last dl"); ?></label>
      <div>
	<input name="hra" value="24" class="element text medium" type="text" maxlength="255" value=""/>
      </div>
      <p class="guidelines"><small>
	  <?php
	    echo T_("Type the number of hours the uploaded file is allowed to be"
		. " kept on the server <strong>after being downloaded</strong>."
		. " After this period is passed without activity, the file will"
		. " be deleted from the server.");
	  ?>
      </small></p>
    </li>

    <li>
      <label class="description"><?php echo T_("Expire after # of downloads"); ?></label>
      <div>
	<input name="dln" value="0" class="element text medium" type="text" maxlength="255" value=""/>
      </div>
      <p class="guidelines"><small>
	  <?php
	    echo T_("Type the number of times the uploaded file is"
		. " <strong>allowed to be downloaded in total</strong>. After"
		. " this amount is reached the file will be deleted from the"
		. " server.");
          ?>
      </small></p>
    </li>

    <li>
      <label class="description"><?php echo T_("Permanent ticket / upload"); ?></label>
      <span>
	<input name="nl" class="element checkbox" type="checkbox" value="1"/>
	<label class="choice"><?php echo T_("Do not expire"); ?></label>
      </span>
      <p class="guidelines"><small>
	  <?php
            echo T_("Set this checkmark if you do not want the uploaded file to expire.");
          ?>
      </small></p>
    </li>

    <li>
      <label class="description"><?php echo T_("Get notified by e-mail"); ?></label>
      <div>
	<input name="nt" class="element text medium" type="text" maxlength="255" value=""/>
      </div>
      <p class="guidelines"><small>
	  <?php
            echo T_("Type an <em>optional</em> e-mail address (or addresses) that"
		. " should be notified when the file is downloaded from the"
		. " server. You can separate multiple addresses with commas.");
          ?>
      </small></p>
    </li>

    <li>
      <label class="description"><?php echo T_("Send link to e-mail"); ?></label>
      <div>
	<input name="st" class="element text medium" type="text" maxlength="255" value=""/>
      </div>
      <p class="guidelines"><small>
	  <?php
            echo T_("Type an <em>optional</em> e-mail address (or addresses) that"
		. " should immediately receive the link to the ticket. You can"
		. " separate multiple addresses with commas.");
          ?>
      </small></p>
    </li>

    <li class="buttons">
      <input type="hidden" name="submit" value="<?php echo $act; ?>"/>
      <input id="submit" type="submit" value="<?php echo T_("Upload"); ?>"/>
      <input type="reset" value="<?php echo T_("Reset"); ?>"/>
      <input type="button" value="<?php echo T_("Set as defaults"); ?>" onclick="setDefaults();"/>
      <?php uploadProgressHtml($up); ?>
    </li>
  </ul>
</form>

<?php
pageFooter();
?>
