<?php
/**************************************************
 *** CONNECT TO SERVER
 **************************************************/
// connection fields
$server_name = 'localhost';
$username    = 'root';
$password    = 'root';
$dbname      = 'feature_request_app';

// create connection
$conn = mysqli_connect($server_name, $username, $password, $dbname);

// check connection
if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
}
?>