<?php
use PHPUnit\Framework\TestCase;
require_once "./Controllers/HomeController.php";
require_once "./Models/DBConnection.php";

class HomeControllerTest extends TestCase{
	
	public function bookHolidaySuccessDataProvider(){
		return array(
			array(
				"2020-07-20",
				"2020-07-24",
				array(
						"2020-07-20",
						"2020-07-21",
						"2020-07-22",
						"2020-07-23",
						"2020-07-24",
						)
			),
			array(
				"2020-07-24",
				"2020-07-24",
				array(
						"2020-07-24"
						)
			)
		);
	}
	
	/**
    * @runInSeparateProcess
	* @dataProvider bookHolidaySuccessDataProvider
    */
	public function testBookHolidaySuccess($firstDate, $lastDate, $holidayList){
		$databaseStub = $this->createMock(DBConnection::class);
		
		$databaseStub->method("getCalendar")->willReturn(array());
		$databaseStub->method("GetHolidays")->willReturn(array());
		
		$databaseStub->expects($this->once())
						->method("BookHoliday")
						->with($this->equalTo($holidayList))
						->willReturn("");
		
		$_POST["submitHoliday"] = 1;
		$_POST["startHolidayPick"] = $firstDate;
		$_POST["endHolidayPick"] = $lastDate;
		$_SESSION["Id"] = 3;
		
		$homeController = new HomeController($databaseStub);
	}
	
	public function bookHolidayFailProvider(){
		return array(
			array(
				"2020-07-27",
				"2020-07-24"
			),
			array(
				"2020-01-15",
				"2020-01-18"
			),
			array(
				"2020-01-15",
				""
			),
			array(
				"",
				"2020-01-18"
			)
		);
	}
	
	/**
    * @runInSeparateProcess
	* @dataProvider bookHolidayFailProvider
    */
	public function testBookHolidayFail($startDate, $finishDate){
		$databaseStub = $this->createMock(DBConnection::class);
		
		$databaseStub->method("getCalendar")->willReturn(array());
		$databaseStub->method("GetHolidays")->willReturn(array());
		
		$databaseStub->expects($this->exactly(0))
						->method("BookHoliday");
		
		$_POST["submitHoliday"] = 1;
		$_POST["startHolidayPick"] = $startDate;
		$_POST["endHolidayPick"] = $finishDate;
		$_SESSION["Id"] = 3;
		
		$homeController = new HomeController($databaseStub);
	}
	
	/**
    * @runInSeparateProcess
    */
	public function testSetCookiesBookHolidaySuccess(){
		$_COOKIE["holidaySuccess"] = "Your holiday has been successfully booked.";
		$_SESSION["Id"] = 3;
		
		$databaseStub = $this->createMock(DBConnection::class);
		$databaseStub->method("getCalendar")->willReturn(array());
		$databaseStub->method("GetHolidays")->willReturn(array());
		
		$homeController = new HomeController($databaseStub);
		
		$reflectionHomeController = new ReflectionObject($homeController);
		$reflectionSuccessMessage = $reflectionHomeController->getProperty("holidayBookingSuccess");
		$reflectionSuccessMessage->setAccessible(true);
		
		$this->assertEquals($reflectionSuccessMessage->getValue($homeController), "Your holiday has been successfully booked.");
	}
	
	/**
    * @runInSeparateProcess
    */
	public function testSetCookiesBookHolidayErrorMessage(){
		$_COOKIE["holidayErrorMessage"] = "The end date is before the begining date";
		$_SESSION["Id"] = 3;
		
		$databaseStub = $this->createMock(DBConnection::class);
		$databaseStub->method("getCalendar")->willReturn(array());
		$databaseStub->method("GetHolidays")->willReturn(array());
		
		$homeController = new HomeController($databaseStub);
		
		$reflectionHomeController = new ReflectionObject($homeController);
		$reflectionErrorMessage = $reflectionHomeController->getProperty("holidayBookingErrorMessage");
		$reflectionErrorMessage->setAccessible(true);
		
		$this->assertEquals($reflectionErrorMessage->getValue($homeController), "The end date is before the begining date");
	}
}


?>