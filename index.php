<?php
/**************************************************
 *** FEATURE REQUEST APP
 *** author: Quan Huynh
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
	<!-- Main Stylesheet -->
	<link href="css/style.css" rel="stylesheet" />
</head>
<body>
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
				<a class="navbar-brand" href="#">Feature Request App</a>
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

	<!-------- REQUESTED FEATURES TABLE -------->
	<div class="container">
		<div class="panel panel-default">
			<div class="panel-heading text-center">REQUESTED FEATURES</div>
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
					<button type="button" class="btn btn-primary pull-right" style="margin-right: 15px;"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Submit New Request</button>
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

	<!-- jQuery 1.12.1 -->
	<script src="https://code.jquery.com/jquery-1.12.1.min.js" type="text/javascript"></script>
	<!-- Bootstrap JS -->
	<script src="js/bootstrap.min.js" type="text/javascript"></script>
</body>
</html>