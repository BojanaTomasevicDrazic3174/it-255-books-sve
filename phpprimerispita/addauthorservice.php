<?php
header('Access-Control-Allow-Methods: GET, POST');
include("functions.php");

if(isset($_POST['name']) && isset($_POST['surname'])){

$surname = $_POST['surname'];
$name = $_POST['name'];
echo addAutor($name,$surname);
} else {
	 echo json_encode('Losi podaci');
}
?>
