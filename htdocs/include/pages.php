<?php
// page tracking utilities

$pages = array
(
  'newt'  => T_("New ticket"),
  'tlist' => T_("Active tickets"),
  'newg'  => T_("New grant"),
  'glist' => T_("Active grants"),
  'users' => T_("Users management"),
);


function pageHeader($vars = array())
{
  global $act, $pages;
  if(empty($vars['title'])) $vars['title'] = $pages[$act];
  includeTemplate('style/include/header.php', $vars);
}


function pageFooter($vars = array())
{
  global $act, $pages, $entry, $auth, $adminPath, $helpPath;

  echo '<div id="footer">';

  $first = true;
  foreach($pages as $page => $title)
  {
    if($entry[$page]['admin'] && !$auth['admin'])
      continue;

    if($first) $first = false;
    else echo ", ";

    if($page == $act) echo $title;
    else echo "<a href=\"$adminPath?a=$page\">$title</a>";
  }

  echo ", <a href=\"$adminPath?u\">" . T_("Logout") . "</a>"
    . ", <a href=\"$helpPath\" target=\"_blank\">" . T_("Help") . "</a></div>";
  includeTemplate('style/include/footer.php', $vars);
}

?>
