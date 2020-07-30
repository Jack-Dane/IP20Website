<!DOCTYPE html>

<html>
	<head>
		<title>Reset Password</title>
		<link rel="stylesheet" type="text/css" href="style.css">
	</head>

	<body>
		<div class="MainBody">
		    <a id="logoutButton" href="login">Home</a>
			<h1>Reset Password</h1>
			
			<form method="POST" action="resetpassword">
				<p>Email: <input type="text" name="email"></p>
				<input type="Submit" name="Submit" class="button">
			</form>
			
			<?php
				if(isset($this->error)){
					echo "<p class='errorText'> " . $this->error . " </p>";
				}
			?>
		</div>
	</body>
</html>