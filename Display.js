
function DisplayHours(startTime, finishTime, rotaStart, rotaFinish, date, breakTime, storeId){
	
	$("div#indexBody").addClass("greyOverlay");
	
	$("div#workingTime").css("display", "block");
	
	$("#startWorkTime").text(startTime);
	$("#finishWorkTime").text(finishTime);
	$("#finishRotaTime").text(rotaFinish);
	$("#startRotaTime").text(rotaStart);
	$("#storeNumber").text(storeId);
	$("#breakTime").text(breakTime);
	
	let breakTimeHours = breakTime.substr(0,2);
	let breakTimeMins = breakTime.substr(3,2);
	
	let startTimeHours = startTime.substr(0,2);
	let startTimeMins = startTime.substr(3,2);
	
	let finishTimeHours = finishTime.substr(0,2);
	let finishTimeMins = finishTime.substr(3,2);
	
	let startTotalMins = (parseInt(startTimeHours, 10) * 60) + parseInt(startTimeMins, 10);
	let finishTotalMins = (parseInt(finishTimeHours, 10) * 60) + parseInt(finishTimeMins, 10);
	let breakTimeTotalMins = (parseInt(breakTimeHours, 10) * 60) + parseInt(breakTimeMins, 10);
	
	let totalHours = (parseInt((finishTotalMins-startTotalMins-breakTimeTotalMins)/60));
	let totalMins = (parseInt((finishTotalMins-startTotalMins-breakTimeTotalMins)%60));
	
	if(totalHours < 0 || totalMins < 0){
	    totalHours = 0;
	    totalMins = 0;
	}
	
	/*
	startTime=(startTime.substr(0,5).replace(":", "."));
	finishTime=(finishTime.substr(0,5).replace(":", "."));
	*/
	
	totalMins = totalMins.toString(10).length == 1 ? "0" + totalMins.toString(10) : totalMins; 
	
	$("#hoursWorked").text(totalHours + ":" + totalMins);//change to work out time when minuites used 
	$("#rotaDate").text(date);
}

$("a#closeButton").click(function(){
	$("div#workingTime").css("display", "none");
	$("div#indexBody").removeClass("greyOverlay");
});