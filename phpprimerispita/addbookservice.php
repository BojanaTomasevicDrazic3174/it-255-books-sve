<?php
// addbookservice.php
header('Access-Control-Allow-Methods: GET, POST');
include("functions.php");

if(isset($_POST['autor_id']) && isset($_POST['name']) && isset($_POST['isbn'])){


$autor_id = $_POST['autor_id'];
$name = $_POST['name'];
$isbn = $_POST['isbn'];

echo addKnjiga($autor_id, $name, $isbn);
}
?>
