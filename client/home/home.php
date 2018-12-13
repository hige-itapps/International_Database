<?php
	/*Get BroncoNetID if CAS session started*/
	include_once(dirname(__FILE__) . "/../../CAS/CAS_session.php");

	/*Get DB connection*/
	include_once(dirname(__FILE__) . "/../../server/DatabaseHelper.php");

	/*Logger*/
	include_once(dirname(__FILE__) . "/../../server/Logger.php");

	/*Site Warning*/
	include_once(dirname(__FILE__) . "/../../server/SiteWarning.php");

	$logger = new Logger(); //for logging to files
	$database = new DatabaseHelper($logger); //database helper object used for some verification and insertion
	$siteWarning = new SiteWarning($database); //used to determine if a site warning exists and should be displayed

	$isAdmin = false;
	if(isset($CASbroncoNetID)){
		if($database->isAdministrator($CASbroncoNetID)){$isAdmin = true;} //find out if user is admin, and should therefore be able to see the admin page link
	}

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
			var scope_isAdmin = <?php echo json_encode($isAdmin); ?>;
			var alert_type = <?php echo json_encode($alertType); ?>;
			var alert_message = <?php echo json_encode($alertMessage); ?>;
		</script>
		<!-- AngularJS Script -->
		<script type="module" src="home.js"></script>
	</head>

	<!-- Page Body -->
	<body ng-app="HIGE-app">
		<!-- Shared Site Banner -->
		<?php include '../include/site_banner.php'; ?>
	
		<div id="MainContent" role="main">
			<?php $siteWarning->showIfExists() ?> <!-- show site warning if it exists -->
			<script src="../include/outdatedbrowser.js" nomodule></script> <!-- show site error if outdated -->
			<?php include '../include/noscript.html'; ?> <!-- show site error if javascript is disabled -->

			<!--AngularJS Controller-->
			<div class="container-fluid" ng-controller="homeCtrl" id="homeCtrl" ng-cloak>

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
	
		<!-- Shared Site Footer -->
		<?php include '../include/site_footer.php'; ?>
	</body>
	
</html>
<?php
	$conn = null; //close connection
?>