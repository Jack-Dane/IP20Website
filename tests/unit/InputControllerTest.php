<?php
use PHPUnit\Framework\TestCase;
require_once "./Controllers/inputController.php";
require_once "./Models/DBConnection.php";

class InputControllerTest extends TestCase{
	
	/**
    * @runInSeparateProcess
    */
	function testInputPinSuccess(){
		$databaseStub = $this->createMock(DBConnection::class);
		
		$databaseStub->expects($this->exactly(1))
					->method("ClockInEmployee");
		
		$_POST["id"] = "32451";
		$_POST["storeId"] = "1234";
		$_POST["clockIn"] = "True";
		
		$inputController = new inputController($databaseStub);
	}
	
	/**
    * @runInSeparateProcess
    */
	function testInputPinFail(){
		$databaseStub = $this->createMock(DBConnection::class);
		
		$databaseStub->expects($this->exactly(0))
					->method("ClockInEmployee");
		
		$_POST["id"] = "15433";
		$_POST["clockIn"] = "True";
		
		$inputController = new inputController($databaseStub);
	}
}

