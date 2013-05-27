<?php
// upload progress helpers
$uploadProgress = ini_get('apc.rfc1867');
$uploadPrefix = ini_get('apc.rfc1867_prefix');
$uploadName = ini_get('apc.rfc1867_name');


function newUploadProgress()
{
  return randomToken();
}


function uploadProgressPc($data)
{
  global $uploadProgress, $uploadPrefix;
  if(!$uploadProgress) return false;

  $status = apc_fetch($uploadPrefix . $data);
  return (!isset($status['current'])? false:
      round($status['current'] * 100 / $status['total']));
}


function uploadProgressHdr($data)
{
  global $uploadProgress;
  if(!$uploadProgress) return;

?>
  <script type="text/javascript" src="static/progress-polyfill.js"></script>
  <style type="text/css" src="static/progress-polyfill.css"></style>
  <iframe style="display: none;" src="static/progress.html"></iframe>
  <script type="text/javascript">

    var progressKey = '<?php echo $data; ?>';
    var progress;

    $(document).ready(function()
    {
      progress = $("#uploadprogressbar");
      progress.attr({'min': 0, 'max': 100});
    });

  </script>
<?php
}


function uploadProgressField($data)
{
  global $uploadProgress, $uploadName;
  if(!$uploadProgress) return;

  echo '<input type="hidden" name="' . htmlentities($uploadName) . '" value="' . $data . '" />';
}


function uploadProgressHtml($data)
{
  global $uploadProgress;
  if(!$uploadProgress) return;
  echo '<progress style="margin-top: 1em; display: none;" id="uploadprogressbar"></progress>';
}

?>
