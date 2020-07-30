<?php
class ResetPasswordController{
	
	private $key;
	private $email;
	private $error;
	private $DBConnection;
	
	function __construct($databaseConnection){
	    $this->DBConnection = $databaseConnection;
		
		$this->CheckPost();
		
		$this->CheckGet();
	}
	
	private function CheckPost(){
		if(isset($_POST["newpassword"]) && isset($_POST["newpassword2"]) && isset($_POST["key"]) && isset($_POST["email"])){
			extract($_POST);
		    if($this->DBConnection->ResetPassword($email, $newpassword, $key)){
		        setcookie("updatedPassword", true, time() + 84600, "/");
				header("location:resetpassword");
			}else{
			    setcookie("updatedPassword", false, time() + 84600, "/");
			    header("location:resetpassword");
			}
			
		}else if(isset($_POST["email"])){
			extract($_POST);
			
			//does the email exist
			$exists = $this->DBConnection->DoesEmailExist($email);
			if($exists){
			    setcookie("sentEmail", "The email has been sent", time() + 84600, "/");
				header("location:resetpassword");
			}else{
			    setcookie("sentEmail", "That isn't a valid email address", time() + 84600, "/");
				header("location:resetpassword");
			}
		}
	}
	
	private function CheckGet(){
		if(isset($_GET["email"]) && isset($_GET["key"])){
			extract($_GET);
			
			if($this->DBConnection->CheckPasswordResetData($email, $key)){
				require_once "Views/ResetPasswordForm.php";
			}else{
				$this->error = "Invalid Key And/Or Email";
				require_once "Views/ResetPassword.php";
			}
		}else{
			//if they have been sent here after reset
			if(isset($_COOKIE["updatedPassword"])){
				if($_COOKIE["updatedPassword"] == true){
					header("location:login");
				}else{
					$this->error = "Could Not Update Password";
					setcookie("updatedPassword", "", time() - 3600);
				}
			}else if(isset($_COOKIE["sentEmail"])){
			    $this->error = $_COOKIE["sentEmail"];
			    setcookie("sentEmail", "", time() - 3600);
			}
			require_once "Views/ResetPassword.php";
			
		}
	}
}

?>