<?php
	$alertType = isset($_POST["alert_type"]) ? $_POST["alert_type"] : null; //set the alert type if it exists, otherwise set to null
	$alertMessage = isset($_POST["alert_message"]) ? $_POST["alert_message"] : null; //set the alert type if it exists, otherwise set to null
?>
<!DOCTYPE html>
<html lang="en">
	
	<!-- Page Head -->
	<head>
		<!-- Shared head content -->
		<?php include '../include/head_content.html'; ?>

		<!-- Set values from PHP on startup, accessible by the AngularJS Script -->
		<script type="text/javascript">
			var alert_type = <?php echo json_encode($alertType); ?>;
			var alert_message = <?php echo json_encode($alertMessage); ?>;
		</script>
		<!-- AngularJS Script -->
		<script type="module" src="home.js"></script>
	</head>

	<!-- Page Body -->
	<body ng-app="HIGE-app">
		<!-- Shared Site Banner -->
		<?php include '../include/site_banner.html'; ?>
	
		<div id="MainContent" role="main">
			<script src="../include/outdatedbrowser.js"></script> <!-- show site error if outdated -->
			<?php include '../include/noscript.html'; ?> <!-- show site error if javascript is disabled -->

			<!--AngularJS Controller-->
			<div class="container-fluid" ng-controller="homeCtrl" id="homeCtrl">

				<div class="row">
					<div class="col-md-4"></div>
					<div class="col-md-4">
						<ul id="pageList">
							<li><a href="../profile_list/profile_list.php" class="btn btn-primary">Database Search</a></li>
							<li><a href="../profile/profile.php?create" class="btn btn-primary">Create A Profile</a></li>
							<li ng-if="isAdmin"><a href="../administrator/administrator.php" class="btn btn-primary">Administrator Page</a></li>
						</ul>	
					</div>
					<div class="col-md-4"></div>
				</div>	
				
				<div class="alert alert-{{alertType}} alert-dismissible" ng-class="{hideAlert: !alertMessage}">
					<button type="button" title="Close this alert." class="close" aria-label="Close" ng-click="removeAlert()"><span aria-hidden="true">&times;</span></button>{{alertMessage}}
				</div>
			</div>
		</div>
		<!--BODY-->
	
	</body>
	
</html>
<?php
	$conn = null; //close connection
?>