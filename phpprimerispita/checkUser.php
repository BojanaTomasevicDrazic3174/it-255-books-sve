<?php
// checkUser.php
header('Access-Control-Allow-Methods:POST');
include("functions.php");

if(isset($_POST['token'])){

$token = $_POST['token'];

if ($token === $_SERVER['HTTP_TOKEN']) {
  if (getKorisnikByIme()) {
    echo json_encode('ok');
  } else {
    echo json_encode('Bad');
  }
} else {
  echo json_encode('Los token');
}

}
?>
