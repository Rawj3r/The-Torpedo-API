<?php
	require 'connect.php';
	require 'test.php';
	require 'con.php';
	Class Progress{
		function updateProgress(){
			if (!empty($_GET)) {

				global $db;
				global $mysqli;

				if (isset($_GET['Progress_Time'])) {
					$userTimer = $_GET['Progress_Time'];
				}

				if (isset($_GET['stage'])) {
					$stage = $_GET['stage'];
				}

				if (isset($_GET['username'])) {
					# code...
					$username = $_GET['username'];
				}

				# code...
				$mysqli->query("SET @uname = " . "'" . $mysqli->real_escape_string($username) . "'");
				//we get the user id of the logged in user, we parse in his username as a parameter to our stored procedure
				$result = $mysqli->query("CALL getId(@uname)");
				// we check if we do not or have any positive data within our result varibale
				if(!$result) die("CALL failed: (" . $mysqli->errno . ") " . $mysqli->error);

				//check if number of rows the query returned is actually greater than 0
				if ($result->num_rows>0) {
					//Fetch our result row as an associative array
					while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
						// we finally get the id of the logged in user
						$id = $row["User_ID"];
					}
				//echo(json_encode($id));
				}

				$result->close();
				$mysqli->next_result();

				$result = $mysqli->query("SELECT Stage_ID FROM TDA_Torpedokid.User_Progress WHERE User_ID =  $id");
				if(!$result) die("GET STAGE ID FAILED: (" . $mysqli->errno . ") " . $mysqli->error);
				if ($result->num_rows>0) {
					# code...
						while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
							$loggedInStageID = $row["Stage_ID"];
						}
					//echo(json_encode($id));
				}
				
				$result = $db->query("SELECT * FROM TDA_Torpedokid.User_Progress WHERE User_ID =  $id AND Stage_ID =  $loggedInStageID");
				if (!$result) {
					$query = "INSERT INTO User_Progress (Progress_Time , Stage_ID, User_ID) VALUES ('$userTimer', '$stage', '$id')";
					$query_params = array();
					try {
						$stmt = $db -> prepare($query);
						$result = $stmt->execute($query_params);
					} catch (PDOException $ex) {
						die($ex->getMessage());
					}
				}

				if ($result)
					$query = "UPDATE User_Progress SET Progress_Time ='$userTimer', Stage_ID='$stage' WHERE User_ID = '$id' AND Progress_ID <> 0 AND Stage_ID =  $loggedInStageID";
					$query_params = array();
					//running our query and updating our row bases on the logged in user
					try{
						$stmt   = $db->prepare($query);
			            $result = $stmt->execute($query_params);
					}catch(PDOException $e){
						die("Failed to run query: " . $e->getMessage());
				}
			}else{
				?>
				<form action="upload.php" method="get">
					<input type="text" name="Progress_Time" placeholder="Progress time" /><br>
					<input type="text" name="stage" placeholder="Stage ID" /><br>
					<input type="text" name="username" placeholder="Username" /><br>
					<input type="submit" value="Submit" />
				</form>
				<?php
			}
		}
	}

$init = new Progress;
$init->updateProgress();
?>