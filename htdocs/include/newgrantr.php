<?php
require_once("include/pages.php");
$act = false;
pageHeader(array('title' => _("Grant result")));

// final url
$url = grantUrl($DATA);
$subject = _("upload grant link");
$body = (!isset($DATA['pass'])? $url: (_("URL:") . " $url\n" .  _("Password:") . " " . $DATA['pass'] . "\n"));
$mailto = "mailto:?subject=" . rawurlencode($subject) . "&body=" . rawurlencode($body);
?>

<div>
  <label class="description">
    <?php printf(_("Your grant %s"), htmlentities(grantStr($DATA))); ?>
  </label>
<p><span class="ticketid"><?php echo htmlentities($url); ?></span></p>
<?php
  if($DATA['pass'])
  {
    echo "<p>" . _("The required password is:") . " <tt>"
      . htmlentities($DATA['pass']) . "</tt></p>";
  }

  if($DATA['st'])
  {
    echo "<p>" . _("A grant link has been sent to:") . " ";
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
  <input type="button" onclick="document.location='<?php echo htmlentities($mailto); ?>';" value="<?php echo _("Send via e-mail"); ?>"/>
  <input type="button" onclick="document.location='<?php echo htmlentities($url); ?>';" value="<?php echo _("Upload"); ?>"/>
</span>

<?php
pageFooter();
?>
