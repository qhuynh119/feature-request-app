<?php
/**************************************************
 *** FEATURE REQUEST APP
 *** author: Quan Huynh
 **************************************************/

/**************************************************
 *** INCLUDES
 **************************************************/
include_once('includes/shared/connection.php');
include_once('includes/shared/functions.php');
ob_start();

// redirect to sign in page if user is not logged in
$user_id = null;

if ($_SESSION['logged_in']) {
	$user_id = $_SESSION['user_id'];
} else if ($_COOKIE['logged_in']) {
	$user_id = $_COOKIE['user_id'];
} else {
	redirect('external_auth.php');
}

// get the action and sub_action value
$action = $_GET['action'];
$sub_action = $_GET['sub_action'];

/**************************************************
 *** LOG OUT
 **************************************************/
if ($action == 'log_out') {
	setcookie('logged_in', '', time() - 3600, '/');

	session_unset();
	session_destroy();

	redirect('external_auth.php');
}

// we check if the user has any client
if ($action != 'submit_new_client') {
	$sql = "SELECT COUNT(id) AS count
			FROM feature_request_app.client
			WHERE user_id = '".$user_id."'";
	$result = mysqli_query($conn, $sql);
	$result = mysqli_fetch_assoc($result);

	// check if the user has any client and if the user just signed up
	if ($result['count'] == 0 && $sub_action == null) {
		redirect('?action=add_client&sub_action=no_client');
	}
}

/**************************************************
 *** AJAX FUNCTIONS
 **************************************************/
if ($action == 'get_table_data') {
	$client_id = $_GET['client_id'];

	$sql = "SELECT rf.*, CONCAT(c.first_name, ' ', c.last_name) AS name
			FROM feature_request_app.requested_feature AS rf
			LEFT JOIN feature_request_app.client AS c
			ON rf.client_id = c.id";

	if ($client_id != '0') {
		$sql .= " WHERE rf.client_id = '".$client_id."'";
	}

	$result = mysqli_query($conn, $sql);


	while ($row = mysqli_fetch_assoc($result)) {
		?>
		<tr>
			<td><?= $row['id'] ?></td>
			<td><a href="#"><?= $row['title'] ?></a></td>
			<td><a href="#"><?= $row['name'] ?></a></td>
			<td><?= $row['target_date'] ?></td>
			<td><?= $row['priority'] ?></td>
		</tr>
		<?php
	}

	// we don't need the rest of the script
	exit();
}

if ($action == 'get_client_priority') {
	// populate the client priority drop-down box
	$client_id = $_GET['client_id'];
	$num_rows  = get_num_requested_features($conn, $client_id);

	// if this client hasn't requested any feature yet,
	// there is only one option for this feature's priority (1)
	if ($num_rows == 0) {
		echo "<option value='1'>1</option>";
	} else {
		for ($i = $num_rows + 1; $i >= 1; $i--) {
			echo "<option value='".$i."'>".$i."</option>";
		}
	}

	// we don't need the rest of the script
	exit();
}

/**************************************************
 *** HEADER
 **************************************************/
include_once('includes/shared/header.php');

/**************************************************
 *** BODY
 **************************************************/
?>
<!-------- NAVBAR -------->
<nav class="navbar navbar-default navbar-static-top">
	<div class="container">
		<div class="navbar-header">
			<!-- Mobile Three-bar Button -->
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse" aria-expanded="false">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>

			<!-- App Title -->
			<a class="navbar-brand" href="<?= $_SERVER['PHP_SELF'] ?>">Feature Request App</a>
		</div><!-- .navbar-header -->

		<div id="navbar-collapse" class="navbar-collapse collapse">
			<!-- Sign In Link -->
			<ul class="nav navbar-nav navbar-right">
				<li><a href="?action=log_out">Log Out</a></li>
			</ul>

			<!-- Search Form -->
			<form class="navbar-form navbar-right" role="search">
				<div class="form-group">
					<input type="text" class="form-control" placeholder="Search">
				</div>
				<button type="submit" class="btn btn-default">Submit</button>
			</form>
		</div><!-- #navbar .navbar-collapse -->
	</div><!-- .container -->
</nav>

