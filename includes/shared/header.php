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

    <!-- Google Analytics -->
<!--    <script>-->
<!--        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){-->
<!--                (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),-->
<!--            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)-->
<!--        })(window,document,'script','//www.google-analytics.com/analytics.js','ga');-->
<!---->
<!--        ga('create', 'UA-74132113-2', 'auto');-->
<!--        ga('send', 'pageview');-->
<!--    </script>-->
</head>
<body>