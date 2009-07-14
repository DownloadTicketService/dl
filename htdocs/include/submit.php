<?php
$title = 'Upload Request';
includeTemplate('style/include/header.php', compact('title'));
?>

<script type="text/javascript">
  window.addEventListener("load", loadDefaults, false)
</script>

<form enctype="multipart/form-data" method="post"
      onsubmit="document.getElementById('submit').disabled = true;"
      action="<?php echo $masterPath; ?>" >
  <ul>
    <li>
      <label class="description">Upload a File</label>
      <div>
	<input name="file" class="element file" type="file"/>
      </div>
      <p class="guidelines"><small>
	  Choose which file to upload. You can upload up to <?php echo
	  $hMaxSize; ?>.
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
      <label class="description">Expire in total # of hours</label>
      <div>
	<input name="hr" value="168" class="element text medium" type="text" maxlength="255" value=""/>
      </div>
      <p class="guidelines"><small>
	  Type the <strong>maximal number of hours</strong> the uploaded file is allowed to be
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
	  Type an <em>optional</em> email address(es) that should be notified when the file is
	  downloaded from the server. Separate multiple addresses with
	  commas.
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
  <a href="<?php echo $masterPath; ?>?l">List active tickets</a>,
  <a href="<?php echo $masterPath; ?>?u">Logout</a>
</div>

<?php
includeTemplate('style/include/footer.php');
?>
