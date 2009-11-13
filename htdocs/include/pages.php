<?php
// page tracking utilities

$pages = array
(
  'newt'  => 'New ticket',
  'tlist' => 'Active tickets',
  'grant' => 'New grant',
  'glist' => 'Active grants',
);


function pageHeader($vars = array())
{
  global $act, $pages;

  if(empty($vars['title'])) $vars['title'] = $pages[$act];
  includeTemplate('style/include/header.php', $vars);
}


function pageFooter($vars = array())
{
  global $act, $pages, $adminPath;

  echo '<div id="footer">';

  $first = true;
  foreach($pages as $page => $title)
  {
    if($page == $act) continue;
    if($first) $first = false;
    else echo ", ";
    echo "<a href=\"$adminPath?a=$page\">$title</a>";
  }

  echo ", <a href=\"$adminPath?u\">Logout</a></div>";
  includeTemplate('style/include/footer.php', $vars);
}


function currentPage()
{
  global $adminPath, $act;
  return "$adminPath?a=$act";
}

?>
