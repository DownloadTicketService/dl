<?php
require_once("pages.php");
$act = false;
pageHeader(array('title' => T_("Grant result")));

// final url
msgGrantCreate($DATA, $subject, $body);
$url = grantUrl($DATA);
$mailto = "mailto:?subject=" . rawurlencode($subject) . "&body=" . rawurlencode($body);
?>

<div>
  <label class="description">
    <?php printf(T_("Your grant %s"), htmlEntUTF8(grantStr($DATA))); ?>
  </label><p><a class="ticketid" href="<?php echo $url; ?>">
    <?php echo htmlentities($url); ?>
  </a></p>
  <?php
  if($DATA['pass'])
  {
    echo "<p>" . T_("The required password is:") . " <tt>"
       . htmlEntUTF8($DATA['pass']) . "</tt></p>";
  }

  if($DATA['sent_email'])
  {
    echo "<p>" . T_("A grant link has been sent to:") . " ";
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
  <input type="button" onclick="copyToClipboard('<?php echo htmlentities($url); ?>');" value="<?php echo T_("Copy to clipboard"); ?>"/>
  <input type="button" onclick="document.location='<?php echo htmlentities($mailto); ?>';" value="<?php echo T_("Send via e-mail"); ?>"/>
</span>

<?php
pageFooter();
