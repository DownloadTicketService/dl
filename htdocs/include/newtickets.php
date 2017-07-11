<?php
require_once("pages.php");
$act = "newt";
$ref = pageLinkAct();
pageHeader();

if($UPLOAD_ERRNO !== UPLOAD_ERR_OK)
  errorMessage(T_("Upload failed"), uploadErrorStr());
?>

<form enctype="multipart/form-data" method="post"
      action="<?php echo $ref; ?>"
      defaults="newticket"
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
	  printf(T_("Choose which file/s to upload. You can upload up to %d files"
		  . " for a total of %s."),
		 $maxFiles, humanSize($iMaxSize));
	?>
      </small></p>
    </li>

    <li>
      <label class="description"><?php echo T_("Comment"); ?></label>
      <div>
	<textarea name="comment" class="element textarea"></textarea>
      </div>
      <p class="guidelines"><small>
	  <?php
	    echo T_("Type an <em>optional</em> comment for your uploaded file."
		. " The comment will be shown along with the file name.");
	  ?>
      </small></p>
    </li>

    <li>
      <label class="description"><?php echo T_("Send link to e-mail"); ?></label>
      <div>
	<input name="send_to" class="element text" type="email" multiple value=""/>
      </div>
      <p class="guidelines"><small>
	  <?php
	    echo T_("Type an <em>optional</em> e-mail address (or addresses) that"
		. " should immediately receive the link to the ticket. You can"
		. " separate multiple addresses with commas.");
	  ?>
      </small></p>
    </li>

  </ul>
  <a id="toggler" class="active" href="#"><?php echo T_('Advanced'); ?></a>
  <ul id="advanced" class="active">

    <li>
      <label class="description"><?php echo T_("Password"); ?></label>
      <div>
	<input name="pass" class="element text password" type="text" maxlength="<?php echo $maxPassLen; ?>" value=""/>
	<input class="element button password" type="button" value="<?php echo T_("Generate"); ?>" onclick="passGen();"/>
      </div>
      <p class="guidelines"><small>
	  <?php
	    echo T_("Type an <em>optional</em> password that will be required"
		. " to download the file, as an additional security measure.");
	  ?>
      </small></p>
    </li>

    <li>
      <label class="description"><?php echo T_("Ticket expiry"); ?></label>
      <div>
	<select id="tex" name="ticket_expiry" class="element">
	  <option value="auto"><?php echo T_("Automatic"); ?></option>
	  <option value="once"><?php echo T_("Single use"); ?></option>
	  <option value="never"><?php echo T_("No expiration"); ?></option>
	  <option value="custom"><?php echo T_("Custom"); ?></option>
	</select>
      </div>
      <p class="guidelines"><small>
	<?php
	echo T_("Select the expiration logic of the ticket. <strong>Automatic</strong>"
	      . " will keep the ticket as long as it's being actively downloaded/used."
	      . " <strong>Single use</strong> allows <em>only one download</em> to be"
	      . " performed. <strong>No expiration</strong> will never remove the"
	      . " ticket automatically.");
	?>
      </small></p>

      <ul id="tex_data">
	<li>
	  <label class="description"><?php echo T_("Expire in total # of days"); ?></label>
	  <div>
	    <input name="ticket_totaldays" value="<?php echo (int)($defaults['ticket']['total'] / (3600 * 24)); ?>" class="element text" type="number" min="0" maxlength="255" value=""/>
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
	  <label class="description"><?php echo T_("Expire in # of days after last download"); ?></label>
	  <div>
	    <input name="ticket_lastdldays" value="<?php echo (int)($defaults['ticket']['lastdl'] / (3600 * 24)); ?>" class="element text" type="number" min="0" maxlength="255" value=""/>
	  </div>
	  <p class="guidelines"><small>
	    <?php
	    echo T_("Type the number of days the uploaded file is allowed to be"
		  . " kept on the server <strong>after being downloaded</strong>."
		  . " After this period is passed without activity, the file will"
		  . " be deleted from the server.");
	    ?>
	  </small></p>
	</li>

	<li>
	  <label class="description"><?php echo T_("Expire after # of downloads"); ?></label>
	  <div>
	    <input name="ticket_maxdl" value="<?php echo $defaults['ticket']['maxdl']; ?>" class="element text" type="number" min="0" maxlength="255" value=""/>
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

      </ul>
    </li>

    <li>
      <label class="description"><?php echo T_("Get notified by e-mail"); ?></label>
      <div>
	<input name="notify" id="notify" class="element text" type="email" multiple maxlength="255" value=""/>
	<input class="element button password" type="button" value="<?php echo T_("Use my e-mail"); ?>" onclick="setNt('<?php echo htmlentities($auth['email']); ?>');"/>
      </div>
      <p class="guidelines"><small>
	  <?php
	    echo T_("Type an <em>optional</em> e-mail address (or addresses) that"
		. " should be notified when the file is downloaded from the"
		. " server. You can separate multiple addresses with commas.");
	  ?>
      </small></p>
    </li>

  </ul>
  <ul>
    <li class="buttons">
      <input type="hidden" name="submit" value="<?php echo $act; ?>"/>
      <input id="submit" type="submit" value="<?php echo T_("Upload"); ?>"/>
      <input type="reset" value="<?php echo T_("Reset"); ?>"/>
      <input type="button" id="setDefaults" value="<?php echo T_("Set as defaults"); ?>"/>
      <div id="uploadprogress"></div>
    </li>
  </ul>
</form>

<?php
pageFooter();
