<?php
Route::set("", function(){
    $databaseConnection = new DBConnection();
    $homeController = new HomeController($databaseConnection);
});

Route::set("home", function(){
    $databaseConnection = new DBConnection();
    $homeController = new HomeController($databaseConnection);
});

Route::set("login", function(){
    $databaseConnection = new DBConnection();
    $loginController = new LoginController($databaseConnection);
});

Route::set("logout", function(){
	Controller::Logout();
});

Route::set("resetpassword", function(){
    $databaseConnection = new DBConnection();
    $resetPasswordController = new ResetPasswordController($databaseConnection);
});

Route::set("input", function(){
    $databaseConnection = new DBConnection();
    $inputController = new inputController($databaseConnection);
});

?>