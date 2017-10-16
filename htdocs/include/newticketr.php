<?php
require_once("pages.php");
$act = false;
pageHeader(array('title' => T_("Upload result")));

// final url
msgTicketCreate($DATA, $subject, $body);
$url = ticketUrl($DATA);
$mailto = "mailto:?subject=" . rawurlencode($subject) . "&body=" . rawurlencode($body);
?>

<div>
  <label class="description">
    <?php printf(T_("Your ticket %s"), htmlEntUTF8(ticketStr($DATA))); ?>
  </label>
  <p><span class="ticketid"><?php echo htmlentities($url); ?></span></p>
<?php
  if($DATA['pass'])
  {
    echo "<p>" . T_("The required password is:") . " <tt>"
      . htmlEntUTF8($DATA['pass']) . "</tt></p>";
  }

  if($DATA['sent_email'])
  {
    echo "<p>" . T_("A download link has been sent to:") . " ";
    $addrs = getEMailAddrs($DATA['sent_email']);
    foreach($addrs as &$addr)
    {
      $addr = '<a href="mailto:' . rawurlencode($addr) . '">'
	. htmlentities($addr) . '</a>';
    }
    echo join(', ', $addrs);
    echo '</p>';
  }
?>
</div>

<span class="buttons">
  <input type="button" onclick="document.location='<?php echo htmlentities($mailto); ?>';" value="<?php echo T_("Send via e-mail"); ?>"/>
  <input type="button" onclick="document.location='<?php echo htmlentities($url); ?>';" value="<?php echo T_("Download"); ?>"/>
</span>

<?php
pageFooter();
