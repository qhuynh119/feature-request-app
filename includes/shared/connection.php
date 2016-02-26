<?php
/**************************************************
 *** CONNECT TO SERVER
 **************************************************/
// connection fields
$server_name = '50.62.209.6';
$username    = 'feat_req_app';
$password    = 'feat_req_app';
$dbname      = 'feature_request_app';

// create connection
$conn = mysqli_connect($server_name, $username, $password, $dbname);

// check connection
if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
}
session_write_close(); // avoid timeout
session_start();
?>