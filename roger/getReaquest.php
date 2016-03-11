<?php
require 'connect.php';
require 'test.php';
require 'con.php';
Class getRequest{
	public function getRequest(){
		global $mysqli;
		global $db;
		if (!empty($_GET)){
			//get logged in username
			if (isset($_GET['username'])) {
				//get th posted data and assign it to the variable name $username
				$loggedInUsername = $_GET['username'];
			}

			$query = "SELECT * FROM request WHERE UserNameRec = '$loggedInUsername' AND _status = '0' ";
			$query_params = array();

			try {
			$stmt   = $db->prepare($query);
			$result = $stmt->execute($query_params);
			} catch (PDOException $e) {
				$response["success"] = 0;
			    $response["message"] = "Database Error!";
			    die(json_encode($response));
			    //die($e->getMessage());
			}

			// Finally, we can retrieve all of the found rows into an array using fetchAll 
			$rows = $stmt->fetchAll();

			if ($rows) {
				# code...
				$response["success"] = 1;
				$response["message"] = "You have pending request!";
		    	$response["getReq"]   = array();

		    	foreach ($$rows as $row) {
		    		# code...
		    		$pendingr = array();
		    		$pendingr["UserNameSend"] = $row["UserNameSend"];
		    		$pendingr["amount"] = $row["amount"];

		    		array_push($response["getReq"], $pendingr);
		    	}
		    	echo(json_encode($pendingr));
			}
		}
	}
}
$init = new getRequest;
$init->getRequest();
?>