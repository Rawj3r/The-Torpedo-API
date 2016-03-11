<?php
require 'connect.php';
require 'test.php';
require 'con.php';
Class Update{
	public function updateRequest(){
			global $db;
			global $mysqli;
			//get logged in username

			$amountr = '';

			if (isset($_GET['username'])) {
				//get the logged in username
				$loggedInUsername = $_GET['username'];
			}

			if (isset($_GET['sender'])) {
				// get the request maker username
				$sender = $_GET['sender'];
			}

			if (isset($_GET['amountRequest'])) {
				$amountr = $_GET['amountRequest']; 
			}

			$sender = explode("}", $sender);
			$sender = $sender[0];

			$amount = explode(",", $amountr);
			$amount = $amount[0];

			if (isset($_GET['status_'])) {
				// get our status which will be dynamic as well
				// if status == 1, request rejected, if status == 2 request accepted
				// else status cannot be changed
				// if a request have not been responded to it's status will be 0
				$type_ = $_GET['status_'];
			}

			// check if the following varibales are not empty as they are all essential for the updating
			// of the request
			if (!empty($sender) && !empty($loggedInUsername) && !empty($type_ )) {
				// and then there was our update query the only thing we have to update in this query is the status
				// the status must change in based on what value our $
				$query = "UPDATE request SET _status = '$type_' WHERE UserNameRec = '$loggedInUsername' AND UserNameSend='$sender' AND id <> 0 ";
				//UPDATE request SET _status = '2' WHERE UserNameRec = 'sei' AND UserNameSend='jobs' AND id <>0
				$query_params = array();

				//running our query and updating our row bases on the logged in user
				try{
					$stmt   = $db->prepare($query);
		            $result = $stmt->execute($query_params);
				}catch(PDOException $e){
					die("Failed to run query: " . $ex->getMessage());
				}
				if ($type_ == 1) {
					# code...
					$response["success"] = 1;
			        $response["message"] = "Request Rejected!";
			        echo json_encode($response);
				}
				if ($type_ == 2) {

					$mysqli->query("SET @uname = " . "'" . $mysqli->real_escape_string($loggedInUsername) . "'");
					$result = $mysqli->query("CALL getId(@uname)");
					if(!$result) die("GET LOGGED IN ID FAILED: (" . $mysqli->errno . ") " . $mysqli->error);

					if ($result->num_rows>0) {
						# code...
						while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
							$loggedInId = $row["User_ID"];
						}
						//echo(json_encode($id));
					}

					$result->close();
					$mysqli->next_result();

					$mysqli->query("SET @uname = " . "'" . $mysqli->real_escape_string($sender) . "'");
					$result = $mysqli->query("CALL getId(@uname)");
					if(!$result) die("GET SENDER ID FAILED: (" . $mysqli->errno . ") " . $mysqli->error);

					if ($result->num_rows>0) {
						# code...
						while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
							$senderId = $row["User_ID"];
						}
						//echo(json_encode($id));
					}

					$result->close();
					$mysqli->next_result();

					$mysqli->query("SET @id = " . "'" . $mysqli->real_escape_string($loggedInId) . "'");
					$result = $mysqli->query("CALL prGet_Bank_Data(@id)");
					if(!$result) die("GET LOGGED IN BANK DATA FAILED: (" . $mysqli->errno . ") " . $mysqli->error);

					if ($result->num_rows>0) {
						# code...
						while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
							# code...
							$loggedInAccNum = $row["Account_Num"];
							$loggedInBalance = $row["Balance"];
						}
					}

					$loggedInBalance -= $amountr;

					$result->close();
					$mysqli->next_result();


					$mysqli->query("SET @id = " . "'" . $mysqli->real_escape_string($senderId) . "'");
					$result = $mysqli->query("CALL prGet_Bank_Data(@id)");
					if(!$result) die("GET SENDER BANK DATA SUCCESS: (" . $mysqli->errno . ") " . $mysqli->error);

					if ($result->num_rows>0) {
						while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
							$senderAccNum = $row["Account_Num"];
							$senderBalance = $row["Balance"];
						}
					}

					$response["success"] = 2;
			        $response["message"] = "Request Accepted!";
			        echo json_encode($response);

					$result->close();
					$mysqli->next_result();

					try {
						$mysqli->query("SET @acc = " . "'" . $mysqli->real_escape_string($loggedInAccNum) . "'");
						$mysqli->query("SET @id = " . "'" . $mysqli->real_escape_string($loggedInId) . "'");
						$mysqli->query("SET @amnt = " . "'" . $mysqli->real_escape_string($loggedInBalance) . "'");
						$result = $mysqli->query("CALL prUpdate_Junior_Bank(@id, @acc, @amnt)");
						if(!$result) die("UPDATING LOGGED IN USER BANK ACCOUNT FAILED: (" . $mysqli->errno . ") " . $mysqli->error);
						if ($result) {
							# code...
							$response["success"] = 1;
							$response["message"] = "success!";
					    	$response["get"]   = array();
					    	echo json_encode($response);
						}

						$query="INSERT INTO TDA_Torpedokid.Transaction_(Account_Num, Description, Amount_, Category) VALUES ($loggedInAccNum, 'You lent $sender', $amount, 'loan')";

						$query_params = array();

						try {
					            $stmt   = $db->prepare($query);
					            $result = $stmt->execute($query_params);
					        }
					        catch (PDOException $ex) {
					            // For testing, we could use a die and message. 
					            die("Failed to run query: " . $ex->getMessage());
					            
					            //or just use this use this one:
					            //$response["success"] = 0;
					            //$response["message"] = "Database Error2. Please Try Again!";
					            //die(json_encode($response));
					        }

					    $query="INSERT INTO TDA_Torpedokid.Transaction_(Account_Num, Description, Amount_, Category) VALUES ($senderAccNum, 'You requested $loggedInUsername', $amount, 'loan')";    

					    $query_params = array();

						try {
					            $stmt   = $db->prepare($query);
					            $result = $stmt->execute($query_params);
					        }
					        catch (PDOException $ex) {
					            // For testing, we could use a die and message. 
					            die("Failed to run query: " . $ex->getMessage());
					            
					            //or just use this use this one:
					            //$response["success"] = 0;
					            //$response["message"] = "Database Error2. Please Try Again!";
					            //die(json_encode($response));
					        }

						}catch (Exception $e) {
							die($e->getMessage());
						}

					//$result->close();
					//Check if there are any more query results from a multi query
					//$mysqli -> mysqli_more_results();

					$senderBalance += $amountr;

					try {
						$mysqli->query("SET @acc = " . "'" . $mysqli->real_escape_string($senderAccNum) . "'");
						$mysqli->query("SET @id = " . "'" . $mysqli->real_escape_string($senderId) . "'");
						$mysqli->query("SET @amnt = " . "'" . $mysqli->real_escape_string($senderBalance) . "'");
						$result = $mysqli->query("CALL prUpdate_Junior_Bank(@id, @acc, @amnt)");
						if(!$result) die("UPDATING BANK DATA FOR SENDER FAILED: (" . $mysqli->errno . ") " . $mysqli->error);
						if ($result) {
							$response["success"] = 1;
							$response["message"] = "success!";
					    	$response["get"]   = array();
					    	echo(json_encode($response));
						}
					} catch (Exception $e) {
						
					}

			}else{
				# code...
					$response["success"] = 0;
			        $response["message"] = "failed cannot execute statements!";
			        echo json_encode($response);
			}
		}
	}
}
$init = new Update;
$init->updateRequest();
?>