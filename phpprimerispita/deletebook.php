<?php
// deletebook.php
header('Access-Control-Allow-Methods: POST');
include("functions.php");

if(isset($_POST['ID'])){
	$id = intval($_POST['ID']);
	echo deleteBook($id);
} else {
	echo json_encode('Losi podaci');
}


?>
