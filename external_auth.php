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

            <?php
            if ($sub_action == 'error') {
                $error = mysqli_real_escape_string($conn, $_SESSION['error']);
                ?>
                <div class="alert alert-danger" id="error"><?= $error ?></div>
                <?php
            }
            ?>

            <div class="form-group">
                <label for="sign_up_first_name" class="sr-only">First Name</label>
                <input type="text" name="sign_up_first_name" class="form-control" placeholder="First Name" value="<?= $_SESSION['first'] ?>" required autofocus>
                <label for="sign_up_last_name" class="sr-only">Password</label>
                <input type="text" name="sign_up_last_name" class="form-control" placeholder="Last Name" value="<?= $_SESSION['last'] ?>" required>
            </div><!-- .form-group -->

            <div class="form-group">
                <label for="sign_up_email" class="sr-only">Email</label>
                <input type="email" name="sign_up_email" class="form-control" placeholder="Email" required>
                <label for="sign_up_password" class="sr-only">Password</label>
                <input type="password" name="sign_up_password" class="form-control" placeholder="Password" required>
            </div><!-- .form-group -->

            <div class="form-group">
                <label for="sign_up_company" class="sr-only">Password</label>
                <input type="text" name="sign_up_company" class="form-control" placeholder="Company" value="<?= $_SESSION['company'] ?>" required>
            </div><!-- .form-group -->

            <button class="btn btn-lg btn-primary btn-block" type="submit">Submit</button>
        </form>

        <p class="text-center"><a href="external_auth.php">Sign In</a></p>
    </div><!-- .container -->
    <?php
}
?>

<?php
/**************************************************
 *** SIGN UP
 **************************************************/
if ($action == 'sign_up_submit') {
    if (isset($_POST)) {
        $error     = '';
        $first     = mysqli_real_escape_string($conn, $_POST['sign_up_first_name']);
        $last      = mysqli_real_escape_string($conn, $_POST['sign_up_last_name']);
        $email     = mysqli_real_escape_string($conn, $_POST['sign_up_email']);
        $password  = mysqli_real_escape_string($conn, $_POST['sign_up_password']);
        $company   = mysqli_real_escape_string($conn, $_POST['sign_up_company']);

        // check to see if the email has been registered
        $sql = "SELECT COUNT(id) AS count
                FROM feature_request_app.app_user
                WHERE email = '".$email."'";
        $result = mysqli_query($conn, $sql);
        $result = mysqli_fetch_assoc($result);

        // if yes, provide a message
        if ($result['count'] > 0) {
            $error = "This email has been registered.</br>";

            // repopulate the fields
            $_SESSION['first'] = $first;
            $_SESSION['last'] = $last;
            $_SESSION['company'] = $company;
            $_SESSION['error'] = $error;

            // redirect to sign up form with a message
            redirect('?action=sign_up&sub_action=error');
        }

        // check password strength
        $error = check_password_strength($password);

        // if length of $error = 0, it's fine
        if (strlen($error) == 0) {
            // generate hash from password to store in the database
            $hash = password_hash($password, PASSWORD_BCRYPT);

            $sql = "INSERT INTO feature_request_app.app_user
                    SET first_name = '".$first."',
                        last_name = '".$last."',
                        email = '" .$email."',
                        password = '" .$hash."',
                        company = '" .$company."'";
            $result = mysqli_query($conn, $sql);
        } else {
            $_SESSION['error'] = $error;

            // redirect to sign up form with a message
            redirect('?action=sign_up&sub_action=error');
        }

        // unset the session variables
        unset($_SESSION['first']);
        unset($_SESSION['last']);
        unset($_SESSION['company']);
        unset($_SESSION['error']);

        // use cookie as default for new user sign up
        setcookie('logged_in', true, time() + (86400 * 365), '/');
        setcookie('user_id', mysqli_insert_id($conn), time() + (86400 * 365), '/');

        // redirect to main page
        redirect("index.php?action=add_client&sub_action=first_request");
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

        // we check the email first
        $sql = "SELECT *, COUNT(id) AS count
                FROM feature_request_app.app_user
                WHERE email = '".$email."'
                LIMIT 1";
        $result = mysqli_query($conn, $sql);
        $result = mysqli_fetch_assoc($result);

        $can_sign_in = false;

        // if there's an account registered with the input email,
        // continue checking the password
        if ($result['count'] != 0) {
            // hash the password to compare with the hash in database
            $hash = password_hash($password, PASSWORD_BCRYPT);

            // if they are equal to each other, set up cookies or sessions
            if (password_verify($password, $hash)) {
                /* Valid */
                $can_sign_in = true;

                // use cookie if remember_me
                if ($remember_me == 'true') {
                    setcookie('logged_in', true, time() + (86400 * 365), '/');
                    setcookie('user_id', $result['id'], time() + (86400 * 365), '/');
                } else {
                    $_SESSION['logged_in'] = true;
                    $_SESSION['user_id'] = $result['id'];
                }

                // redirect to main page
                redirect('index.php');
            }
        }

        if (!$can_sign_in) {
            /* Invalid */
            // redirect to sign in form with a message
            redirect('?sub_action=sign_in_error');
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
                </div><!-- .alert .alert-danger -->
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
            </div><!-- .checkbox -->

            <button class="btn btn-lg btn-primary btn-block" type="submit">Sign In</button>
        </form>

        <p class="text-center"><a href="?action=sign_up">I'm new here</a></p>
    </div><!-- /container -->
    <?php
}
?>

<?php
/**************************************************
 *** FOOTER
 **************************************************/
include_once('includes/shared/footer.php');
?>

