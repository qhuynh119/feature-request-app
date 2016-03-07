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

    <!-- Font -->
    <link href='https://fonts.googleapis.com/css?family=Lato:400,300,700,900' rel='stylesheet' type='text/css'>

    <!-- We put jQuery here instead of the end of page because there is a lot of times that we need to use jQuery parallel with PHP -->
    <!-- jQuery 1.12.1 -->
    <script src="https://code.jquery.com/jquery-1.12.1.min.js" type="text/javascript"></script>
</head>
<body>