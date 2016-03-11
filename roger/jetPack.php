<?php
require 'connect.php';
require 'test.php';
require 'con.php';

Class JetPack{
	public function getJet(){
		global $db;
		$response = array();

		if (isset($_GET["username"])) {
			# code...
			$username = $_GET["username"];
		}

		if (!empty($user)) {

		$mysqli->query("SET @uname = " . "'" . $mysqli->real_escape_string($username) . "'");
		$result = $mysqli->query("CALL getId(@uname)");
		if(!$result) die("CALL failed: (" . $mysqli->errno . ") " . $mysqli->error);

		if ($result->num_rows>0) {
			# code...
			while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
				$id = $row["User_ID"];
			}
			//echo(json_encode($id));
		}
			try {
				$query = "SELECT * FROM TDA_Torpedokid.Gadget_Owned WHERE User_ID = $id AND  Gadget_ID = 4";
		 
				try {
					$stmt = $db->prepare($query);
					$result = $stmt->execute($query_params);
				}catch(Exception $e){
					die("Failed to run query: " . $e->getMessage());
				}
				// Finally, we can retrieve all of the found rows into an array using fetchAll 
				$rows = $stmt->fetchAll();

				if ($rows) {
				 	$response["success"] = 1;
					$response["message"] = "Success!";
			    	$response["jet"]   = array();

			    	foreach ($rows as $row){
			    		$gadget["gadget_jet"] = $row["Gadgets_Count"];

			    		array_push($response["jet"], $gadget);
			    	}
			    	echo(json_encode($response));
				 } 	
				 die();			
			} catch (Exception $e) {
				
			}
		}
	}
}
?>