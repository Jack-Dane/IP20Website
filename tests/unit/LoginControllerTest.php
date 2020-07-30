<?php
use PHPUnit\Framework\TestCase;
require_once "./Controllers/LoginController.php";
require_once "./Models/DBConnection.php";

class LoginControllerTest extends TestCase{
	
	/**
    * @runInSeparateProcess
    */
	public function testLoginFailed(){
		$_COOKIE["loginError"] = "The wrong Username or Password has been entered"; 
		
		$databaseStub = $this->createMock(DBConnection::class);
		
		$loginController = new LoginController($databaseStub);
		
		$reflectionLoginController = new ReflectionObject($loginController);
		$reflectionFailMessage = $reflectionLoginController->getProperty("message");
		$reflectionFailMessage->setAccessible(true);
		
		$this->assertEquals($reflectionFailMessage->getValue($loginController), "The wrong Username or Password has been entered");
	}
	
	/**
    * @runInSeparateProcess
    */
	public function testUpdatePassword(){
		$_COOKIE["updatedPassword"] = true; 
		
		$databaseStub = $this->createMock(DBConnection::class);
		
		$loginController = new LoginController($databaseStub);
		
		$reflectionLoginController = new ReflectionObject($loginController);
		$reflectionFailMessage = $reflectionLoginController->getProperty("message");
		$reflectionFailMessage->setAccessible(true);
		
		$this->assertEquals($reflectionFailMessage->getValue($loginController), "Your password has been updated");
	}
	
}