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
  'users' => array('admin' => true,  'entry' => 'include/users.php'),
);

?>