<?php
class DBConnection{
	
	private $mDbHost = "localhost";
	private $mDbUser = "root";
	private $mDbPass = "NewSqlPassw0rd98";
	private $mDbName = "gamerota_Game";
	private $mConn;
	
	public function __construct(){
	    
	}
	
	private function connectToDatabase(){
		$this->mConn = new mysqli($this->mDbHost, $this->mDbUser, $this->mDbPass, $this->mDbName) or die("Connect failed: %s\n". $this->mConn -> error);
	}
	
	public function closeConnection(){
		//close connection to database
		if($this->mConn != null){
			$this->mConn->close();
			$this->mConn = null;
		}
	}
	
	public function login($username, $password){
		
		$this->connectToDatabase();
		
		//prevent SQL injections
		$username = mysqli_real_escape_string($this->mConn, $username);
		$password = mysqli_real_escape_string($this->mConn, $password);
		
		//select all from employee with username and password entered
		$statment = $this->mConn->prepare("SELECT * FROM tblEmployee WHERE Username = ?");
		$statment->bind_param("s", $username);
		$statment->execute();
		$result = $statment->get_result();
		
		//invalid query
		if (!$result) {
			trigger_error('Invalid query: ' . $this->mConn->error);
		}
		
		if($result->num_rows > 0){
			while($row=$result->fetch_assoc()){
			    if($row["FailedAttempts"] < 3){
    				$id = $row["EmployeeId"];
    				//check the hashed password is the same as password produced
    				$salt = $row["Salt"];
    				$localEncryptedPassword = hash("SHA256", $password.$salt, false);
    				
    				if($localEncryptedPassword == $row["Password"]){
    					//create the session for the user
    					$_SESSION["Username"] = $row["Username"];
    					$_SESSION["Firstname"] = $row["FirstName"];
    					$_SESSION["Id"] = $id;
    					
    					$statment = $this->mConn->prepare("UPDATE tblEmployee SET FailedAttempts = 0 WHERE EmployeeId = ?");
    					$statment->bind_param("i", $id);
    					$statment->execute();
    					
    					$statment->close();
    					$this->closeConnection();
    					return 1;
    					//username and password has been found, user can login
    				}else{//correct username but not correct password
    				    //increment the failed login attempts
    				    $statment = $this->mConn->prepare("UPDATE tblEmployee SET FailedAttempts = FailedAttempts + 1 WHERE EmployeeId = ?");
    				    $statment->bind_param("i", $id);
    				    $statment->execute();
    				    
    				    $statment->close();
    		            $this->closeConnection();
    		            if($row["FailedAttempts"] == 2){
    		                return -2;
    		            }
    				    return 0;
    				}
			    }else{
			        //too many failed attempts, the user has been locked out
			        $statment->close();
			        $this->closeConnection();
    				return -2;
			    }
			}
		}else{
		    $statment->close();
		    $this->closeConnection();
			return -1;
			//no username and password has been found, incorrect information
		}
	}
	
