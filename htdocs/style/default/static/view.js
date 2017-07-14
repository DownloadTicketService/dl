$(document).ready(function()
{
  $(".element").each(function()
  {
    var e = $(this);
    e.focus(function()
    {
      $(".guidelines").hide();
      var root = $(this).parent().parent();
      root.addClass("highlighted");
      $(".guidelines", $(this).parent().parent()).first().show();
    });
    e.blur(function() { $(this).parent().parent().removeClass("highlighted"); });
  });
  $("form li").hover(function()
  {
    $(".guidelines").hide();
    $(".guidelines", this).first().show();
  });

  $("tr.file.comment").click(function() { $(this).toggleClass("expanded"); } );

  // force the focus event to trigger if an input is already focused
  if(document.activeElement)
    $(document.activeElement).trigger('focus');
});
