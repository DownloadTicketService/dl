<?php
// page tracking utilities

$pages = array
(
  'newt'   => T_("New ticket"),
  'tlist'  => T_("Active tickets"),
  'newg'   => T_("New grant"),
  'glist'  => T_("Active grants"),
  'trecv'  => T_("Received files"),
  'prefs'  => T_("Preferences"),
  'users'  => T_("Manage users"),
  'tlista' => T_("All tickets"),
  'glista' => T_("All grants"),
);


function pageLink($page, $params = array())
{
  global $adminPath;
  $params['a'] = $page;
  return htmlentities(tokenUrl($adminPath, $params));
}


function pageLinkAct($params = array())
{
  global $act;
  return pageLink($act, $params);
}


function pageHeader($vars = array())
{
  global $act, $pages, $style;
  if(empty($vars['title'])) $vars['title'] = $pages[$act];
  includeTemplate("$style/include/header.php", $vars);
}


function pageFooter($vars = array())
{
  global $act, $pages, $style, $entry, $auth, $adminPath, $helpPath;

  echo '<div id="footer">';

  $first = true;
  foreach($pages as $page => $title)
  {
    if($entry[$page]['admin'] && !$auth['admin'])
      continue;

    if($first) $first = false;
    else echo ", ";

    $title = htmlEntUTF8($title);
    if($page == $act) echo "<span>$title</span>";
    else echo "<a href=\"" . pageLink($page) . "\">$title</a>";
  }

  echo ", <a href=\"$adminPath?u\">" . T_("Logout") . "</a>"
    . ", <a href=\"$helpPath\" target=\"_blank\">" . T_("Help") . "</a></div>";
  includeTemplate("$style/include/footer.php", $vars);
}
