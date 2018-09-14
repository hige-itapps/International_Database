<?php
	/*Get DB connection*/
	//include_once(dirname(__FILE__) . "/../../functions/database.php");
	//$conn = connection();

	/*Get DB connection*/
	include_once(dirname(__FILE__) . "/../../server/database.php");
	$database = new DatabaseHelper();

	$profiles = $database->getAllUsersSummaries();
	$database->close();
?>










<!DOCTYPE html>
<html lang="en">
	
	<!-- Page Head -->
	<head>
		<!-- Shared head content -->
		<?php include '../include/head_content.html'; ?>

		<!-- Set values from PHP on startup, accessible by the AngularJS Script -->
		<script type="text/javascript">
			var scope_profiles = <?php echo json_encode($profiles); ?>;
		</script>
		<!-- AngularJS Script -->
		<script type="module" src="profile_list.js"></script>
	</head>

	<!-- Page Body -->
	<body ng-app="HIGE-app">
		<!-- Shared Site Banner -->
		<?php include '../include/site_banner.html'; ?>

		<div id="MainContent" role="main">
			<script src="../include/outdatedbrowser.js"></script> <!-- show site error if outdated -->
			<?php include '../include/noscript.html'; ?> <!-- show site error if javascript is disabled -->

			<!--AngularJS Controller-->
			<div class="container-fluid" ng-controller="listCtrl" id="listCtrl">
				<div class="row">
					<h1 class="title">Profiles</h1>
				</div>
				<div class="row" ng-repeat="profile in profiles">
					<div class="col-md-2">
						<h2>{{profile.firstname}} {{profile.lastname}}</h2>
						<hr>
						<h3>{{profile.affiliations}}</h3>
						<h3>{{profile.email}}</h3>
						<h3>{{profile.phone}}</h3>
						<h3>{{profile.social_link}}</h3>
					</div>
					<div class="col-md-10">
						<a href="../profile/profile.php?id={{profile.id}}">View Profile</a>
					</div>
				</div>

				<div class="alert alert-{{alertType}} alert-dismissible" ng-class="{hideAlert: !alertMessage}">
					<button type="button" title="Close this alert." class="close" aria-label="Close" ng-click="removeAlert()"><span aria-hidden="true">&times;</span></button>{{alertMessage}}
				</div>

				<div class="buttons-group bottom-buttons"> 
					<a href="../home/home.php" class="btn btn-info">LEAVE PAGE</a> <!-- For anyone to leave the page -->
				</div>

			</div>

		</div>
	</body>
</html>
<?php
	//}else{
	//	include '../include/permission_denied.html';
	//}
	//$conn = null; //close connection
?>