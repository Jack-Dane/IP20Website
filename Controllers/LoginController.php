<?php
class LoginController{
	
	private $message;
	private $DBConnection;
	
	function __construct($databaseConnection){
		
	    $this->DBConnection = $databaseConnection;
		
		//check if the user is already logged in
		$this->alreadyLoggedIn();
		
		//see if the user has tried to login
		$this->tryLogin();
		
		//check for login error or updated password
		$this->checkCookies();
		
		require_once "Views/Login.php";
	}
	
	private function alreadyLoggedIn(){
		if(isset($_SESSION["Id"])){ 
			header("location:home");
			exit();
		}
	}
	
	private function tryLogin(){
		if(isset($_POST["g_username"])){
			extract($_POST);
			//create select, insert and delete function in database class and call when necessary
			
			$wrongPassword = $this->DBConnection->login($g_username, $g_password);
			
			if($wrongPassword == 1){
				header("location:home");
			}else if($wrongPassword == 0 || $wrongPassword == -1){
		        //go back to login and display error, enabling user to refresh
				setcookie("loginError", "The wrong Username or Password has been entered", time() + 86400, "/");
				header("location:login");
		    }else if($wrongPassword == -2){
		        setcookie("loginError", "Too many failed attempts, you need to reset your Password", time() + 86400, "/");
				header("location:login");
		    }
		}
	}
	
	private function checkCookies(){
	    if(isset($_COOKIE["loginError"])){
	        $this->message = $_COOKIE["loginError"];
	        setcookie("loginError", "", time() - 3600);
	    }else if(isset($_COOKIE["updatedPassword"]) && $_COOKIE["updatedPassword"] == true){
	        $this->message = "Your password has been updated";
	        setcookie("updatedPassword", "", time() - 3600);
	    }
	}
}
?>