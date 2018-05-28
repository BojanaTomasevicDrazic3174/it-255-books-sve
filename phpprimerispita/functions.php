<?php
include("config.php");

function checkIfLoggedIn(){
	global $conn;
	if(isset($_SERVER['HTTP_TOKEN'])){
		$token = $_SERVER['HTTP_TOKEN'];
		$result = $conn->prepare("SELECT * FROM KORISNICI WHERE TOKEN=?");
		$result->bind_param("s",$token);
		$result->execute();
		$result->store_result();
		$num_rows = $result->num_rows;
		if($num_rows > 0)
		{
			return true;
		}
		else{
			return false;
		}
	}
	else{
		return false;
	}
}

function login($username, $password){
	global $conn;
	$rarray = array();
	if(checkLogin($username,$password)){
		$id = sha1(uniqid());
		$result2 = $conn->prepare("UPDATE KORISNICI SET TOKEN=? WHERE USERNAME=?");
		$result2->bind_param("ss",$id,$username);
		$result2->execute();
		$rarray['token'] = $id;
		$_SESSION["token"] = $id;
	} else{
		header('HTTP/1.1 401 Unauthorized');
		$rarray['error'] = "Invalid username/password";
	}
	return json_encode($rarray);
}

function checkLogin($username, $password){
	global $conn;
	$password = md5($password);
	$result = $conn->prepare("SELECT * FROM KORISNICI WHERE USERNAME=? AND PASSWORD=?");
	$result->bind_param("ss",$username,$password);
	$result->execute();
	$result->store_result();
	$num_rows = $result->num_rows;
	if($num_rows > 0)
	{
		return true;
	}
	else{
		return false;
	}
}

function register($username, $password, $firstname, $lastname){
	global $conn;
	$rarray = array();
	$errors = "";
	if(checkIfUserExists($username)){
		$errors .= "Username already exists\r\n";
	}
	if(strlen($username) < 5){
		$errors .= "Username must have at least 5 characters\r\n";
	}
	if(strlen($password) < 5){
		$errors .= "Password must have at least 5 characters\r\n";
	}
	if(strlen($firstname) < 3){
		$errors .= "First name must have at least 3 characters\r\n";
	}
	if(strlen($lastname) < 3){
		$errors .= "Last name must have at least 3 characters\r\n";
	}
	if($errors == ""){
		//$id = sha1(uniqid());
		$stmt = $conn->prepare("INSERT INTO KORISNICI (FIRSTNAME, LASTNAME, USERNAME, PASSWORD) VALUES (?, ?, ?, ?)");

		//$stmt = $conn->prepare("INSERT INTO KORISNICI (FIRSTNAME, LASTNAME, USERNAME, PASSWORD, TOKEN) VALUES (?, ?, ?, ?, ?)");
		$pass =md5($password);
		$stmt->bind_param("ssss", $firstname, $lastname, $username, $pass);
		if($stmt->execute()){
			$id = sha1(uniqid());
			$result2 = $conn->prepare("UPDATE KORISNICI SET TOKEN=? WHERE USERNAME=?");
			$result2->bind_param("ss",$id,$username);
			$result2->execute();
			$rarray['token'] = $id;
		}else{
			header('HTTP/1.1 400 Bad request');
			$rarray['error'] = "Database connection error";
		}
	} else{
		header('HTTP/1.1 400 Bad request');
		$rarray['error'] = json_encode($errors);
	}

	return json_encode($rarray);


}

function checkIfUserExists($username){
	global $conn;
	$result = $conn->prepare("SELECT * FROM KORISNICI WHERE username=?");
	$result->bind_param("s",$username);
	$result->execute();
	$result->store_result();
	$num_rows = $result->num_rows;
	if($num_rows > 0)
	{
		return true;
	}
	else{
		return false;
	}
}

function rezervisi($id_knjige){
	global $conn;
	$rarray = array();
	$user_id = 0;
	if(checkIfLoggedIn()){
		$token = $_SERVER['HTTP_TOKEN'];

		$result2 = $conn->query("SELECT * FROM KORISNICI WHERE TOKEN=".$token);
		while($row = $result2->fetch_assoc()) {
			$user_id = $row['ID'];
		}

		$stmt = $conn->prepare("INSERT INTO IZNAJMLJIVANJE_KNJIGE (KORISNICI_ID, KNJIGA_ID) VALUES (?, ?)");
		$stmt->bind_param("ii", $user_id, $id_knjige);
		if($stmt->execute()){
				$rarray['success'] = "ok";
		}else{
			$rarray['error'] = "Database connection error";
			header('HTTP/1.1 400 Bad request');
		}
	} else{
		$rarray['error'] = "Please log in";
		header('HTTP/1.1 401 Unauthorized');
		return json_encode($rarray);
	}
}

