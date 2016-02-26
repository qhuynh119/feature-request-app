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
</head>
<body>