<?php
/**************************************************
 *** HEADER
 **************************************************/
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Feature Request App</title>

    <!-- Bootstrap Core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet" />
    <!-- Bootstrap Theme CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet" />

    <?php
    if (strpos($_SERVER['PHP_SELF'], 'external_auth.php') !== false) {
        ?>
        <!-- External Auth Stylesheet -->
        <link href="./css/external_auth/style.css" rel="stylesheet"/>
        <?php
    } else {
        ?>
        <!-- Main Stylesheet -->
        <link href="css/style.css" rel="stylesheet"/>
        <?php
    }
    ?>

    <link href='https://fonts.googleapis.com/css?family=Lato:400,300,700,900' rel='stylesheet' type='text/css'>
</head>

<?php
// if the query string contains 'client_selection_id',
// it means the user clicked 'View Requests' on Clients page
// so we will need to select that client in the drop-down box
// and repopulate the data of features table to show only the requests of that client
if (isset($_GET['client_selection_id'])) {
    $client_id = $_GET['client_selection_id'];
    ?>
    <body onload="select_client_option('<?= $_GET['client_selection_id'] ?>');">
    <?php
} else {
    ?>
    <body>
    <?php
}
?>
