<?php
class Controller{
	
	public static function Logout(){
		session_destroy();
		header("location:login");
		exit();
	}
}

?>