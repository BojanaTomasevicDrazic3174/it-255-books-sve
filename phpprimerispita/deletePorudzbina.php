<?php
// deletePorudzbina.php
header('Access-Control-Allow-Methods: GET, POST');
include("functions.php");
if(isset($_POST['ID'])) {

$id =intval($_POST['ID']);

echo deletePorudzbina($id);
}
else
{
  echo json_encode('losi podaci');
}

?>
