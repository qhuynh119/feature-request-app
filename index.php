<?php
/**************************************************
 *** FEATURE REQUEST APP
 *** author: Quan Huynh
 **************************************************/

/**************************************************
 *** INCLUDES
 **************************************************/
include_once('includes/connection.php');

// get the action value
if (isset($_GET['action'])) {
	$action = $_GET['action'];
} else {
	$action = null;
}

/**************************************************
 *** AJAX FUNCTION
 **************************************************/
if ($action == 'get_client_priority') {
	// populate the client priority drop-down box
	$client_id = $_GET['client_id'];
	$sql       = "SELECT priority
				  FROM feature_request_app.requested_feature
				  WHERE client_id = '".mysqli_real_escape_string($conn, $client_id)."'";
	$result    = mysqli_query($conn, $sql);

	// if this client hasn't requested any feature,
	// there is only one option for this feature's priority
	if (mysqli_num_rows($result) == 0) {
		echo "<option value='1'>1</option>";
	} else {
		while ($row = mysqli_fetch_assoc($result)) {
			echo "<option value='".$row['priority']."'>".$row['priority']."</option>";
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
		<form>
			<!-- FEATURE TITLE -->
			<div class="form-group">
				<label for="feature_title">Title <span class="red-text">*</span></label>
				<input type="text" class="form-control" id="feature_title" placeholder="Title">
			</div>

			<div class="row">
				<!-- CLIENT -->
				<div class="form-group col-xs-5 col-sm-3 col-md-6">
					<label for="feature_client">Client <span class="red-text">*</span></label>
					<select id="feature_client" class="form-control" onchange="set_client_priority();">
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
					<select id="feature_priority" class="form-control">
						<?php
						/* priority options are populated by AJAX, determined by client */
						?>
					</select>
				</div>

				<!-- PRODUCT AREA -->
				<div class="form-group col-xs-5 col-sm-3 col-md-3">
					<label for="feature_area">Product Area <span class="red-text">*</span></label>
					<select id="feature_area" class="form-control">
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
				</div>
			</div>

			<!-- TARGET DATE -->
			<div class="form-group">
				<label for="feature_client">Target Date <span class="red-text">*</span></label>
				<input type="date" class="form-control" id="feature_target_date" placeholder="mm/dd/yyyy">
			</div>

			<!-- TICKET URL -->
			<div class="form-group">
				<label for="feature_url">Ticket URL</label>
				<input type="text" class="form-control" id="feature_url" placeholder="Ticket URL">
			</div>

			<!-- FEATURE DESCRIPTION -->
			<div class="form-group">
				<label for="feature_description">Description <span class="red-text">*</span></label>
				<textarea class="form-control" id="feature_description" rows="3"></textarea>
			</div>

			<!-- SUBMIT -->
			<button type="submit" class="btn-lg btn-primary">Submit</button>
		</form>
	</div>
	<?php
}
?>

<?php
/**************************************************
 *** SUBMIT NEW REQUEST
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
 *** FOOTER
 **************************************************/
include_once('includes/footer.php');
?>

