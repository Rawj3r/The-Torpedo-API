<?php
require 'connect.php';
require 'test.php';
require 'con.php';
Class Request{
	public function getRequest(){
		global $mysqli;
		global $db;
		$loggedInUsername = '';
		if (!empty($_GET)){
			//get logged in username

			if (isset($_GET['username'])) {
				//get th posted data and assign it to the variable name $username
				$loggedInUsername = $_GET['username'];
			}

			// run a wuery to get all requests from the request table based on the logged in username and where status value is 0
			// this time the logged in username is the user which receives the request executed in request.php

			$query = "SELECT * FROM request WHERE UserNameRec = '$loggedInUsername' AND _status = '0' ";
			$query_params = array();

			try {
				// we prepare our $query for execution and returns a $query object
		 	$stmt   = $db->prepare($query);
		 	// we execute our prepares statement
			$result = $stmt->execute($query_params);
			// catch any PDOExceptions which may occur
			} catch (PDOException $e) {
				//$response["success"] = 0;
			    //$response["message"] = "Database Error!";
			    //die(json_encode($response));
			    die($e->getMessage());
			}

			// Finally, we can retrieve all of the found rows into an array using fetchAll 
			$rows = $stmt->fetchAll();

			// we check if the returned resut is true
			if ($rows) {
				# code...
				$response["success"] = 1;
				$response["message"] = "You have pending request!";
		    	$response["getReq"]   = array();

		    	foreach ($rows as $row) {
		    		# code...
		    		$pendingr = array();
		    		$pendingr["UserNameSend"] = $row["UserNameSend"];
		    		$pendingr["amount"] = $row["amount"];

		    		//update our array data
		    		array_push($response["getReq"], $pendingr);
		    	}
		    	echo(json_encode($response));
			}
		}else{
			require'getrequestform.php';
		}
	}
}
$init = new Request;
$init->getRequest();
?>