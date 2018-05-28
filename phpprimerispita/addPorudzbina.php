<?php
// addPorudzbina.php
header('Access-Control-Allow-Methods: GET, POST');
include("functions.php");
if(isset($_POST['KNJIGA_ID'])) {

$knjiga_id = $_POST['KNJIGA_ID'];

echo addPorudzbina($knjiga_id);
}
else
{
  echo json_encode('losi podaci');
}

?>