function addAutor($name, $surname){
	global $conn;
	$rarray = array();
	$errors = "";
	if(checkIfLoggedIn()){
		if(strlen($name) < 3){
			$errors .= "First name must have at least 3 characters\r\n";
		}
		if(strlen($surname) < 3){
			$errors .= "Last name must have at least 3 characters\r\n";
		}
		if($errors == ""){
				$stmt = $conn->prepare("INSERT INTO AUTOR (NAME, SURNAME) VALUES (?, ?)");
				$stmt->bind_param("ss", $name, $surname);
				if($stmt->execute()){
					$rarray['success'] = "ok";
				}else{
					$rarray['error'] = "Database connection error";
				}
				return json_encode($rarray);
		} else{
			header('HTTP/1.1 400 Bad request');
			$rarray['error'] = json_encode($errors);
			return json_encode($rarray);
		}
	} else{
		$rarray['error'] = "Please log in";
		header('HTTP/1.1 401 Unauthorized');
		return json_encode($rarray);
	}
}

function addKnjiga($autor_id, $name, $isbn){
	global $conn;
	$rarray = array();
	$errors = "";
	if(checkIfLoggedIn()){
		if(strlen($name) < 3){
			$errors .= "Name must have at least 3 characters\r\n";
		}
		if(strlen($isbn) < 3){
			$errors .= "ISBN must have at least 3 characters\r\n";
		}
		if(!isset($autor_id)){
			$errors .= "You need to set author of a book\r\n";
		}
		if($errors == ""){
			$stmt = $conn->prepare("INSERT INTO KNJIGA (AUTOR_ID, NAME, ISBN) VALUES (?, ?, ?)");
			$stmt->bind_param("iss", $autor_id, $name, $isbn);
			if($stmt->execute()){
				$rarray['success'] = "ok";
			}else{
				$rarray['error'] = "Database connection error";
			}
			return json_encode($rarray);
		} else{
			header('HTTP/1.1 400 Bad request');
			$rarray['error'] = json_encode($errors);
			return json_encode($rarray);
		}
	} else{
		$rarray['error'] = "Please log in";
		header('HTTP/1.1 401 Unauthorized');
		return json_encode($rarray);
	}
}

function getKnjige(){
	global $conn;
	$rarray = array();
	if(checkIfLoggedIn()){
		$result = $conn->query("SELECT * FROM KNJIGA");
		$num_rows = $result->num_rows;
		$books = array();
		if($num_rows > 0)
		{
			$result2 = $conn->query("SELECT * FROM KNJIGA");
			while($row = $result2->fetch_assoc()) {
				$row['AUTOR_NAME'] = getAuthorsById($row['AUTOR_ID']);
				array_push($books,$row);
			}
		}
		$rarray['books'] = $books;
		return json_encode($rarray);
	} else{
		$rarray['error'] = "Please log in";
		header('HTTP/1.1 401 Unauthorized');
		return json_encode($rarray);
	}
}

function getAuthors(){
	global $conn;
	$rarray = array();
	if(checkIfLoggedIn()){
		$result = $conn->query("SELECT * FROM AUTOR");
		$num_rows = $result->num_rows;
		$authors = array();
		if($num_rows > 0)
		{
			$result2 = $conn->query("SELECT * FROM AUTOR");
			while($row = $result2->fetch_assoc()) {
				array_push($authors,$row);
			}
		}
		$rarray['authors'] = $authors;
		return json_encode($rarray);
	} else{
		$rarray['error'] = "Please log in";
		header('HTTP/1.1 401 Unauthorized');
		return json_encode($rarray);
	}
}
function deleteBook($id){
	global $conn;
	$rarray = array();
	if(checkIfLoggedIn()){
		if (getKorisnikByIme()){
			$result = $conn->prepare("DELETE FROM knjiga WHERE ID=?");
			$result->bind_param("i",$id);
			$result->execute();
			$rarray['success'] = "Deleted successfully";
		}

	} else{
		$rarray['error'] = "Please log in";
		header('HTTP/1.1 401 Unauthorized');
	}
	return json_encode($rarray);
}
function getAuthorsById($id){
	global $conn;
	$rarray = array();
	$id = intval($id);
	$result = $conn->query("SELECT * FROM AUTOR WHERE ID=".$id);
	$num_rows = $result->num_rows;
	$rowtoreturn = array();
	if($num_rows > 0)
	{
		$result2 = $conn->query("SELECT * FROM AUTOR WHERE ID=".$id);
		while($row = $result2->fetch_assoc()) {
			$rowtoreturn = $row;
		}
	}
	return $rowtoreturn['NAME']." ".$rowtoreturn['SURNAME'];
}

