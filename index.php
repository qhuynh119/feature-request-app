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
		redirect('?action=client_form&sub_action=no_client');
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
			ON rf.client_id = c.id
			WHERE rf.user_id = '".$user_id."'";

	if ($client_id != '0') {
		$sql .= " AND rf.client_id = '".$client_id."'";
	}

	$sql .= " ORDER BY name, rf.priority";

	$result = mysqli_query($conn, $sql);
	$num_rows = mysqli_num_rows($result);

	if ($num_rows == 0) {
		?>
		<tr>
			<td colspan="5" style="text-align: center;"><i>No available requests</i></td>
		</tr>
		<?php
	} else {
		while ($row = mysqli_fetch_assoc($result)) {
			?>
			<tr>
				<td><?= $row['id'] ?></td>
				<td><a href="?action=view_request&req_id=<?= $row['id'] ?>"><?= $row['title'] ?></a></td>
				<td><a href="?action=view_client&client_id=<?= $row['client_id'] ?>"><?= $row['name'] ?></a></a></td>
				<td><?= date_format(date_create($row['target_date']), 'F d, Y') ?></td>
				<td><span class="badge"><?= $row['priority'] ?></span></td>
			</tr>
			<?php
		}
	}

	// we don't need the rest of the script
	exit();
}

if ($action == 'get_client_priority') {
	// populate the client priority drop-down box
	$client_id = $_GET['client_id'];
	$req_id    = $_GET['req_id'];
	$num_rows  = get_num_requested_features($conn, $client_id);

	// if this client hasn't requested any feature yet,
	// there is only one option for this feature's priority (1)
	if ($num_rows == 0) {
		echo "<option value='1'>1</option>";
	} else {
		// if the user is editing a request,
		// we don't allow them to change the priority
		// of that request to be lower than the existing
		// lowest priority
		if ($sub_action == 'edit_request') {
			$i = $num_rows;

			$sql = "SELECT priority
					FROM feature_request_app.requested_feature
					WHERE id = '".$req_id."'";
			$result = mysqli_query($conn, $sql);
			$result = mysqli_fetch_assoc($result);
		} else {
			$i = $num_rows + 1;
		}

		for ($i; $i >= 1; $i--) {
			echo "<option value='".$i."'".(($i == $result['priority']) ? 'selected' : '').">".$i."</option>";
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
	<div class="container-fluid">
		<div class="navbar-header">
			<!-- Mobile Three-bar Button -->
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse" aria-expanded="false">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>

			<!-- App Title -->
			<a class="navbar-brand" href="<?= $_SERVER['PHP_SELF'] ?>">FEATURE REQUEST APP<br/><small style="font-weight: lighter; font-size: 12px;">by Quan K. Huynh</small></a>
		</div><!-- .navbar-header -->

		<div id="navbar-collapse" class="navbar-collapse collapse">
			<!-- Sign In Link -->
			<ul class="nav navbar-nav navbar-right">
				<li><a href="<?= $_SERVER['PHP_SELF'] ?>">REQUESTS</a></li>
				<li><a href="?action=show_all_clients">CLIENTS</a></li>
				<li><a href="?action=log_out">LOG OUT</a></li>
			</ul>
		</div><!-- #navbar .navbar-collapse -->

		<div class="navbar-title">
			<?php
			$title = '';

			switch ($action) {
				case 'request_form':
					if ($sub_action == 'edit_request') {
						$title = "EDIT REQUEST <small>#</small>".$_GET['request_id'];
					} else {
						$title = 'SUBMIT REQUEST';
					}
					break;
				case 'client_form':
					if ($sub_action == 'edit_client') {
						$title = "EDIT CLIENT <small>#</small>".$_GET['client_id'];
					} else {
						$title = 'ADD CLIENT';
					}
					break;
				case 'view_request':
					$title = "REQUEST <small>#</small>".$_GET['request_id'];
					break;
				case 'view_client':
					$title = "CLIENT <small>#</small>".$_GET['client_id'];
					break;
				case 'show_all_clients':
					$title = 'CLIENTS';
					break;
				default:
					$title = 'REQUESTS';
					break;
			}

			echo $title;
			?>
		</div>
	</div><!-- .container -->
</nav>

<?php
/**************************************************
 *** CLIENT FORM
 **************************************************/
if ($action == 'client_form') {
	?>
	<div class="container form">
		<?php
		$edit_client = false;

		if ($sub_action == 'no_client') {
			?>
			<div class="alert alert-info"><strong>No worries.</strong> We redirected you here because you need to have at least one client in order to submit a new feature request.</div>
			<?php
		} else if ($sub_action == 'first_request') {
			?>
			<div class="alert alert-success"><strong>Welcome aboard!</strong> Please add a new client first before you start submitting new feature requests.</div>
			<?php
		} else if ($sub_action == 'edit_client') {
			$edit_client = true;
			$client_id = $_GET['client_id'];

			$sql = "SELECT *
					FROM feature_request_app.client
					WHERE id = '".mysqli_real_escape_string($conn, $client_id)."'";
			$result = mysqli_query($conn, $sql);
			$result = mysqli_fetch_assoc($result);

			$first_name = $result['first_name'];
			$last_name  = $result['last_name'];
			$company    = $result['company'];
			$email      = $result['email'];
			$phone      = $result['phone'];
			$occupation = $result['occupation'];
			$address_1  = $result['address_1'];
			$address_2  = $result['address_2'];
			$city       = $result['city'];
			$state      = $result['state'];
			$pcode      = $result['postal_code'];
			$notes      = $result['notes'];
			$contact    = $result['contact_method'];
		}
		?>

		<!-- ADD NEW CLIENT FORM -->
		<form method="post" action="?action=submit_client<?= ($edit_client) ? '&sub_action=edit_client&client_id='.$client_id : '' ?><?= ($_GET['back'] != null) ? '&back='.$_GET['back'] : '' ?>">
			<div class="row">
				<!-- FIRST NAME -->
				<div class="form-group col-xs-6 col-sm-6 col-md-6">
					<label for="client_first_name">First Name <span class="red-text">*</span></label>
					<input type="text" class="form-control" name="client_first_name" placeholder="First Name" value="<?= $first_name ?>" required>
				</div><!-- .form-group -->

				<!-- LAST NAME -->
				<div class="form-group col-xs-6 col-sm-6 col-md-6">
					<label for="client_last_name">Last Name <span class="red-text">*</span></label>
					<input type="text" class="form-control" name="client_last_name" placeholder="Last Name" value="<?= $last_name ?>" required>
				</div><!-- .form-group -->
			</div><!-- .row -->

			<div class="row">
				<!-- EMAIL -->
				<div class="form-group col-xs-12 col-sm-6 col-md-6">
					<label for="client_email">Email <span class="red-text">*</span></label>
					<input type="email" class="form-control" name="client_email" placeholder="Email" value="<?= $email ?>" required>
				</div><!-- .form-group -->

				<!-- PHONE -->
				<div class="form-group col-xs-12 col-sm-6 col-md-6">
					<label for="client_phone">Phone <span class="red-text">*</span></label>
					<input type="tel" pattern="^(\([0-9]{3}\) |[0-9]{3}-)[0-9]{3}-[0-9]{4}$" maxlength="14" class="form-control" name="client_phone" placeholder="(123) 456-7890" value="<?= $phone ?>" required>
				</div><!-- .form-group -->
			</div><!-- .row -->

			<hr/>

			<div class="row">
				<!-- ADDRESS LINE 1 -->
				<div class="form-group col-xs-12 col-sm-12 col-md-12">
					<label for="client_address_1">Address Line 1</label>
					<input type="text" class="form-control" name="client_address_1" placeholder="Address Line 1" value="<?= $address_1 ?>">
				</div><!-- .form-group -->

				<!-- ADDRESS LINE 2 -->
				<div class="form-group col-xs-12 col-sm-12 col-md-12">
					<label for="client_address_2">Address Line 2</label>
					<input type="text" class="form-control" name="client_address_2" placeholder="Address Line 2" value="<?= $address_2 ?>">
				</div><!-- .form-group -->
			</div><!-- .row -->

			<div class="row">
				<!-- CITY -->
				<div class="form-group col-xs-5 col-sm-5 col-md-5">
					<label for="client_city">City</label>
					<input type="text" class="form-control" name="client_city" placeholder="City" value="<?= $city ?>">
				</div><!-- .form-group -->

				<!-- STATE -->
				<div class="form-group col-xs-2 col-sm-2 col-md-2">
					<label for="client_occupation">State</label>
					<input type="text" maxlength="2" class="form-control" name="client_state" placeholder="State" value="<?= $state ?>">
				</div><!-- .form-group -->

				<!-- POSTAL CODE -->
				<div class="form-group col-xs-5 col-sm-5 col-md-5">
					<label for="client_postal_code">Postal Code</label>
					<input type="text" maxlength="5" class="form-control" name="client_postal_code" placeholder="Postal Code" value="<?= $pcode ?>">
				</div><!-- .form-group -->
			</div><!-- .row -->

			<hr/>

			<div class="row">
				<!-- COMPANY -->
				<div class="form-group col-xs-12 col-sm-6 col-md-6">
					<label for="client_company">Company <span class="red-text">*</span></label>
					<input type="text" class="form-control" name="client_company" placeholder="Company" value="<?= $company ?>" required>
				</div><!-- .form-group -->

				<!-- OCCUPATION -->
				<div class="form-group col-xs-12 col-sm-6 col-md-6">
					<label for="client_occupation">Occupation <span class="red-text">*</span></label>
					<input type="text" class="form-control" name="client_occupation" placeholder="Occupation" value="<?= $occupation ?>" required>
				</div><!-- .form-group -->
			</div><!-- .row -->

			<div class="clear"></div>

			<!-- METHOD OF CONTACT -->
			<div class="row">
				<div class="form-group col-xs-6 col-sm-6 col-md-6">
					<label for="client_contact_method">Preferred Method of Contact <span class="red-text">*</span></label>
					<select id="feature_client" class="form-control" name="client_contact_method" required onchange="set_client_priority();">
						<option></option>
						<option value="Email" <?= ($contact == 'Email') ? 'selected' : '' ?>>Email</option>
						<option value="Phone" <?= ($contact == 'Phone') ? 'selected' : '' ?>>Phone</option>
					</select>
				</div><!-- .form-group -->
			</div><!-- .row -->

			<div class="clear"></div>

			<hr/>

			<!-- ADDITIONAL NOTES -->
			<div class="form-group">
				<label for="client_notes">Additional Notes</label>
				<textarea class="form-control" name="client_notes" rows="3"><?= $notes ?></textarea>
			</div><!-- .form-group -->

			<br/>

			<!-- SUBMIT -->
			<button type="submit" class="btn-lg btn-primary">Submit</button>
		</form>
	</div><!-- .container .form -->
	<?php
}
?>

<?php
/**************************************************
 *** SUBMIT NEW or UPDATE CLIENT
 **************************************************/
if ($action == 'submit_client') {
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

		if ($sub_action == 'edit_client') {
			// update existing client
			$modified   = date('Y-m-d h:i:sa');
			$client_id  = $_GET['client_id'];

			$sql = "UPDATE feature_request_app.client
					SET first_name = '".$first_name."', last_name = '".$last_name."', email = '".$email."',
						phone = '".$phone."', address_1 = '".$address_1."', address_2 = '".$address_2."',
						city = '".$city."', state = '".$state."', postal_code = '".$p_code."',
						company = '".$company."', occupation = '".$occupation."', contact_method = '".$c_method."',
						notes = '".$notes."', user_id = '".$user_id."', modified = '".$modified."'
					WHERE id = '".$client_id."'";
			mysqli_query($conn, $sql);

			redirect('?action=view_client&sub_action=edit_client_successful&client_id='.$client_id);
		} else {
			// insert new client record into the database
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

	if ($sub_action == 'edit_request') {
		$edit_request = true;
		$req_id = $_GET['request_id'];

		$sql = "SELECT *
				FROM feature_request_app.requested_feature
				WHERE id = '".mysqli_real_escape_string($conn, $req_id)."'";
		$result = mysqli_query($conn, $sql);
		$result = mysqli_fetch_assoc($result);

		$req_title   = $result['title'];
		$client_id   = $result['client_id'];
		$priority    = $result['priority'];
		$target_date = $result['target_date'];
		$prod_area   = $result['prod_area_id'];
		$url         = $result['ticket_url'];
		$desc        = $result['description'];
	}
	?>

	<!-- SUBMIT REQUEST FORM -->
	<div class="container form">
		<?php
		if ($sub_action == 'submit_client_successful') {
			?>
			<div class="alert alert-success" role="alert"><strong>New client added successfully!</strong></div>
			<?php
		}
		?>

		<!-- SUBMIT NEW FEATURE FORM -->
		<form method="post" action="?action=submit_new_request<?= ($edit_request) ? '&sub_action=edit_request&req_id='.$req_id : '' ?>">
			<!-- FEATURE TITLE -->
			<div class="form-group">
				<label for="feature_title">Title <span class="red-text">*</span></label>
				<input type="text" class="form-control" name="feature_title" placeholder="Title" value="<?= htmlentities($req_title) ?>" required>
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
					<button class="btn btn-default form-control" id="add_client" onclick="window.location.href = '?action=client_form&back=<?= back_url() ?>';">
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

				<?php
				if ($sub_action == 'edit_request') {
					?>
					<script>
						// set the client in case the user is editing a request
						$('#feature_client').val('<?= $client_id ?>');

						$(document).ready(function () {
							// we call this function here to populate priority field
							// manually in case user edit a request
							set_client_priority(true, <?= $req_id ?>);
						});
					</script>
					<?php
				}
				?>

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
							<option value="<?= $row['id'] ?>" <?= ($row['id'] == $prod_area) ? 'selected' : '' ?>><?= $row['name'] ?></option>
						<?php
						}
						?>
					</select>
				</div><!-- .form-group -->
			</div><!-- .row -->

			<!-- TARGET DATE -->
			<div class="form-group">
				<label for="feature_client">Target Date <span class="red-text">*</span></label>
				<input type="date" class="form-control" name="feature_target_date" placeholder="mm/dd/yyyy" value="<?= $target_date ?>" required>
			</div><!-- .form-group -->

			<!-- TICKET URL -->
			<div class="form-group">
				<label for="feature_url">Ticket URL</label>
				<input type="url" class="form-control" name="feature_url" placeholder="Ticket URL" <?= $url ?>>
			</div><!-- .form-group -->

			<!-- FEATURE DESCRIPTION -->
			<div class="form-group">
				<label for="feature_description">Description <span class="red-text">*</span></label>
				<textarea class="form-control" name="feature_description" rows="3" required><?= $desc ?></textarea>
			</div><!-- .form-group -->

			<br/>

			<!-- SUBMIT -->
			<button type="submit" class="btn-lg btn-primary">Submit</button>
		</form>
	</div><!-- .container .form -->
	<?php
}
?>

<?php
/**************************************************
 *** SUBMIT NEW REQUEST or UPDATE REQUEST
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

		if ($sub_action == 'edit_request') {
			// update existing request
			$modified   = date('Y-m-d h:i:sa');
			$req_id  = $_GET['req_id'];

			// first we get the priority of the request being edited
			$sql = "SELECT priority
					FROM feature_request_app.requested_feature
					WHERE id = '".$req_id."'";
			$result = mysqli_query($conn, $sql);
			$result = mysqli_fetch_assoc($result);

			// and then we start swapping the priorities of the request being edited
			// and the request having the priority that we want to assign to the request being edited
			$sql = "UPDATE feature_request_app.requested_feature
					SET priority = '".$result['priority']."'
					WHERE priority = '".$priority."'";
			mysqli_query($conn, $sql);

			// the rest is self-explanatory
			$sql = "UPDATE feature_request_app.requested_feature
					SET title = '".$title."', client_id = '".$client_id."', priority = '".$priority."',
						prod_area_id = '".$area."', target_date = '".$date."', ticket_url = '".$url."',
						description = '".$desc."'
					WHERE id = '".$req_id."'";
			mysqli_query($conn, $sql);

			redirect('?action=view_request&sub_action=edit_request_successful&req_id='.$req_id);
		} else {
			// insert into database
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

			$sql = "INSERT INTO feature_request_app.requested_feature
					SET title = '" . $title . "', client_id = '" . $client_id . "', priority = '" . $priority . "',
						target_date = '" . $date . "', ticket_url = '" . $url . "', description = '" . $desc . "',
						prod_area_id = '" . $area . "', user_id = '" . $user_id . "'";
			$result = mysqli_query($conn, $sql);

			// redirect to main page with a message
			redirect('?sub_action=submit_request_successful');
		}
	}
}
?>

<?php
/**************************************************
 *** VIEW REQUEST
 **************************************************/
if ($action == 'view_request') {
	$req_id = $_GET['req_id'];

	$sql = "SELECT rf.*, pa.name AS prod_area_name, CONCAT(c.first_name, ' ', c.last_name) AS name
			FROM feature_request_app.requested_feature AS rf
			LEFT JOIN feature_request_app.client AS c
			ON rf.client_id = c.id
			LEFT JOIN feature_request_app.product_area AS pa
			ON rf.prod_area_id = pa.id
			WHERE rf.id = '".$req_id."'";

	$result = mysqli_query($conn, $sql);
	$result = mysqli_fetch_assoc($result);
	?>
	<div class="container form">
		<?php
		if ($sub_action == 'edit_request_successful') {
			?>
			<div class="alert alert-success"><strong>Updated request #<?= $_GET['req_id'] ?> successfully!</strong></div>
			<?php
		}
		?>

		<ul class="list-group">
			<li class="list-group-item list-group-item-info">
				<h6>TITLE</h6>
				<h4><?= $result['title'] ?></h4>
			</li>
		</ul>

		<!-- TABLE -->
		<table id="feature_table" class="table">
			<tbody>
				<tr>
					<td><strong>Client</strong></td>
					<td><a href="?action=view_client&client_id=<?= $result['client_id'] ?>"><?= $result['name'] ?></a></td>
				</tr>
				<tr>
					<td><strong>Priority</strong></td>
					<td><span class="badge"><?= $result['priority'] ?></span></td>
				</tr>
				<tr>
					<td><strong>Target Date</strong></td>
					<td><?= date_format(date_create($result['target_date']), 'F d, Y') ?></td>
				</tr>
				<tr>
					<td><strong>Product Area</strong></td>
					<td><?= $result['prod_area_name'] ?></td>
				</tr>
				<tr <?= ($result['ticket_url'] == null) ? 'hidden' : '' ?>>
					<td><strong>Ticket URL</strong></td>
					<td><a href="<?= $result['ticket_url'] ?>"><?= $result['ticket_url'] ?></td>
				</tr>
				<tr>
					<td><strong>Created By</strong></td>
					<td><?= date_format(date_create($result['created']), 'F d, Y h:i:s A') ?></td>
				</tr>
				<tr>
					<td><strong>Modified By</strong></td>
					<td><?= ($result['modified'] == null) ? 'N/A' : date_format(date_create($result['modified']), 'F d, Y h:i:s A') ?></td>
				</tr>
				<tr>
					<td><strong>Description</strong></td>
					<td><?= $result['description'] ?></td>
				</tr>
			</tbody>
		</table>

		<button class="btn btn-primary" onclick="window.location.href = '?action=request_form&sub_action=edit_request&request_id=<?= $req_id ?>'">Edit Request</button>

		<button class="btn btn-danger" onclick="if (confirm('Are you sure you want to delete this feature request? This action cannot be undone.')) window.location.href = '?action=delete_request&request_id=<?= $req_id ?>';">Delete Request</button>
	</div><!-- .container .form -->
	<?php
}
?>

<?php
/**************************************************
 *** VIEW CLIENT
 **************************************************/
if ($action == 'view_client') {
	$client_id = $_GET['client_id'];

	$sql = "SELECT *, CONCAT(first_name, ' ', last_name) AS name, CONCAT(address_1, '<br/>', address_2) AS address
			FROM feature_request_app.client
			WHERE id = '".$client_id."'";

	$result = mysqli_query($conn, $sql);
	$result = mysqli_fetch_assoc($result);
	?>
	<div class="container form">
		<?php
			if ($sub_action == 'edit_client_successful') {
			?>
			<div class="alert alert-success"><strong>Updated client #<?= $_GET['client_id'] ?> successfully!</strong></div>
			<?php
		}
		?>

		<ul class="list-group">
			<li class="list-group-item list-group-item-info">
				<h6>FULL NAME</h6>
				<h4><?= $result['name'] ?></h4>
			</li>
		</ul>

		<!-- TABLE -->
		<table id="feature_table" class="table">
			<tbody>
				<tr>
					<td><strong>Email</strong></td>
					<td><a href="mailto:<?= $result['email'] ?>"><?= $result['email'] ?></a></td>
				</tr>
				<tr>
					<td><strong>Phone</strong></td>
					<td><a href="tel:<?= $result['phone'] ?>"><?= $result['phone'] ?></td>
				</tr>
				<tr>
					<td><strong>Occupation</strong></td>
					<td><?= $result['occupation'] ?></td>
				</tr>
				<tr <?= ($result['address'] == '<br/>') ? 'hidden' : '' ?>>
					<td><strong>Address</strong></td>
					<td><?= $result['address'] ?></td>
				</tr>
				<tr <?= ($result['city'] == null) ? 'hidden' : '' ?>>
					<td><strong>City</strong></td>
					<td><?= $result['city'] ?></td>
				</tr>
				<tr <?= ($result['state'] == null) ? 'hidden' : '' ?>>
					<td><strong>State</strong></td>
					<td><?= $result['state'] ?></td>
				</tr>
				<tr <?= ($result['postal_code'] == null) ? 'hidden' : '' ?>>
					<td><strong>Postal Code</strong></td>
					<td><?= $result['postal_code'] ?></td>
				</tr>
				<tr>
					<td><strong>Preferred</strong></td>
					<td><?= $result['contact_method'] ?></td>
				</tr>
				<tr>
					<td><strong>Created By</strong></td>
					<td><?= date_format(date_create($result['created']), 'F d, Y h:i:s A') ?></td>
				</tr>
				<tr>
					<td><strong>Modified By</strong></td>
					<td><?= ($result['modified'] == null) ? 'N/A' : date_format(date_create($result['modified']), 'F d, Y h:i:s A') ?></td>
				</tr>
				<tr <?= ($result['notes'] == null) ? 'hidden' : '' ?>>
					<td><strong>Notes</strong></td>
					<td><?= $result['notes'] ?></td>
				</tr>
				<?php
				$sql = "SELECT COUNT(id) AS count
						FROM feature_request_app.requested_feature
						WHERE client_id = '".$client_id."'";
				$result = mysqli_query($conn, $sql);
				$result = mysqli_fetch_assoc($result);
				?>
				<tr>
					<td><strong># of Requested Features</strong></td>
					<td><?= $result['count'] ?></td>
				</tr>
			</tbody>
		</table>

		<button class="btn btn-primary" onclick="window.location.href = '?action=client_form&sub_action=edit_client&client_id=<?= $client_id ?>'">Edit Client</button>

		<button class="btn btn-danger" onclick="if (confirm('All related requested features will also be deleted. Are you sure you want to delete this client?')) window.location.href = '?action=delete_client&client_id=<?= $client_id ?>';">Delete Client</button>
	</div><!-- .container .form -->
	<?php
}
?>

<?php
/**************************************************
 *** DELETE REQUEST
 **************************************************/
if ($action == 'delete_request') {
	$req_id = $_GET['request_id'];

	$sql = "SELECT client_id, priority
			FROM feature_request_app.requested_feature
			WHERE id = '".$req_id."'";
	$result = mysqli_query($conn, $sql);
	$result = mysqli_fetch_assoc($result);

	$client_id = $result['client_id'];
	$priority  = $result['priority'];

	// delete request
	$sql = "DELETE FROM feature_request_app.requested_feature
			WHERE id = '".$req_id."'";
	mysqli_query($conn, $sql);

	// update priorities of the remaining requests
	$sql = "UPDATE feature_request_app.requested_feature
			SET priority = priority - 1
			WHERE client_id = '".$client_id."'
			AND user_id = '".$user_id."'
			AND priority > '".$priority."'";
	mysqli_query($conn, $sql);

	// redirect to main page with a message
	redirect('?sub_action=delete_request_successful&request_id='.$req_id);
}
?>

<?php
/**************************************************
 *** DELETE CLIENT
 **************************************************/
if ($action == 'delete_client') {
	$client_id = $_GET['client_id'];

	// delete request
	$sql = "DELETE FROM feature_request_app.client
			WHERE id = '".$client_id."'";
	var_dump($sql);
	mysqli_query($conn, $sql);

	// redirect to main page with a message
	redirect('?action=show_all_clients&sub_action=delete_client_successful&client_id='.$client_id);
}
?>

<?php
/**************************************************
 *** SHOW CLIENTS TABLE
 **************************************************/
if ($action == 'show_all_clients') {
	?>
	<!-------- CLIENTS TABLE -------->
	<div class="container">
		<?php
		if ($sub_action == 'submit_request_successful') {
			// show a message after submitted a new request successfully
			?>
			<div class="alert alert-success" role="alert"><strong>Feature was successfully submitted!</strong></div>
			<?php
		} else if ($sub_action == 'submit_client_successful') {
			// show a message after added a new client successfully
			?>
			<div class="alert alert-success" role="alert"><strong>New client added successfully!</strong></div>
			<?php
		} else if ($sub_action == 'delete_request_successful') {
			// show a message after deleted a request successfully
			?>
			<div class="alert alert-success" role="alert"><strong>Deleted request ID#<?= $_GET['request_id'] ?> successfully!</strong></div>
			<?php
		} else if ($sub_action == 'delete_client_successful') {
			// show a message after deleted a client successfully
			?>
			<div class="alert alert-success" role="alert"><strong>Deleted client ID#<?= $_GET['client_id'] ?> successfully!</strong></div>
			<?php
		}
		?>

		<div class="panel panel-default">
			<div class="panel-body">
				<p>
					Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
				</p>

				<div class="row">
					<!-- Submit New Request Button -->
					<button type="button" class="btn btn-primary pull-left" onclick="window.location.href = '?action=client_form&back=<?= back_url() ?>';" style="margin-left: 15px;"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> &nbsp;ADD CLIENT</button>
				</div><!-- .row -->
			</div><!-- .panel-body -->

			<!-- Table -->
			<table id="feature_table" class="table">
				<thead>
					<tr>
						<th><a href="#">ID</a></th>
						<th><a href="#">Full Name</a></th>
						<th><a href="#">Company</a></th>
						<th><a href="#">Contact</a></th>
						<th><a href="#">Option</a></th>
					</tr>
				</thead>

				<tbody>
				<?php
				$sql = "SELECT id, CONCAT(first_name, ' ', last_name) AS name, company, email, phone, contact_method
						FROM feature_request_app.client
						WHERE user_id = '".mysqli_real_escape_string($conn, $user_id)."'";

				$result = mysqli_query($conn, $sql);
				$num_rows = mysqli_num_rows($result);

				if ($num_rows == 0) {
					?>
					<tr>
						<td colspan="5" style="text-align: center;"><i>No available clients</i></td>
					</tr>
					<?php
				} else {
					$result = mysqli_query($conn, $sql);

					while ($row = mysqli_fetch_assoc($result)) {
						?>
						<tr>
							<td><?= $row['id'] ?></td>
							<td><a href="?action=view_client&client_id=<?= $row['id'] ?>&back=<?= back_url() ?>"><?= $row['name'] ?></a></td>
							<td><?= $row['company'] ?></a></td>
							<?php
							if ($row['contact_method'] == 'Email') {
								?>
								<td><a href="mailto:<?= $row['email'] ?>"><?= $row['email'] ?></a></td>
								<?php
							} else if ($row['contact_method'] == 'Phone') {
								?>
								<td><a href="tel:<?= $row['phone'] ?>"><?= $row['phone'] ?></a></td>
							<?php
							}
							?>
							<td><a href="?client_selection_id=<?= $row['id'] ?>">View Requests</a></td>
						</tr>
						<?php
					}
				}
				?>
				</tbody>
			</table>
		</div>
	</div>
	<?php
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
		<?php
		if ($sub_action == 'submit_request_successful') {
			// show a message after submitted a new request successfully
			?>
			<div class="alert alert-success" role="alert"><strong>Feature was successfully submitted!</strong></div>
			<?php
		} else if ($sub_action == 'submit_client_successful') {
			// show a message after added a new client successfully
			?>
			<div class="alert alert-success" role="alert"><strong>New client added successfully!</strong></div>
			<?php
		} else if ($sub_action == 'delete_request_successful') {
			// show a message after deleted a request successfully
			?>
			<div class="alert alert-success" role="alert"><strong>Deleted request ID#<?= $_GET['request_id'] ?> successfully!</strong></div>
			<?php
		} else if ($sub_action == 'delete_client_successful') {
			// show a message after deleted a client successfully
			?>
			<div class="alert alert-success" role="alert"><strong>Deleted client ID#<?= $_GET['client_id'] ?> successfully!</strong></div>
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

							if (isset($_GET['client_selection_id'])) {
								$client_id = $_GET['client_selection_id'];
							}

							while ($row = mysqli_fetch_assoc($result)) {
								?>
								<option value="<?= $row['id'] ?>" <?= ($row['id'] == $client_id) ? 'selected' : '' ?>><?= $row['name'] ?></option>
								<?php
							}
							?>
						</select>

						<script>
							$(document).ready(function () {
								// we call this function here to populate requests from a specific client
								// in case the user clicked 'View Requests' link in Clients page
								set_table_data();
							});
						</script>
					</div><!-- .col-xs-5 .col-sm-3 .col-md-2 -->

					<!-- Submit New Request Button -->
					<button type="button" class="btn btn-primary pull-right" onclick="window.location.href = '?action=request_form';" style="margin-right: 15px;"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> &nbsp;SUBMIT REQUEST</button>
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
							ON rf.client_id = c.id
							WHERE rf.user_id = '".$user_id."'
							ORDER BY name, rf.priority";

					$result = mysqli_query($conn, $sql);
					$num_rows = mysqli_num_rows($result);

					if ($num_rows == 0) {
						?>
						<tr>
							<td colspan="5" style="text-align: center;"><i>No available requests</i></td>
						</tr>
						<?php
					} else {
						$result = mysqli_query($conn, $sql);

						while ($row = mysqli_fetch_assoc($result)) {
							?>
							<tr>
								<td><?= $row['id'] ?></td>
								<td><a href="?action=view_request&req_id=<?= $row['id'] ?>&back=<?= back_url() ?>"><?= $row['title'] ?></a></td>
								<td><a href="?action=view_client&client_id=<?= $row['client_id'] ?>&back=<?= back_url() ?>"><?= $row['name'] ?></a></td>
								<td><?= date_format(date_create($row['target_date']), 'F d, Y') ?></td>
								<td><span class="badge"><?= $row['priority'] ?></span></td>
							</tr>
							<?php
						}
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