<div class="div-spacing"></div>

<?php
/**************************************************
 *** ADD CLIENT
 **************************************************/
if ($action == 'add_client') {
	?>
	<div class="container form">
		<h1>Add New Client</h1>

		<div class="div-spacing"></div>

		<?php
		if ($sub_action == 'no_client') {
			?>
			<div class="alert alert-info"><strong>No worries.</strong> We redirected you here because you need to have at least one client in order to submit a new feature request.</div>
			<?php
		} else if ($sub_action == 'first_request') {
			?>
			<div class="alert alert-success"><strong>Welcome aboard!</strong> Please add a new client first before you start submitting new feature requests.</div>
			<?php
		}
		?>

		<!-- ADD NEW CLIENT FORM -->
		<form method="post" action="?action=submit_new_client<?= ($_GET['back'] != null) ? '&back='.$_GET['back'] : '' ?>">
			<div class="row">
				<!-- FIRST NAME -->
				<div class="form-group col-xs-6 col-sm-6 col-md-6">
					<label for="client_first_name">First Name <span class="red-text">*</span></label>
					<input type="text" class="form-control" name="client_first_name" placeholder="First Name" required>
				</div><!-- .form-group -->

				<!-- LAST NAME -->
				<div class="form-group col-xs-6 col-sm-6 col-md-6">
					<label for="client_last_name">Last Name <span class="red-text">*</span></label>
					<input type="text" class="form-control" name="client_last_name" placeholder="Last Name" required>
				</div><!-- .form-group -->
			</div><!-- .row -->

			<div class="row">
				<!-- EMAIL -->
				<div class="form-group col-xs-12 col-sm-6 col-md-6">
					<label for="client_email">Email <span class="red-text">*</span></label>
					<input type="email" class="form-control" name="client_email" placeholder="Email" required>
				</div><!-- .form-group -->

				<!-- PHONE -->
				<div class="form-group col-xs-12 col-sm-6 col-md-6">
					<label for="client_phone">Phone <span class="red-text">*</span></label>
					<input type="tel" maxlength="10" class="form-control" name="client_phone" placeholder="Phone" required>
				</div><!-- .form-group -->
			</div>

			<hr/>

			<div class="row">
				<!-- ADDRESS LINE 1 -->
				<div class="form-group col-xs-12 col-sm-12 col-md-12">
					<label for="client_address_1">Address Line 1</label>
					<input type="text" class="form-control" name="client_address_1" placeholder="Address Line 1">
				</div><!-- .form-group -->

				<!-- ADDRESS LINE 2 -->
				<div class="form-group col-xs-12 col-sm-12 col-md-12">
					<label for="client_address_2">Address Line 2</label>
					<input type="text" class="form-control" name="client_address_2" placeholder="Address Line 2">
				</div><!-- .form-group -->
			</div>

			<div class="row">
				<!-- CITY -->
				<div class="form-group col-xs-5 col-sm-5 col-md-5">
					<label for="client_city">City</label>
					<input type="text" class="form-control" name="client_city" placeholder="City">
				</div><!-- .form-group -->

				<!-- STATE -->
				<div class="form-group col-xs-2 col-sm-2 col-md-2">
					<label for="client_occupation">State</label>
					<input type="text" maxlength="2" class="form-control" name="client_state" placeholder="State">
				</div><!-- .form-group -->

				<!-- POSTAL CODE -->
				<div class="form-group col-xs-5 col-sm-5 col-md-5">
					<label for="client_postal_code">Postal Code</label>
					<input type="text" maxlength="5" class="form-control" name="client_postal_code" placeholder="Postal Code">
				</div><!-- .form-group -->
			</div>

			<hr/>

			<div class="row">
				<!-- COMPANY -->
				<div class="form-group col-xs-12 col-sm-6 col-md-6">
					<label for="client_company">Company <span class="red-text">*</span></label>
					<input type="text" class="form-control" name="client_company" placeholder="Company" required>
				</div><!-- .form-group -->

				<!-- OCCUPATION -->
				<div class="form-group col-xs-12 col-sm-6 col-md-6">
					<label for="client_occupation">Occupation <span class="red-text">*</span></label>
					<input type="text" class="form-control" name="client_occupation" placeholder="Occupation" required>
				</div><!-- .form-group -->
			</div>

			<div class="clear"></div>

			<!-- METHOD OF CONTACT -->
			<div class="row">
				<div class="form-group col-xs-6 col-sm-6 col-md-6">
					<label for="client_contact_method">Preferred Method of Contact <span class="red-text">*</span></label>
					<select id="feature_client" class="form-control" name="client_contact_method" required onchange="set_client_priority();">
						<option></option>
						<option value="email">Email</option>
						<option value="phone">Phone</option>
					</select>
				</div><!-- .form-group -->

			</div>

			<div class="clear"></div>

			<hr/>

			<!-- ADDITIONAL NOTES -->
			<div class="form-group">
				<label for="client_notes">Additional Notes</label>
				<textarea class="form-control" name="client_notes" rows="3"></textarea>
			</div><!-- .form-group -->

			<br/>

			<!-- SUBMIT -->
			<button type="submit" class="btn-lg btn-primary">Submit</button>
		</form>
	</div>
	<?php
}
?>

