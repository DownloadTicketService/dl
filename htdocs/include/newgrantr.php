<?php
require_once("pages.php");
$act = false;
pageHeader(array('title' => T_("Grant result")));

// final url
$url = grantUrl($DATA);
$subject = T_("upload grant link");
$body = (!isset($DATA['pass'])? $url: (T_("URL:") . " $url\n" .  T_("Password:") . " " . $DATA['pass'] . "\n"));
$mailto = "mailto:?subject=" . rawurlencode($subject) . "&body=" . rawurlencode($body);
?>

<div>
  <label class="description">
    <?php printf(T_("Your grant %s"), htmlEntUTF8(grantStr($DATA))); ?>
  </label>
<p><span class="ticketid"><?php echo htmlentities($url); ?></span></p>
<?php
  if($DATA['pass'])
  {
    echo "<p>" . T_("The required password is:") . " <tt>"
      . htmlEntUTF8($DATA['pass']) . "</tt></p>";
  }

  if($DATA['st'])
  {
    echo "<p>" . T_("A grant link has been sent to:") . " ";
    $addrs = getEMailAddrs($DATA['st']);
    foreach($addrs as &$addr)
    {
      $addr = '<a href="mailto:' . urlencode($addr) . '">'
	. htmlentities($addr) . '</a>';
    }
    echo join(', ', $addrs);
    echo '</p>';
  }
?>
</div>

<span class="buttons">
  <input type="button" onclick="document.location='<?php echo htmlentities($mailto); ?>';" value="<?php echo T_("Send via e-mail"); ?>"/>
  <input type="button" onclick="document.location='<?php echo htmlentities($url); ?>';" value="<?php echo T_("Upload"); ?>"/>
</span>

<?php
pageFooter();
?>
