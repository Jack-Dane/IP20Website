<?php
use PHPUnit\Framework\TestCase;
require_once "./Controllers/ResetPasswordController.php";
require_once "./Models/DBConnection.php";

class ResetPasswordControllerTest extends TestCase{
	
	/**
    * @runInSeparateProcess
    */
	public function testWrongEmailOrKeyResetPasswordCorrect(){
		$_GET["email"] = "test.email@testemail.com";
		$_GET["key"] = "randomkey";
		
		$databaseStub = $this->createMock(DBConnection::class);
		$databaseStub->method("CheckPasswordResetData")->willReturn(true);
		
		$resetPasswordController = new ResetPasswordController($databaseStub);
		
		$included_files = get_included_files();
		
		$this->assertTrue(in_array("C:\wamp64\www\EmployeeWebsite\Views\ResetPasswordForm.php", $included_files));
	}

	/**
    * @runInSeparateProcess
    */
	public function testWrongEmailOrKeyResetPasswordError(){
		$_GET["email"] = "test.email@testemail.com";
		$_GET["key"] = "randomkey";
		
		$databaseStub = $this->createMock(DBConnection::class);
		$databaseStub->method("CheckPasswordResetData")->willReturn(false);
		
		$resetPasswordController = new ResetPasswordController($databaseStub);
		
		$reflectionResetPasswordController = new ReflectionObject($resetPasswordController);
		$reflectionErrorMessage = $reflectionResetPasswordController->getProperty("error");
		$reflectionErrorMessage->setAccessible(true);
		
		$this->assertEquals($reflectionErrorMessage->getValue($resetPasswordController), "Invalid Key And/Or Email");
	}
	
	/**
    * @runInSeparateProcess
    */
	public function testUpdatePasswordError(){
		$_COOKIE["updatedPassword"] = false;
		$_GET["key"] = "randomkey";
		
		$databaseStub = $this->createMock(DBConnection::class);
		$databaseStub->method("CheckPasswordResetData")->willReturn(false);
		
		$resetPasswordController = new ResetPasswordController($databaseStub);
		
		$reflectionResetPasswordController = new ReflectionObject($resetPasswordController);
		$reflectionErrorMessage = $reflectionResetPasswordController->getProperty("error");
		$reflectionErrorMessage->setAccessible(true);
		
		$this->assertEquals($reflectionErrorMessage->getValue($resetPasswordController), "Could Not Update Password");
	}
	
	/**
    * @runInSeparateProcess
    */
	public function testSentEmailNotification(){
		$_COOKIE["sentEmail"] = "The email has been sent";
		
		$databaseStub = $this->createMock(DBConnection::class);
		$databaseStub->method("CheckPasswordResetData")->willReturn(false);
		
		$resetPasswordController = new ResetPasswordController($databaseStub);
		
		$reflectionResetPasswordController = new ReflectionObject($resetPasswordController);
		$reflectionErrorMessage = $reflectionResetPasswordController->getProperty("error");
		$reflectionErrorMessage->setAccessible(true);
		
		$this->assertEquals($reflectionErrorMessage->getValue($resetPasswordController), "The email has been sent");
	}
}

?>