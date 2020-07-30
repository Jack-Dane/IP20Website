<!DOCTYPE html>
<?php
?>

<html>
	<head>
		<title>Game Rota</title>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
		<link rel="stylesheet" type="text/css" href="style.css">
	</head>
	<body>
		<div class="MainBody">
			<h1>GAME LOGIN PAGE</h1>
			<form method="POST" action="login">
				<p style="margin: 2px;">Username: </p><br><input style="margin: 2px;" type="text" name="g_username">
				<br><p style="margin: 2px;">Password: </p><br><input style="margin: 2px;" type="password" name="g_password"><br><br>
				<input type="submit" value="Login" class="button" id="loginButton">
			</form>
			<?php
			if(isset($this->message)){
				echo "<p class='errorText'>".$this->message."</p>";
			}
			?>
			<p><a href="resetpassword">Forgot Password?</a></p>
		</div>
	</body>
</html>