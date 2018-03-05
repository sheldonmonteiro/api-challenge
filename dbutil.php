<?php	
	function connectDB() {
		$server = "localhost";
		$db = "shows";
		$username = "root";
		$password = "";
		try {
			$conn = new PDO("mysql:host=$server;dbname=$db", $username, $password);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch(PDOException $e) {
			$response = array(
				"status" =>  "error",
				"message" => "service temporarily unavailable"
			);
			print_r(json_encode($response));
			die();
		}
		return $conn;
	}
?>