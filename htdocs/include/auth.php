<?php
// initialize the session and authorization

function authenticate()
{
  global $db, $authRealm;

  // external authentication (built-in methods)
  foreach(Array('PHP_AUTH_USER', 'REMOTE_USER', 'REDIRECT_REMOTE_USER') as $key)
  {
    if(isset($_SERVER[$key]))
    {
      $remoteUser = $_SERVER[$key];
      break;
    }
  }

  // authentication attempt
  if(!isset($remoteUser))
  {
    if(empty($_REQUEST['u']) || !isset($_REQUEST['p']))
    {
      // simple logout
      return false;
    }

    $user = $_REQUEST['u'];
    $pass = md5($_REQUEST['p']);
  }
  else
  {
    if(isset($_REQUEST['u']) && empty($_REQUEST['u']))
    {
      // remote logout
      Header('HTTP/1.0 401 Unauthorized');
      Header('WWW-Authenticate: Basic realm="' . $authRealm . '"');
      includeTemplate('style/include/rmtlogout.php');
      exit();
    }

    $user = $remoteUser;
  }

  // verify if we have administration rights
  $sql = "SELECT u.id, u.name, pass_md5, admin FROM user u"
    . " LEFT JOIN role r ON r.id = u.role_id"
    . " WHERE u.name = " . $db->quote($user);
  $DATA = $db->query($sql)->fetch();
  if($DATA !== false)
    $okpass = (isset($remoteUser) || ($pass === $DATA['pass_md5']));
  else
  {
    $okpass = isset($remoteUser);
    if($okpass)
    {
      // create a stub user and get the id
      $sql = "INSERT INTO user (name, role_id) VALUES (";
      $sql .= $db->quote($user);
      $sql .= ", (SELECT id FROM role WHERE name = 'user')";
      $sql .= ")";
      if($db->exec($sql) != 1) return false;

      // fetch defaults
      $sql = "SELECT u.id, u.name, admin FROM user";
      $sql .= " LEFT JOIN role r ON r.id = u.role_id";
      $sql .= " WHERE ROWID = last_insert_rowid()";
      $DATA = $db->query($sql)->fetch();
    }
  }

  if(!$okpass) return false;
  return $DATA;
}

if(!isset($_SESSION["auth"]) || isset($_REQUEST['u']))
  $_SESSION["auth"] = authenticate();

?>
