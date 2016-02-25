<?php
/**************************************************
 *** FEATURE REQUEST APP
 *** author: Quan Huynh
 **************************************************/

/**************************************************
 *** INCLUDES
 **************************************************/
include_once('includes/connection.php');
ob_start();

// get the action and sub_action value
$action = $_GET['action'];
$sub_action = $_GET['sub_action'];

/**************************************************
 *** AJAX FUNCTIONS
 **************************************************/
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
include_once('includes/header.php');

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
				<li><a href="./">Sign In</a></li>
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
 *** REQUEST FORM
 **************************************************/
if ($action == 'request_form') {
	?>
	<div class="container form">
		<h1>Submit New Request</h1>

		<div class="div-spacing"></div>

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
								FROM feature_request_app.client";
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
					<button class="btn btn-default form-control" id="add_client" onclick="?action=add_client">
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
				<input type="text" class="form-control" name="feature_url" placeholder="Ticket URL">
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
					prod_area_id = '".$area."'";
		$result = mysqli_query($conn, $sql);

		// redirect to main page with a message
		header('Location: ' . $_SERVER['PHP_SELF'] . '?sub_action=submit_successful');
		exit();
	}
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
		if ($sub_action == 'submit_successful') {
			// show a message after submitted a new request successfully
			?>
			<div class="alert alert-success" role="alert"><strong>Successful!</strong></div>
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
						<select class="form-control">
							<option>All Clients</option>
							<option>Client A</option>
							<option>Client B</option>
							<option>Client C</option>
						</select>
					</div><!-- .col-xs-5 .col-sm-3 .col-md-2 -->

					<!-- Submit New Request Button -->
					<button type="button" class="btn btn-primary pull-right" onclick="window.location.href = '?action=request_form';" style="margin-right: 15px;"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Submit New Request</button>
				</div><!-- .row -->
			</div><!-- .panel-body -->

			<!-- Table -->
			<table class="table">
				<thead>
				<tr>
					<th><a href="#">No.</a></th>
					<th><a href="#">Title</a></th>
					<th><a href="#">Client</a></th>
					<th><a href="#">Target Date</a></th>
					<th><a href="#">Priority</a></th>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td>1</td>
					<td><a href="#">Feature A1</a></td>
					<td><a href="#">Client A</a></td>
					<td>August 06, 2016</td>
					<td>1</td>
				</tr>
				<tr>
					<td>2</td>
					<td><a href="#">Feature A2</a></td>
					<td><a href="#">Client A</a></td>
					<td>August 06, 2016</td>
					<td>2</td>
				</tr>
				<tr>
					<td>3</td>
					<td><a href="#">Feature B1</a></td>
					<td><a href="#">Client B</a></td>
					<td>June 08, 2016</td>
					<td>1</td>
				</tr>
				<tr>
					<td>4</td>
					<td><a href="#">Feature C1</a></td>
					<td><a href="#">Client C</a></td>
					<td>December 12, 2016</td>
					<td>1</td>
				</tr>
				</tbody>
			</table>
		</div>
	</div>
	<?php
}

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

/**************************************************
 *** FOOTER
 **************************************************/
include_once('includes/footer.php');
?>