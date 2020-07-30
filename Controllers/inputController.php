<?php

class InputController{
    
    function __construct($databaseConnection){
        $dbConnection = $databaseConnection;

        if(isset($_POST["id"]) && isset($_POST["storeId"]) && isset($_POST["clockIn"])){
        	extract($_POST);
        	
        	echo $dbConnection-> ClockInEmployee($id, $storeId, $clockIn);
        }
    }
}

?>