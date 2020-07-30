<?php

//start session
session_start();

//load all classes, models and controllers needed
function autoLoad($class){
	if(is_file("Models/".$class.".php")){
		require_once "Models/".$class.".php";
	} else if(is_file("Controllers/".$class.".php")){
		require_once "Controllers/".$class.".php";
	}
}

//perform autoload function
spl_autoload_register("autoLoad");

//call the routes after, where all web routes are stored
require_once "Routes.php";
?>