	public function getCalendar($month, $year){
		
		$this->connectToDatabase();
		
		$id = $_SESSION["Id"];
		
		$work_days = array();
		$work_day;
		
		//prevent SQL injections
		$month = mysqli_real_escape_string($this->mConn, $month);
		$year = mysqli_real_escape_string($this->mConn, $year);

		//create query to get work days only from current month
		$date_query = $year."-_".$month."-%";
		$statment = $this->mConn->prepare("SELECT WorkHoursId, StartTime, FinishTime, RotaStartTime, RotaFinishTime, StoreId, BreakTime, Date FROM tblWorkHours WHERE EmployeeId = ? AND Date LIKE ? ORDER BY Date");
		$statment->bind_param("is", $id, $date_query);
		$statment->execute();
		$result = $statment->get_result();
		
		$statment->close();
		$this->closeConnection();
		
		//invalid query
		if (!$result) {
			trigger_error('Invalid query: ' . $this->mConn->error);
		}
		
		//crate the days in which employees will be working
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				extract($row);
				$work_day = new WorkDay($WorkHoursId, $Date, $StartTime, $FinishTime, $RotaStartTime, $RotaFinishTime, $BreakTime, $StoreId);
				array_push($work_days, $work_day);
			}
		}
		
		return $work_days;
	}
	
	public function GetHolidays($month, $year){
		$this->connectToDatabase();
		
		$id = $_SESSION["Id"];
		
		$holidays = array();
		$holiday;
		
		$month = mysqli_real_escape_string($this->mConn, $month);
		$year = mysqli_real_escape_string($this->mConn, $year);
		
		$date_query = $year."-_".$month."-%";
		$statment = $this->mConn->prepare("SELECT * FROM tblHoliday WHERE EmployeeId = ? AND Date LIKE ? ORDER BY Date");
		$statment->bind_param("is", $id, $date_query);
		$statment->execute();
		$result = $statment->get_result();
		
		$statment->close();
		$this->closeConnection();
		
		//error
		if (!$result) {
			trigger_error('Invalid query: ' . $this->mConn->error);
		}
		
		//crate the days in which employees will be working
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				extract($row);
				$holiday = new Holiday($HolidayId, $Date, $State);
				array_push($holidays, $holiday);
			}
		}
		
		return $holidays;
	}
	
	public function BookHoliday($holidayDays){
		$this->connectToDatabase();
		$errorMessage = "";
		
		$id = $_SESSION["Id"];
		
		$workHoursStatment = $this->mConn->prepare("SELECT * FROM tblWorkHours WHERE EmployeeId = ? AND DATE = ?");
		$holidayStatment = $this->mConn->prepare("SELECT * FROM tblHoliday WHERE EmployeeId = ? AND Date = ?");
		$insertHolidayStatment = $this->mConn->prepare("INSERT INTO tblHoliday (EmployeeId, Date, State) VALUES (?, ?, 'Pending')");
		
		//get each holiday the employee has booked and insert into the database
		foreach($holidayDays as $holiday){
		    $workHoursStatment->bind_param("is", $id, $holiday);
		    $workHoursStatment->execute();
		    $workHoursResult = $workHoursStatment->get_result();
		    
		    //check the user isn't already scheduled working on that day
		    if($workHoursResult->num_rows <= 0){
		        $holidayStatment->bind_param("is", $id, $holiday);
		        $holidayStatment->execute();
    			$holidayResult = $holidayStatment->get_result();
    			
    			//check the date hasn't already been booked by the user
    			if($holidayResult->num_rows <= 0){
    				$insertHolidayStatment->bind_param("is", $id, $holiday);
    				$result = $insertHolidayStatment->execute();
    			}else{
    			    $errorMessage .= "Holiday already booked on " . $holiday . "<br>";
                }
		    }else{
		        $errorMessage .= "Scheduled to work on " . $holiday . "<br>";
		    }
		}
		
		$workHoursStatment->close();
		$holidayStatment->close();
		$insertHolidayStatment->close();
		$this->closeConnection();
		return $errorMessage;
	}
	
	public function DoesEmailExist($email){
		$this->connectToDatabase();
		
		$date = date("Y-m-d");
		
		$email = mysqli_real_escape_string($this->mConn, $email);
		
		$employeeStatment = $this->mConn->prepare("SELECT FirstName FROM tblEmployee WHERE Email = ?");
		$employeeStatment->bind_param("s", $email);
		$employeeStatment->execute();
		$result = $employeeStatment->get_result();
		
		if(!$result){
			trigger_error("invalid query: " . $this->mConn->error);
		}
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
			    $name = $row["FirstName"];
			}
		    //if a reset key is already valid, send the same one again
			$resetPasswordStatment = $this->mConn->prepare("SELECT Email, ResetKey FROM tblResetPassword WHERE Email = ? AND Valid = 1 AND ExpiryDate = ?");
			$resetPasswordStatment->bind_param("ss", $email, $date);
			$resetPasswordStatment->execute();
			$result = $resetPasswordStatment->get_result();
			$resetPasswordStatment->close();
			
			if(!$result){
				trigger_error("query error: " . $this->mConn->error);
			}
			
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					extract($row);
					$key=$ResetKey;
				}
			}else{
				$key = bin2hex(random_bytes(16));
				$resetPasswordInsert = $this->mConn->prepare("INSERT INTO tblResetPassword (ResetKey, ExpiryDate, Valid, Email) VALUES (?, ?, 1, ?)");
				$resetPasswordInsert->bind_param("sss", $key, $date, $email);
				$resetPasswordInsert->execute();
				$resetPasswordInsert->close();
			}
			
			//Send email to employee from server email
			$link = "https://www.gamerotasystem.com/resetpassword?key=".$key."&email=".$email;
			
			$this->SendEmail($email, $name, $link);
			
			$this->closeConnection();
			return true;
		}else{
			$this->closeConnection();
			return false;
		}
	}
	
	public function CheckPasswordResetData($email, $key){
		$this->connectToDatabase();
		
		$date = date("Y-m-d");
		
		$email = mysqli_real_escape_string($this->mConn, $email);
		$key = mysqli_real_escape_string($this->mConn, $key);
		
		$selectStatment = $this->mConn->prepare("SELECT * FROM tblResetPassword WHERE ResetKey = ? AND Email = ? AND Valid = 1 AND ExpiryDate = ?");
		$selectStatment->bind_param("sss", $key, $email, $date);
		$selectStatment->execute();
		$result = $selectStatment->get_result();
		
		if(!$result){
			trigger_error("invalid query: " . $this->mConn->error);
		}
		
		if($result->num_rows > 0){
			return true;
		}else{
			return false;
		}
	}
	
	public function ResetPassword($email, $newPassword, $key){
		$this->connectToDatabase();
		
		$email = mysqli_real_escape_string($this->mConn, $email);
		$key = mysqli_real_escape_string($this->mConn, $key);
		$newPassword = mysqli_real_escape_string($this->mConn, $newPassword);
		
		if($this->CheckPasswordResetData($email, $key)){
			$updateStatment = $this->mConn->prepare("UPDATE tblResetPassword SET Valid = 0 WHERE Email = ? AND ResetKey = ?");
			$updateStatment->bind_param("ss", $email, $key);
			$updateStatment->execute();
			
			$salt = bin2hex(random_bytes(32));
			$newPasswordEnc = hash("SHA256", $newPassword.$salt, false);
			
			$updateStatment2 = $this->mConn->prepare("UPDATE tblEmployee SET Salt = ?, Password = ?, FailedAttempts = 0 WHERE Email = ?");
			$updateStatment2->bind_param("sss", $salt, $newPasswordEnc, $email);
			$updateStatment2->execute();
			
			return true;
		}else{
			return false;
		}
	}
	
	public function SendEmail($email, $name, $link){
		$body = "<html><body><h1>Password Reset</h1>"
		. "<p> Hello " . $name . ", </p>"
		. "<p> You have requested a password reset. </p>"
		. "<p> Please follow this link to reset your password </p>"
		. "<p> " . $link . " </p>"
		. "<p> Thank you, </p>"
		. "<p> Game </p></html></body>";
		
		$headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$headers .= "From: rotanotification@gamerotasystem.com\r\n";
		$headers .= "X-Mailer: PHP/" . phpversion();
		
		$to = $email;
		$subject = "Password Reset";

		mail($to,$subject,$body,$headers);
	}
	
	public function ClockInEmployee($clockInNo, $storeId, $clockIn){
	    $this->connectToDatabase();
	    
	    $clockInNo = mysqli_real_escape_string($this->mConn, $clockInNo);
	    $storeId = mysqli_real_escape_string($this->mConn, $storeId);
	    $clockIn = mysqli_real_escape_string($this->mConn, $clockIn);
	    
	    //get the employee's id based on the storeId and clockInNumber
	    $selectStatment = $this->mConn->prepare("select tblEmployee.EmployeeId FROM tblEmployee JOIN tblEmployeeStore ON tblEmployeeStore.EmployeeId = tblEmployee.EmployeeId JOIN tblStore ON tblStore.StoreId = tblEmployeeStore.StoreId WHERE tblStore.StoreId = ? AND tblEmployee.ClockInNo = ?");
	    $selectStatment->bind_param("is", $storeId, $clockInNo);
	    $selectStatment->execute();
	    
	    //$query = "SELECT Id FROM tblEmployee WHERE ClockInNo = '$clockInNo' AND StoreId = '$storeId'";
	    $result = $selectStatment->get_result();
	    
	    if($result->num_rows > 0){
	        $row = $result->fetch_assoc();
	        $id = $row["EmployeeId"];
	        
	        $date = date("Y-m-d");
	        
	        //see if the employee has already been rotad in to work that day
	        $selectStatment = $this->mConn->prepare("SELECT WorkHoursId FROM tblWorkHours WHERE EmployeeId = ? AND Date = ?");
	        $selectStatment->bind_param("is", $id, $date);
	        $selectStatment->execute();
	        $result = $selectStatment->get_result();
	        
	        $time = new DateTime("now", new DateTimeZone('Europe/London'));
            $time = $time->format('H:i:s');
			
	        if($result->num_rows > 0){
	            //the employee has been rotered in, so just update
	            $row = $result->fetch_assoc();
	            $workHoursId = $row["WorkHoursId"];
	            
	            if($clockIn == "True"){//clockIn == true when user is clocking in
	                $updateWorkHoursStatment = $this->mConn->prepare("UPDATE tblWorkHours SET StartTime = ?, StoreId = ? WHERE WorkHoursId = ?");
	            }else{//clockIn == false when user is clocking out
	                $updateWorkHoursStatment = $this->mConn->prepare("UPDATE tblWorkHours SET FinishTime = ?, StoreId = ? WHERE WorkHoursId = ?");
	            }
	            $updateWorkHoursStatment->bind_param("sss", $time, $storeId, $workHoursId);
	            $updateWorkHoursStatment->execute();
	            
	            return true;
	            
	        }else{
	            //the employee has not been rotered in, so insert into the database
	            if($clockIn == "True"){
	                $insertWorkHoursStatment = $this->mConn->prepare("INSERT INTO tblWorkHours (EmployeeId, StoreId, StartTime, FinishTime, RotaStartTime, RotaFinishTime, BreakTime, Date) VALUES (?, ?, ?, '00:00:00', '00:00:00', '00:00:00', '00:00:00', ?)");
	            }else{
	                $insertWorkHoursStatment = $this->mConn->prepare("INSERT INTO tblWorkHours (EmployeeId, StoreId, StartTime, FinishTime, RotaStartTime, RotaFinishTime, BreakTime, Date) VALUES (?, ?, '00:00:00', ?, '00:00:00', '00:00:00', '00:00:00', ?)");
	            }
	            $insertWorkHoursStatment->bind_param("ssss", $id, $storeId, $time, $date);
	            $insertWorkHoursStatment->execute();
	            
	            return true;
	        }
	    }else{
	        //the employee could not be found, incorrect id
	        return false;
	    }
	}
}

class WorkDay{
	public $mid;
	public $mdate;
	public $mstore;
	public $mstart_time;
	public $mfinish_time;
	public $mrota_start_time;
	public $mrota_finish_time;
	public $mbreak_time;
	
	function __construct($id, $date, $start_time, $finish_time, $rota_start_time, $rota_finish_time, $break_time, $store){
		$date = date("Y-m-d", strtotime($date));
		$date = date("d-m-Y", strtotime($date));
		
		$this->mid = $id;
		$this->mstore = strval($store);
		$this->mdate = $date;
		$this->mstart_time = $start_time;
		$this->mfinish_time = $finish_time;
		$this->mrota_start_time = $rota_start_time;
		$this->mrota_finish_time = $rota_finish_time;
		$this->mbreak_time = $break_time;
	}
}

class Holiday{
	public $mid;
	public $mdate;
	public $mstate;
	
	function __construct($id, $date, $state){
		$date = date("Y-m-d", strtotime($date));
		$date = date("d-m-Y", strtotime($date));
		
		$this->mid = $id;
		$this->mdate = $date;
		$this->mstate = $state;
	}
}
?>