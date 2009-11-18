<?php
// upload progress helpers
$uploadProgress = ini_get('apc.rfc1867');


function newUploadProgress()
{
  return uniqid(rand());
}


function uploadProgressPc($data)
{
  global $uploadProgress;
  if(!$uploadProgress) return false;

  $status = apc_fetch("upload_$data");
  return (!isset($status['current'])? false:
      round($status['current'] * 100 / $status['total']));
}


function uploadProgressHdr($data)
{
  global $uploadProgress;
  if(!$uploadProgress) return;

?>
  <script type="text/javascript" src="static/jquery.js"></script>
  <script type="text/javascript" src="static/jquery.progressbar.min.js"></script>
  <script type="text/javascript" src="static/progress.js"></script>
  <script type="text/javascript">
   var progressKey = '<?php echo $data; ?>';
  </script>
<?php
}


function uploadProgressField($data)
{
  global $uploadProgress;
  if(!$uploadProgress) return;

  echo '<input type="hidden" name="APC_UPLOAD_PROGRESS" value="' . $data . '" />';
}


function uploadProgressHtml($data)
{
  global $uploadProgress;
  if(!$uploadProgress) return;
  echo '<div style="padding-top: 1em; display: none;" id="uploadprogressbar"></div>';
}

?>