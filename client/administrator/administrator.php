<?php
	/*User validation*/
	include_once(dirname(__FILE__) . "/../../CAS/CAS_login.php");

	echo $CASbroncoNetID;
	
	/*Get DB connection*/
	//nclude_once(dirname(__FILE__) . "/../../functions/database.php");
	//$conn = connection();
		
	// if(isAdministrator($conn, $CASbroncoNetID)) 
	// { 
	// 	$administrators = getAdministrators($conn);
	// 	$applicationApprovers = getApplicationApprovers($conn);
	// 	$committee = getCommittee($conn);
	// 	$finalReportApprovers = getfinalReportApprovers($conn);
?>




<!DOCTYPE html>
<html lang="en">
	
	<!-- Page Head -->
	<head>
		<!-- Shared head content -->
		<?php include '../include/head_content.html'; ?>

		<!-- Set values from PHP on startup, accessible by the AngularJS Script -->
		<!-- <script type="text/javascript">
			var scope_administrators = <?php echo json_encode($administrators); ?>;
		</script> -->
		<!-- AngularJS Script -->
		<script type="module" src="administrator.js"></script>
	</head>

	<!-- Page Body -->
	<body ng-app="HIGE-app">
	
		<!-- Shared Site Banner -->
		<?php include '../include/site_banner.html'; ?>

		<div id="MainContent" role="main">
			<script src="../include/outdatedbrowser.js" nomodule></script> <!-- show site error if outdated -->
			<?php include '../include/noscript.html'; ?> <!-- show site error if javascript is disabled -->
	
				<!--AngularJS Controller-->
				<div class="container-fluid" ng-controller="adminCtrl" id="adminCtrl">
				
					<h1 class="title">Administrator View</h1>
					
					<!-- View & Remove Admins -->
					<!-- <table class="table table-bordered table-sm">
						<caption class="title">Administrators:</caption>
						<thead>
							<tr>
								<th>BroncoNetID</th>
								<th>Name</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody>
							<tr ng-repeat="admin in administrators">
								<td>{{ admin[0] }}</td>
								<td>{{ admin[1] }}</td>
								<td><button type="button" ng-click="removeAdmin(admin[0])" class="btn btn-danger">REMOVE</button></td> 
							</tr>
						</tbody>
					</table> -->
					<!--Add Admin-->
					<!-- <h3>Add Administrator:</h3>
					<form class="form-inline" ng-submit="addAdmin()"> 
						<div class="form-group">
							<label for="addAdminID">BroncoNetID:</label>
							<input type="text" ng-model="addAdminID" id="addAdminID" name="addAdminID">
						</div>
						<div class="form-group">
							<label for="addAdminName">Name:</label>
							<input type="text" ng-model="addAdminName" id="addAdminName" name="addAdminName">
						</div>
						<button type="submit" class="btn btn-success">Submit</button>
					</form> -->


					<div class="alert alert-{{alertType}} alert-dismissible" ng-class="{hideAlert: !alertMessage}">
						<button type="button" title="Close this alert." class="close" aria-label="Close" ng-click="removeAlert()"><span aria-hidden="true">&times;</span></button>{{alertMessage}}
					</div>


					<div class="buttons-group bottom-buttons"> 
						<a href="../home/home.php" class="btn btn-info">LEAVE PAGE</a>
					</div>
				</div>

			</div>
			
		</div>
	</body>
</html>
<?php
	// }else{
	// 	include '../include/permission_denied.html';
	// }
	// $conn = null; //close connection
?>