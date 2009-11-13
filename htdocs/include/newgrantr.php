<?php
require_once("include/pages.php");
$act = false;
pageHeader(array('title' => 'Grant result'));

// final url
$url = grantUrl($DATA);
$subject = 'upload grant link';
$body = (!isset($DATA['pass'])? $url: "URL: $url\nPassword: " . $DATA['pass']);
$mailto = "mailto:?subject=" . rawurlencode($subject) . "&body=" . rawurlencode($body);
?>

<div>
  <label class="description">Your grant
<?php echo htmlentities(grantStr($DATA)); ?>
  </label>
<p><span class="ticketid"><?php echo htmlentities($url); ?></span></p>
<?php
  if($DATA['pass'])
  {
    echo "<p>The required password is: <tt>"
      . htmlentities($DATA['pass']) . "</tt></p>";
  }

  if($DATA['st'])
  {
    echo "<p>A grant link has been sent to: ";
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
  <input type="button" onclick="document.location='<?php echo htmlentities($mailto); ?>';" value="Send via E-Mail"/>
  <input type="button" onclick="document.location='<?php echo htmlentities($url); ?>';" value="Upload"/>
</span>

<?php
pageFooter();
?>
