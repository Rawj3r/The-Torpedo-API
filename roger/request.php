<?php
require 'connect.php';
require 'test.php';
require 'con.php';

Class Request{
	public function send(){
		global $mysqli;
		global $db;

		// we first check if our get request is empty, if not empty we run execute the rest of the code
		if (!empty($_GET)) {
			try {
				// we use the inbuilt php function isset() to check if our variable have been set or not
				if (isset($_GET['username'])) {
					# code...
					$username = $_GET['username'];
				}

				if (isset($_GET['fusername'])) {
					# code...
					$fusername = $_GET['fusername'];
				}

				if (isset($_GET['amnt'])) {
					# code...
					$amnt = $_GET['amnt'];
				}

				//in this section we are going to need the logged in username, the friend's username which is the friend to receive the request
				// and lastly the amount being requested
				//within out if condition we check is logged in username, friend username and amount are not empty 
				if (!empty($username) && !empty($fusername)  && !empty($amnt) ) {
				# code...
					$mysqli->query("SET @uname = " . "'" . $mysqli->real_escape_string($username) . "'");
					$result = $mysqli->query("CALL getId(@uname)");
					if(!$result) die("CALL failed: (" . $mysqli->errno . ") " . $mysqli->error);

					//check if number of rows the query returned is actually greater than 0
					if ($result->num_rows>0) {

						//Fetch our result row as an associative array
						while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
							//we finally get the id of the logged in username, because that is all we need in the Users table in this script
							$id = $row["User_ID"];
						}
							//check if we have successfully returned an id of a registerd user and the logged in user.
							// all that possible because of our username variable
							//echo(json_encode($id));
					}
				}
				// catch any PDOExceptions we may have, echo them out and kill the rest of the page
			} catch (PDOException $e) {
				die("Error occurred:" . $e->getMessage());
			}
			$result->close();
			$mysqli->next_result();

			// here we do the same exact same as above we get a user ID from the User table based on the username
			// well this time the ID we are getting is not of the logged in one, it is of the user to receive the borrow request.
			$mysqli->query("SET @uname = " . "'" . $mysqli->real_escape_string($fusername) . "'");
			$result = $mysqli->query("CALL getId(@uname)");
			if(!$result) die("CALL failed: (" . $mysqli->errno . ") " . $mysqli->error);

			//check if number of rows the query returned is actually greater than 0
			if ($result->num_rows>0) {
				//Fetch our result row as an associative array
				while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
					//we finally get the id of the user to receive the request username, because that is all we need in the Users table in this script
					$fid = $row["User_ID"];
				}
			}

			// we declare a variable; status, give it and initial value which we wont change in this script
			$status = 0;

			$query = "INSERT INTO request(UserNameRec, UserNameSend, _status, amount, UserIDSend, UserIDRec) VALUES ('$fusername', '$username', '$status', '$amnt', '$id', '$fid') ";
			$query_params = array();

			//time to run our query, and create the user
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
        
        // If we have reached this part, it means our two users needed to perfom this task have been successfully returned from the db
        // and the borrow request have successfully executed
        $response["success"] = 1;
        $response["message"] = "successfully sent";
        echo json_encode($response);
		}else{
			?>
			<form action="request.php" name="makeReq" method="get">
				<input type="text" name="username" placeholder="Username" ><br>
				<input type="text" name="fusername" placeholder="Friend's username" ><br>
				<input type="number" name="amnt" placeholder="amount" ><br>
				<input type="submit" name="sendReq" value="Submit" >

			</form>
			<?php
		}
	}
}

$init = new Request;
$init->send();
?>