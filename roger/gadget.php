<?php
require 'connect.php';
require 'test.php';
require 'con.php';

Class Gadget{

	function purchase(){
		global $mysqli;
        global $db;
        if (!empty($_GET)) {
        if (isset($_GET['username'])) {
        	$username = $_GET['username'];
        }

        if (isset($_GET['unit_'])) {
        	$unit_ = $_GET['unit_'];
        }

        if (isset($_GET['g_id'])) {
        	$g_id = $_GET['g_id'];
        }

        if (isset($_GET['amount_'])) {
            $g_amount = $_GET['amount_'];
        }

        $g_amount *= $unit_;

        	try {
        		$result = $mysqli->query("SELECT * FROM TDA_Torpedokid.Gadget");
        		if (!$result) die("Failed to get Gadgets");

        		if ($result->num_rows>0) {
        			# code...
        			while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        				# code...
        				$gadget_id = $row["Gadget_ID"];
        				$gadget_name = $row["Gadget_Name"];
        				$gadget_price = $row["Price_"];
        			}
        		}

        		$mysqli->query("SET @uname = " . "'" . $mysqli->real_escape_string($username) . "'");
                $result = $mysqli->query("SELECT * FROM User_ WHERE _UserName = '$username'");
                if(!$result) die("CALL failed: (" . $mysqli->errno . ") " . $mysqli->error);

                if ($result->num_rows>0) {
                    # code...
                    while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
                       $id = $row["User_ID"];
                    }   
                }

                $mysqli->query("SET @id = " . "'" . $mysqli->real_escape_string($id) . "'");
                $result = $mysqli->query("CALL prGet_Bank_Data(@id)");
                if(!$result) die("GET LOGGED IN BANK DATA FAILED: (" . $mysqli->errno . ") " . $mysqli->error);

                if ($result->num_rows>0) {
                    while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
                        $loggedInAccNum = $row["Account_Num"];
                        $loggedInBalance = $row["Balance"];
                    }
                } 

                if ($g_amount > $loggedInBalance) {
                    die(json_encode("Oops Sorry :) Insufficient funds"));
                         
                 }elseif ($loggedInBalance >= $g_amount) {
                    $loggedInBalance -= $g_amount;
                 }

                $result->close();
                $mysqli->next_result();

                $result=$mysqli->query("SELECT * FROM TDA_Torpedokid.Gadget_Owned WHERE User_ID = $id AND Gadget_ID = '$g_id'");
                if (!$result) {
                	$query = "INSERT INTO TDA_Torpedokid.Gadget_Owned(Gadget_ID, User_ID, Gadgets_Count) VALUES ('$g_id', '$id', '$unit_' )";
                	$query_params = array();

                	try{
                		$stmnt = $db->prepare($query);
                		$result = $stmnt->execute($query_params);

                        $mysqli->query("SET @acc = " . "'" . $mysqli->real_escape_string($loggedInAccNum) . "'");
                        $mysqli->query("SET @id = " . "'" . $mysqli->real_escape_string($id) . "'");
                        $mysqli->query("SET @amnt = " . "'" . $mysqli->real_escape_string($loggedInBalance) . "'");
                        $result = $mysqli->query("CALL prUpdate_Junior_Bank(@id, @acc, @amnt)");
                        if(!$result) die("INSERTINGT LOGGED IN USER BANK ACCOUNT FAILED: (" . $mysqli->error . ") " . $mysqli->error);
                        if ($result) {
                            # code...
                            $response["success"] = 1;
                            $response["message"] = "successfully purchased new gadget!";
                            $response["get"]   = array();
                            echo json_encode($response);
                        }
                	}catch(PDOException $ex){
                		die("Failed to run query: " . $ex->getMessage());
                	}
                }
                if ($result->num_rows>0) {
                	while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
                		$gagdet_owned_id = $row["ID"];
                		//$gadget_id = $row["Gadget_ID"];
						$gadget_count = $row["Gadgets_Count"];
                	}

                	//$unit_ += $gadget_count;

                    $finalUnit = $unit_ + $gadget_count;

                	$query = "UPDATE TDA_Torpedokid.Gadget_Owned SET Gadgets_Count =  $finalUnit WHERE User_ID = $id AND ID <> 0 AND Gadget_ID = '$g_id' ";
                	$query_params = array();

                	try{
                		$stmnt = $db->prepare($query);
                		$result = $stmnt->execute($query_params);

                        $mysqli->query("SET @acc = " . "'" . $mysqli->real_escape_string($loggedInAccNum) . "'");
                        $mysqli->query("SET @id = " . "'" . $mysqli->real_escape_string($id) . "'");
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


                        $query="INSERT INTO TDA_Torpedokid.Transaction_(Account_Num, Description, Amount_, Category) VALUES ($loggedInAccNum, 'Purchased $unit_ gadgets', $g_amount, 'gadget')";    

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

                	}catch(PDOException $xe){
                		die($xe->getMessage());
                	}
                }
        	} catch (Exception $e) {	
        }
    }else{
    	?>
    	<form action="gadget.php" method="get" >
    		<input type="text" name="username" placeholder="Username" />
    		<input type="text" name="unit_" placeholder="Unit" />
            <input type="text" name="amount_" placeholder="Amount" />
    		<input type="text" name="g_id" placeholder="Gadget Id" />
    		<input type="submit" value="Submit" />
    	</form>
    	<?php
    }
	}
}

$init = new Gadget;
$init->purchase();

?>