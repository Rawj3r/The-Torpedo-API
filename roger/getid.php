<?php
require 'connect.php';
require 'test.php';
require 'con.php';

Class GetId{
	public function getId(){
		global $mysqli;
		global $db;

		if (!empty($_GET)) {
			try{
				if (isset($_GET['username'])) {
					$loggedInUsername = $_GET['username'];
				}

				$query = "CALL getId('$loggedInUsername')";
				$query_params = array();

				try {
					$stmt   = $db->prepare($query);
					$result = $stmt->execute($query_params);
				} catch (Exception $e) {
					//$response["success"] = 0;
					//$response["message"] = "Database Error!";
					//die(json_encode($response));

					die("Failed to run query: " . $e->getMessage()); 
				}
				// Finally, we can retrieve all of the found rows into an array using fetchAll 
				$rows = $stmt->fetchAll();

				if ($rows){
					# code...
					$response["success"] = 1;
					$response["message"] = "Success!";
			    	$response["get"]   = array();

			    	foreach ($rows as $row){
			    		$id["user_id"] = $row["User_ID"];

			    		array_push($response["get"], $id);
			    	}
			    	echo(json_encode($response));
				}
				die();
			}catch(Exception $e){
				$response["failed"] = "0";
				die(json_encode($response));
			}
		}
	}
}
$init = new GetId;
$init->getId();
?>