<?php
/**************************************************
 *** SUBMIT NEW CLIENT
 **************************************************/
if ($action == 'submit_new_client') {
	// insert new feature record into the database
	if (isset($_POST)) {
		$first_name = mysqli_real_escape_string($conn, $_POST['client_first_name']);
		$last_name  = mysqli_real_escape_string($conn, $_POST['client_last_name']);
		$email      = mysqli_real_escape_string($conn, $_POST['client_email']);
		$phone      = mysqli_real_escape_string($conn, $_POST['client_phone']);
		$address_1  = mysqli_real_escape_string($conn, $_POST['client_address_1']);
		$address_2  = mysqli_real_escape_string($conn, $_POST['client_address_2']);
		$city       = mysqli_real_escape_string($conn, $_POST['client_city']);
		$state      = mysqli_real_escape_string($conn, $_POST['client_state']);
		$p_code     = mysqli_real_escape_string($conn, $_POST['client_postal_code']);
		$company    = mysqli_real_escape_string($conn, $_POST['client_company']);
		$occupation = mysqli_real_escape_string($conn, $_POST['client_occupation']);
		$c_method   = mysqli_real_escape_string($conn, $_POST['client_contact_method']);
		$notes      = mysqli_real_escape_string($conn, $_POST['client_notes']);

		// insert into database
		$sql = "INSERT INTO feature_request_app.client
				SET first_name = '".$first_name."', last_name = '".$last_name."', email = '".$email."',
					phone = '".$phone."', address_1 = '".$address_1."', address_2 = '".$address_2."',
					city = '".$city."', state = '".$state."', postal_code = '".$p_code."',
					company = '".$company."', occupation = '".$occupation."', contact_method = '".$c_method."',
					notes = '".$notes."', user_id = '".$user_id."'";
		$result = mysqli_query($conn, $sql);

		if ($_GET['back'] != null) {
			// redirect to main page with a message
			$back_url = urldecode($_GET['back']);

			redirect($back_url . ((strpos($back_url, '?') == -1) ? '?' : '&') .'sub_action=submit_client_successful');
		} else {
			// redirect to main page with a message
			redirect('?sub_action=submit_client_successful');
		}
	}
}
?>

<?php
/**************************************************
 *** REQUEST FORM
 **************************************************/
