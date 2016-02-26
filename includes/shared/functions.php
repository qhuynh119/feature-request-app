<?php
/**************************************************
 *** HELPER FUNCTIONS
 **************************************************/
function get_num_requested_features($conn, $client_id) {
    // get the number of requested features of a specific client
    $sql       = "SELECT COUNT(id) AS count
				  FROM feature_request_app.requested_feature
				  WHERE client_id = '".mysqli_real_escape_string($conn, $client_id)."'";
    $result    = mysqli_query($conn, $sql);
    $result    = mysqli_fetch_assoc($result);

    return $result['count'];
}

function is_logged_in() {
    // check if the user is logged in
    return ($_SESSION['logged_in'] != '1' && $_COOKIE['logged_in'] != true) ? false : true;
}

function back_url() {
    // return the url of previous page
    return htmlentities($_SERVER['PHP_SELF']);
}

function redirect($url) {
    // redirect to another page
    $url = trim($url);

    if (!preg_match('/^http:\/\/|https:\/\/|ftp:\/\//i', $url) ) {
        $url = ('http://' . $_SERVER['HTTP_HOST'] . (strpos($url, '/') === 0)
            ? $url : (dirname($_SERVER['PHP_SELF']) . '/' . $url));
    }

    header('Location: ' . $url); //use absolute URI
    exit();
}

function check_password_strength($pwd) {
    // this functions check the strength of the password when the user registers for an account
    $error = '';
    if (strlen($pwd) < 8) {
        $error .= '• Password too short (at least 8 chars.)<br/>';
    }
    if (strlen($pwd) > 20) {
        $error .= "• Password too long (at most 20 chars.)<br/>";
    }
    if (!preg_match("#[0-9]+#", $pwd)) {
        $error .= "• Password must include at least one number<br/>";
    }
    if (!preg_match("#[a-z]+#", $pwd)) {
        $error .= "• Password must include at least one letter<br/>";
    }

//    if( !preg_match("#[A-Z]+#", $pwd) ) {
//        $error .= "Password must include at least one CAPS!";
//    }
//    if( !preg_match("#\W+#", $pwd) ) {
//        $error .= "Password must include at least one symbol!";
//    }

    return $error;
}
?>