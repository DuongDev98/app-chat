<?php
    if(!isset($_SESSION)) 
    { 
        session_start(); 
    }
    $serverName = "D-COMPUTER\MSSQL, 1433";
    $connectionInfo = array( "Database"=>"CHATROOM", "UID"=>"sa", "PWD"=>"1433", "CharacterSet" => "UTF-8" );
    sqlsrv_configure('WarningsReturnAsErrors',0);
    $conn = sqlsrv_connect($serverName, $connectionInfo);
    if(!$conn ) {
        die( print_r(sqlsrv_errors(), true));
    }