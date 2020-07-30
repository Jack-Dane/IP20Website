<?php

class HomeController{
	
	private $first_day;
	private $number_of_days;
	private $work_days;
	private $holidays;
	private $month;
	private $year;
	private $holidayBookingErrorMessage;
	private $holidayBookingSuccess;
	private $DBConnection;
	
	function __construct($databaseConnection){
		
		$this->DBConnection = $databaseConnection;
		
		//if the user isn't logged in, send them back to the login page
		if(!isset($_SESSION["Id"])){
			header("location:login");
			exit();
		}
		
		$this->checkCookies();

		$weekdays = array("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");
		
		if(isset($_GET["date"])){
			extract($_GET);
			$newDate = date("Y-m", strtotime($date));
			$year = date("Y", strtotime($date));
			$month = date("m", strtotime($date));
			
			$maxYear = date('Y-m-d', strtotime('+2 years'));
			$minYear = date('Y-m-d', strtotime('-1 years'));
			
			if($year > $maxYear || $year < $minYear){
			    $date = date("Y-m");
			}
			$updated_date = $date;
		}else{
			$updated_date = date("Y-m");
		}
		
		if(isset($_POST["submitHoliday"])){
			$this->bookHoliday();
		}
		
		$this->setDays($updated_date);
		
		require_once "Views/Home.php";
	}
	
	private function checkCookies(){
	    if(isset($_COOKIE["holidayErrorMessage"])){
	        $this->holidayBookingErrorMessage = $_COOKIE["holidayErrorMessage"];
	        setcookie("holidayErrorMessage", "", time()-3600, "/");
	    }
	    if(isset($_COOKIE["holidaySuccess"])){
	        $this->holidayBookingSuccess = $_COOKIE["holidaySuccess"];
	        setcookie("holidaySuccess", "", time()-3600, "/");
	    }
	}
	
	private function setDays($updated_date){
		$d = date_parse_from_format("Y-m", $updated_date);
		$this->month = $d["month"];
		$this->year = $d["year"];

		//calculate number of days in month
		$this->number_of_days = cal_days_in_month(CAL_GREGORIAN, $this->month, $this->year);

		//get the first and last day of month
		$first_day = date("w", mktime(0, 0, 0, $this->month, 0, $this->year));
		$this->first_day = $first_day + 1 > 6? 0 : $first_day + 1;

		$this->work_days = $this->DBConnection->getCalendar($this->month, $this->year);
		
		$this->holidays = $this->DBConnection->GetHolidays($this->month, $this->year);
	}
	
	private function bookHoliday(){
		if(!empty($_POST["startHolidayPick"]) && !empty($_POST["endHolidayPick"])){
			extract($_POST);
			
			$startHolidayPickDate = strtotime($startHolidayPick);
			$endHolidayPickDate = strtotime($endHolidayPick);
			
			if($startHolidayPickDate <= $endHolidayPickDate){
			    
			    $currentDate = new DateTime("now", new DateTimeZone('Europe/London'));
			    $currentDate = $currentDate->format('Y-m-d');
			    if( date("Y-m-d" , strtotime($startHolidayPick)) > $currentDate){
    			    $dayCounter = 0;
    				
    				$holidayDays = array();
    				$overbooked = false;
    				//only allow booking of 7 days at a time
    				while (strtotime($startHolidayPick . " + ".$dayCounter." days") <= $endHolidayPickDate){
    					$holidayDays[] = date("Y-m-d" , strtotime($startHolidayPick . " + ".$dayCounter." days"));
    					if($dayCounter == 6){
    					    $overbooked = true;
    					    break;
    					}
    					$dayCounter++;
    				}
					
    				$errorMessage = $this->DBConnection->BookHoliday($holidayDays);
    				if(!$errorMessage == ""){
    				    setcookie("holidayErrorMessage", $errorMessage, time() + 86400, "/");
    				}else{
    				    if($overbooked){
    				        setcookie("holidaySuccess", "Your holiday has been successfully booked, however you can only book 7 days at a time.", time() + 86400, "/");
    				    }else{
    				        setcookie("holidaySuccess", "Your holiday has been successfully booked.", time() + 86400, "/");
    				    }
    				}
    				//holiday booked notificaiton
			    }else{
			        setcookie("holidayErrorMessage", "The holiday cannot be before tomorrows date", time() + 86400, "/");
			    }
			}else{
			    setcookie("holidayErrorMessage", "The end date is before the begining date", time() + 86400, "/");
			}
		}else{
		    setcookie("holidayErrorMessage", "Both dates need to be picked", time() + 86400, "/");
		}
		
		//redirect so when user refreshes page the post request isn't sent again
		header("location:home");
	}
}
?>