function checkKorisnikById() {
	global $conn;
	$token = $_SERVER['HTTP_TOKEN'];
	$result = $conn->query("SELECT * FROM KORISNICI WHERE TOKEN='".$token."'  ");
	$num_rows = $result->num_rows;
	if($num_rows === 1){
		$row = $result->fetch_assoc();
		if($row['ID'] === 1) {
			return true;
		}else {
			return false;
		}
	}
}

function getKorisnikByToken() {
	global $conn;
	$token = $_SERVER['HTTP_TOKEN'];
	$result = $conn->query("SELECT * FROM KORISNICI WHERE TOKEN='".$token."'");
	// $result->bind_param("s",$token);
		$num_rows = $result->num_rows;

		if($num_rows === 1 ) {
			$row = $result->fetch_assoc();
		if ($row['ID'] == 1 ){
			return true;
		}else {
			return false;
		}
		}
}
function addPorudzbina($knjiga_id) {

		global $conn;
		$rarray = array();
		// $DATUM = date('y-m-d H:i:s');
		if(checkIfLoggedIn()){
			$korisnikId = getKorisnikById();
			$stmt = $conn->prepare("INSERT INTO `iznajmljivanje_knjige` (`KORISNICI_ID`, `KNJIGA_ID`)
			VALUES (?, ?)");
			$stmt -> bind_param("ii",$korisnikId,$knjiga_id);

			if($stmt -> execute()){
				$rarray['success'] = "ok";

			}else {
				$rarray['error'] = "Database connection error";

			}

			return json_encode($rarray);

		} else{
			$rarray['error'] = "Please log in";
			header('HTTP/1.1 401 Unauthorized');
			return json_encode($rarray);
		}


}
function getKorisnikById() {

	global $conn;
	$token = $_SERVER['HTTP_TOKEN'];
	$result = $conn->query("SELECT * FROM KORISNICI WHERE TOKEN='".$token."'");
	// $result->bind_param("s",$token);
		$num_rows = $result->num_rows;

		if($num_rows === 1 ) {
			$row = $result->fetch_assoc();
			return $row['ID'];
		}


}

function getKorisnikByIme() {
	global $conn;
	$token = $_SERVER['HTTP_TOKEN'];
	$result = $conn->query("SELECT * FROM KORISNICI WHERE TOKEN='".$token."'");
	// $result->bind_param("s",$token);
		$num_rows = $result->num_rows;

		if($num_rows === 1 ) {
			$row = $result->fetch_assoc();
		if ($row['USERNAME'] == 'admin' ){
			return true;
		}else {
			return false;
		}
		}
}

function getPorudzbine() {
	global $conn;
	$rarray = array();

	if(checkIfLoggedIn()) {
		$korisnikId = getKorisnikById();
		$result = $conn->query("SELECT * FROM iznajmljivanje_knjige WHERE KORISNICI_ID=".$korisnikId);
		$num_rows = $result->num_rows;
		$porudzbina = array();
		if($num_rows > 0 ) {

while ($row = $result->fetch_assoc()) {
	$row['NAME'] = getBookById($row['KNJIGA_ID']);
	array_push($porudzbina,$row);
		}

		$rarray['porudzbina'] = $porudzbina;
		return json_encode($rarray);

	}
} else{
		$rarray['error'] = "Please log in";
		header('HTTP/1.1 401 Unauthorized');
		return json_encode($rarray);
	}
}

function getBookById($knjiga_id) {
	global $conn;
	$rarray = array();
$knjiga_id = intval($knjiga_id);
$result = $conn->query("SELECT * from knjiga WHERE ID=".$knjiga_id);

$num_rows = $result->num_rows;
if($num_rows > 0){
	while ($row = $result->fetch_assoc()) {
			$rarray = $row;
	}
}

	return $rarray['NAME'];

}

function deletePorudzbina($id) {
		global $conn;
		$rarray = array();
		if (checkIfLoggedIn()) {

				$result = $conn->prepare("DELETE FROM iznajmljivanje_knjige WHERE ID=?");
				$result-> bind_param("i", $id);
				$result->execute();

				$rarray['success'] = "ok";

		} else {
				$rarray['error'] = "Please log in";
				header('HTTP/1.1 401 Unauthorized');

			}

			return json_encode($rarray);
	}

?>
