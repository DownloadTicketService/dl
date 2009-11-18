// defaults
var uploadProgressDelay = 1000;
var uploadProgressInterval = 750;

// hooks
$(document).ready(function()
{
  $("#uploadprogressbar").progressBar();
  $("#submit").click(function() { setTimeout("beginUpload()", uploadProgressDelay); });
});


// upload status
function beginUpload()
{
  $("#uploadprogressbar").slideDown();
  showUpload();
}


function showUpload()
{
  $.getJSON("index.php?s=" + progressKey, function(data)
  {
    var pc = parseInt(data['percent']);
    $("#uploadprogressbar").progressBar(pc);
  });
  setTimeout("showUpload()", uploadProgressInterval);
}