<?php
/**************************************************
 *** FEATURE REQUEST APP
 *** USER AUTHENTICATION
 *** author: Quan Huynh
 **************************************************/

/**************************************************
 *** INCLUDES
 **************************************************/
include_once('includes/shared/connection.php');
include_once('includes/shared/functions.php');
include_once('includes/shared/password.php');
ob_start();

// get the action and sub_action value
$action = $_GET['action'];
$sub_action = $_GET['sub_action'];

/**************************************************
 *** HEADER
 **************************************************/
include_once('includes/shared/header.php');

/**************************************************
 *** BODY
 **************************************************/
?>

<?php
/**************************************************
 *** SIGN UP FORM
 **************************************************/
if ($action == 'sign_up') {
    ?>
    <div class="container">
        <form method="post" action="?action=sign_up_submit" class="form-signin">
            <h2 class="form-signin-heading">Create an Account</h2>

            <div class="hidden alert alert-danger" id="error"></div>

            <div class="form-group">
                <label for="sign_up_email" class="sr-only">Email</label>
                <input type="email" name="sign_up_email" class="form-control" placeholder="Email" required autofocus>
                <label for="sign_up_password" class="sr-only">Password</label>
                <input type="password" name="sign_up_password" id="sign_in_password" class="form-control" placeholder="Password" required>
            </div>

            <div class="form-group">
                <label for="sign_up_company" class="sr-only">Password</label>
                <input type="text" name="sign_up_company" class="form-control" placeholder="Company" required>
            </div>

            <button class="btn btn-lg btn-primary btn-block" type="submit">Submit</button>
        </form>

        <p class="text-center"><a href="external_auth.php">Sign In</a></p>
    </div>
    <?php
}
?>

<?php
/**************************************************
 *** SIGN UP
 **************************************************/
if ($action == 'sign_up_submit') {
    if (isset($_POST)) {
        $email     = mysqli_real_escape_string($conn, $_POST['sign_up_email']);
        $password  = mysqli_real_escape_string($conn, $_POST['sign_up_password']);
        $company   = mysqli_real_escape_string($conn, $_POST['sign_up_company']);

        // generate hash from password to store in the database
        $hash = password_hash($password, PASSWORD_BCRYPT);

        $sql = "INSERT INTO feature_request_app.app_user
                SET email = '" .$email."',
                    password = '" .$hash."',
                    company = '" .$company."'";

        if (!mysqli_query($conn, $sql)) {
            $error = "There is an error. Please try again later.";
        }

        setcookie('logged_in', true, time() + (86400 * 365), '/');

        redirect("index.php");
    }
}
?>

<?php
/**************************************************
 *** SIGN IN
 **************************************************/
if ($action == 'sign_in_submit') {
    if (isset($_POST)) {
        $email       = mysqli_real_escape_string($conn, $_POST['sign_in_email']);
        $password    = mysqli_real_escape_string($conn, $_POST['sign_in_password']);
        $remember_me = $_POST['remember_me'];

        $sql = "SELECT *, COUNT(id) AS count
                FROM feature_request_app.app_user
                WHERE email = '".$email."'
                LIMIT 1";
        $result = mysqli_query($conn, $sql);
        $result = mysqli_fetch_assoc($result);

        $can_sign_in = false;

        if ($result['count'] != 0) {
            $hash = password_hash($password, PASSWORD_BCRYPT);

            if (password_verify($password, $hash)) {
                /* Valid */
                $can_sign_in = true;

                if ($remember_me == 'true') {
                    setcookie('logged_in', true, time() + (86400 * 365), '/');
                    setcookie('id', $result['id'], time() + (86400 * 365), '/');
                    setcookie('email', $result['email'], time() + (86400 * 365), '/');
                    setcookie('company', $result['company'], time() + (86400 * 365), '/');
                } else {
                    $_SESSION['logged_in'] = true;
                    $_SESSION['user'] = array(
                        'id' => $result['id'],
                        'email' => $result['email'],
                        'company' => $result['company']
                    );
                }

                redirect('index.php');
            }
        }

        if (!$can_sign_in) {
            /* Invalid */
            redirect('?sub_action=sign_in_error&test=');
        }
    }
}
?>

<?php
/**************************************************
 *** SIGN IN FORM
 **************************************************/
if ($action == null) {
    ?>
    <div class="container">
        <form method="post" action="?action=sign_in_submit" class="form-signin">
            <h2 class="form-signin-heading">Welcome!</h2>

            <?php
            if ($sub_action == 'sign_in_error') {
                ?>
                <div class="alert alert-danger">
                    The email or password you’ve entered is not correct.
                    <strong><a style="color: #A94442;" href="?action=sign_up">Sign up for an account</a>.</strong>
                </div>
                <?php
            }
            ?>

            <label for="sign_in_email" class="sr-only">Email</label>
            <input type="email" name="sign_in_email" class="form-control" placeholder="Email" required autofocus>

            <label for="sign_in_password" class="sr-only">Password</label>
            <input type="password" name="sign_in_password" class="form-control" placeholder="Password" required>

            <div class="checkbox">
                <label>
                    <input type="checkbox" name="remember_me" value="true"> Remember me
                </label>
            </div>

            <button class="btn btn-lg btn-primary btn-block" type="submit">Sign In</button>
        </form>

        <p class="text-center"><a href="?action=sign_up">I'm new here</a></p>
    </div> <!-- /container -->
    <?php
}
?>

<?php
/**************************************************
 *** FOOTER
 **************************************************/
include_once('includes/shared/footer.php');
?>

