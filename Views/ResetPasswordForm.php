<!DOCTYPE html>

<html>
	<head>
		<title>ResetPassword</title>
		<link rel="stylesheet" type="text/css" href="style.css">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	</head>
	
	<body>
		<div class="MainBody">
		    <a id="logoutButton" href="login">Home</a>
			<h1> Enter New Password </h1>
			<form name="resetPasswordForm" method="POST" action="resetpassword" id="resetPasswordForm">
				<p> New Password : <input type="password" name="newpassword" id="newPasswordId"></p>
				<p> Confirm New Password: <input type="password" name="newpassword2" id="newPassword2Id"></p>
				<input type="hidden" name="email" value=<?php echo $email?>>
				<input type="hidden" name="key" value=<?php echo $key?>><br>
				<input type="Submit" name="Submit" class="button">
			</form>
			<div id="ErrorDisplay"></div>
		</div>
		<script>
		    $("#resetPasswordForm").on("submit", function() {
		        var password1 = $("#newPasswordId").val();
		        var password2 = $("#newPassword2Id").val();
		        
		        $("div#ErrorDisplay").empty();
		        
		        if(password1 === password2){
		            if(password1.length < 8){
		                $("div#ErrorDisplay").append("<p class='errorText'>The password must contain be 8 characters or longer</p>");
		                return false;
		            }else if(!/[a-z]/.test(password1)){
		                $("div#ErrorDisplay").append("<p class='errorText'>The password must contain a lower case letter</p>");
		                return false;
		            }else if(!/[A-Z]/.test(password1)){
		                $("div#ErrorDisplay").append("<p class='errorText'>The password must contain a upper case letter</p>");
		                return false;
		            }else if(!/\d/.test(password1)){
		                $("div#ErrorDisplay").append("<p class='errorText'>The password must contain a numeric character</p>");
		                return false;
		            }else if(!/[@$!%*?&]/.test(password1)){
		                $("div#ErrorDisplay").append("<p class='errorText'>The password must contain a character from \"@$!%*?&\"</p>");
		                return false;
		            }
		            return true;
		        }else{
		            $("div#ErrorDisplay").append("<p class='errorText'>Both password fields must be the same</p>");
		            return false;
		        }
		    });
		</script>
	</body>
</html>