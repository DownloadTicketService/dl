<?php
// page entry points

$entryAuth = 'login';
$entryDefault = 'newt';

$entry = array
(
  'login' => array('admin' => false, 'entry' => 'include/login.php'),
  'newt'  => array('admin' => false, 'entry' => 'include/newticket.php'),
  'tlist' => array('admin' => false, 'entry' => 'include/ticketl.php'),
  'tedit' => array('admin' => false, 'entry' => 'include/editticket.php'),
  'newg'  => array('admin' => false, 'entry' => 'include/newgrant.php'),
  'glist' => array('admin' => false, 'entry' => 'include/grantl.php'),
  'prefs' => array('admin' => false, 'entry' => 'include/prefs.php'),
  'users' => array('admin' => true,  'entry' => 'include/users.php'),
);

$rest = array
(
  'info' => array
  (
    'method'  => 'GET',
    'admin' => false,
    'entry' => 'include/restinfo.php',
    'func'  => 'info',
  ),
  'newticket' => array
  (
    'method' => 'POST',
    'admin' => false,
    'entry' => 'include/restnewticket.php',
    'func'  => 'newticket'
  ),
);

?>
