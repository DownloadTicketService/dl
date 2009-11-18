<?php
// output upload percentage status
require_once("progress.php");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

$id = $_REQUEST["s"];
$pc = uploadProgressPc($id);
echo json_encode(array('percent' => $pc));
?>