if ($action == 'request_form') {
	if (!is_logged_in()) {
		// redirect to login page if not logged in
		redirect('external_auth.php');
	}

	?>
	<div class="container form">
		<h1>Submit New Request</h1>

		<div class="div-spacing"></div>

		<?php
		if ($sub_action == 'submit_client_successful') {
			?>
			<div class="alert alert-success" role="alert"><strong>New Client Added Successfully!</strong></div>
			<?php
		}
		?>

		<!-- SUBMIT NEW FEATURE FORM -->
		<form method="post" action="?action=submit_new_request">
			<!-- FEATURE TITLE -->
			<div class="form-group">
				<label for="feature_title">Title <span class="red-text">*</span></label>
				<input type="text" class="form-control" name="feature_title" placeholder="Title" required>
			</div>

			<div class="row">
				<!-- CLIENT -->
				<div class="form-group col-xs-5 col-sm-3 col-md-6">
					<label for="feature_client">Client <span class="red-text">*</span></label>
					<select id="feature_client" class="form-control" name="feature_client" required onchange="set_client_priority();">
						<option></option>
						<?php
						$sql = "SELECT id, CONCAT(first_name, ' ',last_name) AS name
								FROM feature_request_app.client
								WHERE user_id = '".mysqli_real_escape_string($conn, $user_id)."'";
						$result = mysqli_query($conn, $sql);

						while ($row = mysqli_fetch_assoc($result)) {
						?>
						<option value="<?= $row['id'] ?>"><?= $row['name'] ?></option>
						<?php
						}
						?>
					</select>
				</div>

				<!-- ADD NEW CLIENT -->
				<div class="form-group col-xs-2 col-sm-2 col-md-2">
					<label for="add_client" style="visibility: hidden">A</label>
					<button class="btn btn-default form-control" id="add_client"  onclick="window.location.href = '?action=add_client&back=<?= urlencode('http'.(isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}/{$_SERVER['REQUEST_URI']}") ?>';">
						<span class="glyphicon glyphicon-plus"></span>
					</button>
				</div>
			</div>

			<div class="clear"></div>

			<div class="row">
				<!-- CLIENT PRIORITY -->
				<div class="form-group col-xs-5 col-sm-3 col-md-3">
					<label for="feature_priority">Client Priority <span class="red-text">*</span></label>
					<select id="feature_priority" class="form-control" name="feature_priority" required>
						<?php
						/* priority options are populated by AJAX, determined by client */
						?>
					</select>
				</div><!-- .form-group -->

				<!-- PRODUCT AREA -->
				<div class="form-group col-xs-5 col-sm-3 col-md-3">
					<label for="feature_area">Product Area <span class="red-text">*</span></label>
					<select name="feature_area" class="form-control" required>
						<option></option>
						<?php
						$sql = "SELECT *
								FROM feature_request_app.product_area
								WHERE active = '1'";
						$result = mysqli_query($conn, $sql);

						while ($row = mysqli_fetch_assoc($result)) {
						?>
						<option value="<?= $row['id'] ?>"><?= $row['name'] ?></option>
						<?php
						}
						?>
					</select>
				</div><!-- .form-group -->
			</div><!-- .row -->

			<!-- TARGET DATE -->
			<div class="form-group">
				<label for="feature_client">Target Date <span class="red-text">*</span></label>
				<input type="date" class="form-control" name="feature_target_date" placeholder="mm/dd/yyyy" required>
			</div><!-- .form-group -->

			<!-- TICKET URL -->
			<div class="form-group">
				<label for="feature_url">Ticket URL</label>
				<input type="url" class="form-control" name="feature_url" placeholder="Ticket URL">
			</div><!-- .form-group -->

			<!-- FEATURE DESCRIPTION -->
			<div class="form-group">
				<label for="feature_description">Description <span class="red-text">*</span></label>
				<textarea class="form-control" name="feature_description" rows="3" required></textarea>
			</div><!-- .form-group -->

			<!-- SUBMIT -->
			<button type="submit" class="btn-lg btn-primary">Submit</button>
		</form>
	</div><!-- .container .form -->
	<?php
}
?>

<?php
/**************************************************
 *** SUBMIT NEW REQUEST
 **************************************************/
