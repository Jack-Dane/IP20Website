<!DOCTYPE html>
<html>
	<head>
		<title>Work Sheet</title>
		<link rel="stylesheet" type="text/css" href="style.css">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	</head>
	<body>
		<div class="MainBody" id="indexBody">
			<a id="logoutButton" href="logout">Logout</a>
		
			<h1>Work Rota</h1>
			<table id="Calender">
				<?php
				
				echo "<caption><b> " . date("m-Y", strtotime($updated_date)) . " </b></caption>";
				
				echo "<tr>";
				foreach($weekdays as $day){
					echo "<th>".substr($day, 0, 2)."</th>";
				}
				echo "</tr>";
				
				$row = "<tr>";
				$total = 0;
				$day = 0;
				
				while($total <= 6 * 7){
			
					if($total % 7 == 0){
						$row .= "</tr>";
						echo $row;
						$row = "<tr>";
					}
				
					if($this->first_day > 0 || $day >= $this->number_of_days){
						$row .= "<td> . </td>";
						$this->first_day--;
					}else{
						$day++;
						$temp_row = "<td>".$day."</td>";
						$current_day = date("d-m-Y", strtotime($day."-".$this->month."-".$this->year));
						foreach($this->holidays as $days){
							if($days->mdate == $current_day){
								if($days->mstate == "Pending"){
									$temp_row = "<td style = 'background-color: orange;'>".$day."</td>";
								}else if($days->mstate == "Denied"){
									$temp_row = "<td style = 'background-color: red;'>".$day."</td>";
								}else{
									$temp_row = "<td style = 'background-color: green;'>".$day."</td>";
								}
							}
						}
						foreach($this->work_days as $days){
							if($days->mdate == $current_day){
								$start_time = json_encode($days->mstart_time);
								$finish_time = json_encode($days->mfinish_time);
								$rota_start_time = json_encode($days->mrota_start_time);
								$rota_finish_time = json_encode($days->mrota_finish_time);
								$date = json_encode($days->mdate);
								$store = json_encode($days->mstore);
								$break_time = json_encode($days->mbreak_time);
								
								$temp_row = "<td><a class='hoursLink' href='#' onclick='DisplayHours($start_time, $finish_time, $rota_start_time, $rota_finish_time, $date, $break_time, $store);'>".$day."</a></td>";
							}
						}
						$row.=$temp_row;
					}
				
					$total++;
				}
				
				?>
			</table>
			
			<!-- Changing the month form -->
			<div id="datesForm">
				<form action="home" method="GET" class="inline">
					<input type="Hidden" name="date" value="<?php echo date('Y-m', strtotime("-1 month", strtotime($updated_date)));?>">
					<input type="Submit" value="Previous Month"  name="direction" class="button" id="nextButton">
				</form>
				<form action="home" method="GET" class="inline">
					<input type="Hidden" name="date" value="<?php echo date('Y-m', strtotime("+1 month", strtotime($updated_date)));?>">
					<input type="Submit" value="Next Month" name="direction" class="button" id="nextButton">
				</form>
			</div>
		</div>
		
		<!-- Holiday booking form -->
		<div id="holidayBooking" class="MainBody">
			<h1>Holiday Booking</h1>
			<form action="home" method="POST" id="holidayBookingForm" class="inline">
				<h2>Holiday Start Date </h2><input type="date" name="startHolidayPick">
				<h2>Holiday End Date </h2><input type="date" name="endHolidayPick"><br><br>
				<input type="Submit" value="Book Holiday" name="submitHoliday" class="button" id="submitHolidayButton">
			</form>
			<?php
			if(isset($this->holidayBookingErrorMessage)){
				echo "<p class='errorText'>".$this->holidayBookingErrorMessage."</p>";
			}
			if(isset($this->holidayBookingSuccess)){
			    echo "<p class='errorText'>".$this->holidayBookingSuccess." </p>";
			}
			?>
		</div>
		
		<!-- Pop up window to display the current working day -->
		<div id="workingTime">
			<p id="rotaDate">dd/mm/yyyy</p>
			<p>
				<p class="inline clockdata1">Clock in time:</p>
				<p id="startWorkTime" class="inline clockdata2">0</p>
			</p>
			<p>
				<p class="inline clockdata1">Clock out time:</p>
				<p id="finishWorkTime" class="inline clockdata2">0</p>
			</p>
			<p>
				<p class="inline clockdata1">Rota Time Start: </p>
				<p id="startRotaTime" class="inline clockdata2">0</p>
			</p>
			<p>
				<p class="inline clockdata1">Rota Time Finish: </p>
				<p id="finishRotaTime" class="inline clockdata2">0</p>
			</p>
			<p>
				<p class="inline clockdata1">Hours Worked: </p>
				<p id="hoursWorked" class="inline clockdata2">0</p>
			</p>
			<p>
				<p class="inline clockdata1">Break Time: </p>
				<p id="breakTime" class="inline clockdata2">0</p>
			</p>
			<p>
				<p class="inline clockdata1">Store: </p>
				<p id="storeNumber" class="inline clockdata2">0</p>
			</p>
			<a id="closeButton" href="#"><img height="100%" width="100%" src="Images/cross_icon.png"></a>
		</div>
		<script src="Display.js?t=5"></script>
	</body>
</html>