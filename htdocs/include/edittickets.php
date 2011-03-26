<?php
require_once("pages.php");
$act = "tedit";
$ref = "$adminPath?a=$act&id=$id";
$title = sprintf(T_("Editing ticket %s"), "<span class=\"ticketid\">$id</span>");
pageHeader(array('title' => $title));

// form values
$name = anyOf(@$_POST['name'], $DATA['name']);
$cmt = anyOf(@$_POST['cmt'], $DATA['cmt']);
$hasPass = isset($DATA['pass_md5']);
$pass = anyOf(@$_POST['pass'], "");
$clr = anyOf(@$_POST['clr'], "");
$nl = anyOf(@$_POST['nl'], !$DATA['expire']);
$nt = anyOf(@$_POST['nt'], join(", ", getEMailAddrs($DATA['notify_email'])));

// current expiration values
if(isset($_POST['dn']))
  $dn = $_POST['dn'];
elseif($DATA["expire"])
  $dn = ceil(($DATA["expire"] - time()) / (3600 * 24));
else
  $dn = $defaultTicketTotalDays;

if(isset($_POST['hra']))
  $hra = $_POST['hra'];
elseif($DATA["last_time"])
  $hra = ceil($DATA["last_time"] / 3600);
else
  $hra = $defaultTicketLastDl;

if(isset($_POST['dln']))
  $dln = $_POST['dln'];
elseif($DATA["expire_dln"])
  $dln = ($DATA["expire_dln"] - $DATA["downloads"]);
else
  $dln = $defaultTicketMaxDl;

// current expiry
infoMessage('Current expiry', ticketExpiry($DATA));

?>

<form enctype="multipart/form-data" method="post"
      onsubmit="validate(event);" action="<?php echo $ref; ?>" >
  <ul>
    <li>
      <?php
	$error = ((@$_POST["submit"] === $act) && empty($_POST["name"]));
	$class = "description required" . ($error? " error": "");
      ?>
      <label class="<?php echo $class; ?>"><?php echo T_("File name"); ?></label>
      <div>
	<input name="name" class="element text required" type="text" value="<?php echo htmlEntUTF8($name); ?>"/>
      </div>
      <p class="guidelines"><small>
	  <?php
	    echo T_("Type the file name of the ticket (<em>including the extension</em>).");
	  ?>
      </small></p>
    </li>

    <li>
      <label class="description"><?php echo T_("Comment"); ?></label>
      <div>
	<textarea name="cmt" class="element textarea"><?php echo htmlEntUTF8($cmt); ?></textarea>
      </div>
      <p class="guidelines"><small>
	  <?php
	    echo T_("Type an <em>optional</em> comment for your uploaded file."
		. " The comment will be shown along with the file name.");
	  ?>
      </small></p>
    </li>

    <li>
      <label class="description"><?php echo ($hasPass? T_("Change password"): T_("Password")); ?></label>
      <div>
	<input name="pass" class="element text password" type="text" maxlength="255"
	  placeholder="<?php if($hasPass) echo str_repeat('&bull;', 5); ?>"
	  value="<?php echo htmlEntUTF8($pass); ?>"/>
	<input class="element button password" type="button" value="<?php echo T_("Generate"); ?>" onclick="passGen();"/>
      </div>
      <?php if($hasPass) { ?>
      <div>
	 <input name="clr" id="clr" class="element checkbox" type="checkbox"
	   <?php if($clr) echo 'checked="checked"'; ?> value="1"/>
	<label for="clr" class="choice"><?php echo T_("Clear password"); ?></label>
      </div>
      <?php } ?>
      <p class="guidelines"><small>
	  <?php
	    echo T_("Type an <em>optional</em> password that will be required"
		. " to download the file, as an additional security measure.");
            if($hasPass)
	      echo " " . T_("Checking \"Clear password\" will remove the password.");
	  ?>
      </small></p>
    </li>

    <li>
      <label class="description"><?php echo T_("Expire in total # of days"); ?></label>
      <div>
	<input name="dn" value="<?php echo $dn; ?>" class="element text" type="text" maxlength="255" value=""/>
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
	<input name="hra" value="<?php echo $hra; ?>" class="element text" type="text" maxlength="255" value=""/>
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
	<input name="dln" value="<?php echo $dln; ?>" class="element text" type="text" maxlength="255" value=""/>
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
      <div>
	<input name="nl" id="nl" class="element checkbox" type="checkbox"
	   <?php if($nl) echo 'checked="checked"'; ?> value="1"/>
	<label for="nl" class="choice"><?php echo T_("Do not expire"); ?></label>
      </div>
      <p class="guidelines"><small>
	  <?php
	    echo T_("Set this checkmark if you do not want the uploaded file to expire.");
	  ?>
      </small></p>
    </li>

    <li>
      <label class="description"><?php echo T_("Get notified by e-mail"); ?></label>
      <div>
	<input name="nt" class="element text" type="text" maxlength="255" value="<?php echo htmlEntUTF8($nt); ?>"/>
      </div>
      <p class="guidelines"><small>
	  <?php
	    echo T_("Type an <em>optional</em> e-mail address (or addresses) that"
		. " should be notified when the file is downloaded from the"
		. " server. You can separate multiple addresses with commas.");
	  ?>
      </small></p>
    </li>

    <li class="buttons">
      <input type="hidden" name="submit" value="<?php echo $act; ?>"/>
      <input id="submit" type="submit" value="<?php echo T_("Update"); ?>"/>
      <input type="reset" value="<?php echo T_("Reset"); ?>"/>
    </li>
  </ul>
</form>

<?php
pageFooter();
?>