if ($action == 'submit_new_request') {
	// insert new feature record into the database
	if (isset($_POST)) {
		$title     = mysqli_real_escape_string($conn, $_POST['feature_title']);
		$client_id = mysqli_real_escape_string($conn, $_POST['feature_client']);
		$priority  = mysqli_real_escape_string($conn, $_POST['feature_priority']);
		$area      = mysqli_real_escape_string($conn, $_POST['feature_area']);
		$date      = mysqli_real_escape_string($conn, $_POST['feature_target_date']);
		$url       = mysqli_real_escape_string($conn, $_POST['feature_url']);
		$desc      = mysqli_real_escape_string($conn, $_POST['feature_description']);

		$num_requested_features = get_num_requested_features($conn, $client_id);

		// but first, we need to check if the priority the client set for this feature
		// is less than or equal to the number of requested features of this client
		// if yes, then we need to lower the priorities of all the features which
		// come after the newly created one
		if ($priority <= $num_requested_features) {
			$sql = "UPDATE feature_request_app.requested_feature
					SET priority = priority + 1
					WHERE client_id = '".$client_id."'
					AND priority >= '".$priority."'";
			mysqli_query($conn, $sql);
		}

		// insert into database
		$sql = "INSERT INTO feature_request_app.requested_feature
				SET title = '".$title."', client_id = '".$client_id."', priority = '".$priority."',
					target_date = '".$date."', ticket_url = '".$url."', description = '".$desc."',
					prod_area_id = '".$area."', user_id = '".$user_id."'";
		$result = mysqli_query($conn, $sql);

		// redirect to main page with a message
		redirect('?sub_action=submit_request_successful');
	}
}
?>

<?php
/**************************************************
 *** VIEW REQUEST
 **************************************************/
if ($action == 'submit_new_request') {

}
?>

<?php
/**************************************************
 *** SHOW REQUESTED FEATURES TABLE
 **************************************************/
if ($action == null) {
	?>
	<!-------- REQUESTED FEATURES TABLE -------->
	<div class="container">
		<h1>Requested Features</h1>

		<div class="div-spacing"></div>

		<?php
		if ($sub_action == 'submit_request_successful') {
			// show a message after submitted a new request successfully
			?>
			<div class="alert alert-success" role="alert"><strong>Feature Submitted Successfully!</strong></div>
			<?php
		} else if ($sub_action == 'submit_client_successful') {
			// show a message after added a new client successfully
			?>
			<div class="alert alert-success" role="alert"><strong>Client Added Successfully!</strong></div>
			<?php
		}
		?>

		<div class="panel panel-default">
			<div class="panel-body">
				<p>
					Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
				</p>

				<div class="row">
					<!-- Select Client -->
					<div class="col-xs-5 col-sm-3 col-md-2">
						<select id="client_filter" class="form-control" onchange="set_table_data();">
							<option value="0">All Clients</option>
							<?php
							$sql = "SELECT id, CONCAT(first_name, ' ',last_name) AS name
									FROM feature_request_app.client
									WHERE user_id = '".mysqli_real_escape_string($conn, $user_id)."'";
							$result = mysqli_query($conn, $sql);

							while ($row = mysqli_fetch_assoc($result)) {
								?>
								<option value="<?= $row['id'] ?>"><?= $row['name'] ?></option>
								<?php
							}
							?>
						</select>
					</div><!-- .col-xs-5 .col-sm-3 .col-md-2 -->

					<!-- Submit New Request Button -->
					<button type="button" class="btn btn-primary pull-right" onclick="window.location.href = '?action=request_form';" style="margin-right: 15px;"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Submit New Request</button>
				</div><!-- .row -->
			</div><!-- .panel-body -->

			<!-- Table -->
			<table id="feature_table" class="table">
				<thead>
				<tr>
					<th><a href="#">ID</a></th>
					<th><a href="#">Title</a></th>
					<th><a href="#">Client</a></th>
					<th><a href="#">Target Date</a></th>
					<th><a href="#">Priority</a></th>
				</tr>
				</thead>
				<tbody>
					<?php
					$sql = "SELECT rf.*, CONCAT(c.first_name, ' ', c.last_name) AS name
							FROM feature_request_app.requested_feature AS rf
							LEFT JOIN feature_request_app.client AS c
							ON rf.client_id = c.id";

					$result = mysqli_query($conn, $sql);

					while ($row = mysqli_fetch_assoc($result)) {
						?>
						<tr>
							<td><?= $row['id'] ?></td>
							<td><a href="#"><?= $row['title'] ?></a></td>
							<td><a href="#"><?= $row['name'] ?></a></td>
							<td><?= $row['target_date'] ?></td>
							<td><?= $row['priority'] ?></td>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>
		</div>
	</div>
	<?php
}

/**************************************************
 *** FOOTER
 **************************************************/
include_once('includes/shared/footer.php');
?>