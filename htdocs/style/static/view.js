$(document).ready(function()
{
  $(".element").each(function()
  {
    var e = $(this);
    e.focus(function() { $(this).parent().parent().addClass("highlighted"); });
    e.blur(function() { $(this).parent().parent().removeClass("highlighted"); });
  });

  $("tr.file.comment").click(function() { $(this).toggleClass("expanded"); } );
});
