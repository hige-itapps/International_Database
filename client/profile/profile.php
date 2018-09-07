<?php
	/*Get DB connection*/
	// include_once(dirname(__FILE__) . "/../../functions/database.php");
	// $conn = connection();
	
	// /*Cycle functions*/
	// include_once(dirname(__FILE__) . "/../../functions/cycles.php");
	
	// /*Document functions*/
	// include_once(dirname(__FILE__) . "/../../functions/documents.php");
	
	/*get initial character limits for text fields*/
	// $maxName = $appCharMax[array_search('Name', array_column($appCharMax, 0))][1]; //name char limit
	// $maxDepartment = $appCharMax[array_search('Department', array_column($appCharMax, 0))][1]; //department char limit
	// $maxTitle = $appCharMax[array_search('Title', array_column($appCharMax, 0))][1]; //title char limit
	// $maxDestination = $appCharMax[array_search('Destination', array_column($appCharMax, 0))][1]; //destination char limit
	// $maxOtherEvent = $appCharMax[array_search('IsOtherEventText', array_column($appCharMax, 0))][1]; //other event text char limit
	// $maxOtherFunding = $appCharMax[array_search('OtherFunding', array_column($appCharMax, 0))][1]; //other funding char limit
	// $maxProposalSummary = $appCharMax[array_search('ProposalSummary', array_column($appCharMax, 0))][1]; //proposal summary char limit
	// $maxDeptChairApproval = $appCharMax[array_search('DepartmentChairSignature', array_column($appCharMax, 0))][1];//signature char limit
	
	// $maxBudgetDetails = $appBudgetCharMax[array_search('Details', array_column($appBudgetCharMax, 0))][1]; //budget details char limit
	
	
	/*Initialize all user permissions to false*/
	// $isCreating = false; //user is an applicant initially creating application
	// $isReviewing = false; //user is an applicant reviewing their already created application
	// $isAdmin = false; //user is an administrator
	// $isCommittee = false; //user is a committee member
	// $isChair = false; //user is the associated department chair
	// $isChairReviewing = false; //user is the associated department chair, but cannot do anything (just for reviewing purposes)
	// $isApprover = false; //user is an application approver (director)
	
	// $permissionSet = false; //boolean set to true when a permission has been set- used to force only 1 permission at most
	
	/*Get all user permissions. THESE ARE TREATED AS IF THEY ARE MUTUALLY EXCLUSIVE; ONLY ONE CAN BE TRUE!
	For everything besides application creation, the app ID MUST BE SET*/
	// if(isset($_GET["id"]))
	// {
	// 	if($permissionSet = $isAdmin = isAdministrator($conn, $CASbroncoNetID)){} //admin check
	// 	else if($permissionSet = $isApprover = isApplicationApprover($conn, $CASbroncoNetID)){} //application approver check
	// 	else if($permissionSet = $isCommittee = isCommitteeMember($conn, $CASbroncoNetID)){} //committee member check
	// 	else if($permissionSet = $isChair = isUserAllowedToSignApplication($conn, $CASemail, $_GET['id'], $CASbroncoNetID)){} //department chair check
	// 	else if($permissionSet = $isChairReviewing = isUserDepartmentChair($conn, $CASemail, $_GET['id'], $CASbroncoNetID)){} //department chair reviewing check
	// 	else if($permissionSet = $isReviewing = doesUserOwnApplication($conn, $CASbroncoNetID, $_GET['id'])){} //applicant reviewing check
	// }
	// if(!$permissionSet && !isset($_GET["id"])) //applicant creating check. Note- if the app id is set, then by default the application cannot be created
	// {
	// 	$permissionSet = $isCreating = isUserAllowedToCreateApplication($conn, $CASbroncoNetID, true); //applicant is creating an application (check latest date possible)
	// }

	// /*Verify that user is allowed to render application*/
	// if($permissionSet)
	// {	
	// 	/*Initialize variables if application has already been created*/
	// 	if(!$isCreating)
	// 	{
	// 		$appID = $_GET["id"];
			
	// 		$app = getApplication($conn, $appID); //get application Data
	// 		$submitDate = DateTime::createFromFormat('Y-m-d', $app->dateSubmitted);
	// 		$appFiles = getFileNames($appID);
	// 		$appEmails = getEmails($conn, $appID);

	// 		$thisCycle = getCycleName($submitDate, false, true);
	// 		$nextCycle = getCycleName($submitDate, true, true);

	// 		if($isAdmin || $isApprover || $isCommittee) //if hige staff, then retrieve staff notes
	// 		{
	// 			$staffNotes = getStaffNotes($conn, $appID);
	// 		}
	// 	}
	// 	else
	// 	{
	// 		$thisCycle = getCycleName($currentDate, false, true);
	// 		$nextCycle = getCycleName($currentDate, true, true); 
	// 	}
?>










<!DOCTYPE html>
<html lang="en">
	
	<!-- Page Head -->
	<head>
		<!-- Shared head content -->
		<?php include '../include/head_content.html'; ?>

		<!-- Set values from PHP on startup, accessible by the AngularJS Script -->
		<!-- <script type="text/javascript">
			var scope_currentDate = <?php echo json_encode($currentDate->format('Y-m-d')); ?>;
			var scope_thisCycle = <?php echo json_encode($thisCycle); ?>;
			var scope_nextCycle = <?php echo json_encode($nextCycle); ?>;
		</script> -->
		<!-- AngularJS Script -->
		<script type="module" src="profile.js"></script>
	</head>

	<!-- Page Body -->
	<body ng-app="HIGE-app">
	
		<!-- Shared Site Banner -->
		<?php include '../include/site_banner.html'; ?>

	<div id="MainContent" role="main">
		<script src="../include/outdatedbrowser.js"></script> <!-- show site error if outdated -->
		<?php include '../include/noscript.html'; ?> <!-- show site error if javascript is disabled -->

			<!--AngularJS Controller-->
			<div class="container-fluid" ng-controller="appCtrl" id="appCtrl">

				<h1 ng-cloak ng-show="!isCreating" class="{{appStatus}}-background status-bar">Application Status: {{appStatus}}</h1>

				<div ng-cloak ng-show="isAdmin || isAdminUpdating" class="buttons-group"> 
					<button type="button" ng-click="toggleAdminUpdate()" class="btn btn-warning">TURN {{isAdminUpdating ? "OFF" : "ON"}} ADMIN UPDATE MODE</button>
					<button type="button" ng-click="populateForm(null)" class="btn btn-warning">RELOAD SAVED DATA</button>
					<button type="button" ng-click="insertApplication()" class="btn btn-warning">SUBMIT CHANGES</button>
				</div>

					<!-- application form -->
				<form enctype="multipart/form-data" class="form-horizontal" id="applicationForm" name="applicationForm" ng-submit="submit()">

					

					<div class="row">
						<h1 class="title">APPLICATION:</h1>
					</div>
					
					
					<div class="row">
					<!--NAME-->
						<!-- <div class="col-md-5">
							<div class="form-group">
								<label for="name">Name{{isCreating || isAdminUpdating ? " (Required) ("+(maxName-formData.name.length)+" characters remaining)" : ""}}:</label>
								<input type="text" class="form-control" maxlength="{{maxName}}" ng-model="formData.name" ng-disabled="appFieldsDisabled" id="name" name="name" placeholder="Enter Name" />
								<span class="help-block" ng-show="errors.name" aria-live="polite">{{ errors.name }}</span> 
							</div>
						</div> -->
					<!--EMAIL-->
						<!-- <div class="col-md-7">
							<div class="form-group">
								<label for="email">Email Address{{isCreating || isAdminUpdating ? " (Required)" : ""}}:</label>
								<input type="email" class="form-control" ng-model="formData.email" ng-disabled="appFieldsDisabled" id="email" name="email" placeholder="Enter Email Address" />
								<span class="help-block" ng-show="errors.email" aria-live="polite">{{ errors.email }}</span> 
							</div>
						</div> -->
					</div>


					<div class="alert alert-{{alertType}} alert-dismissible" ng-class="{hideAlert: !alertMessage}">
						<button type="button" title="Close this alert." class="close" aria-label="Close" ng-click="removeAlert()"><span aria-hidden="true">&times;</span></button>{{alertMessage}}
					</div>



					<div class="buttons-group bottom-buttons"> 
						<button ng-show="false" type="submit" ng-click="submitFunction='insertApplication'" class="btn btn-success">SUBMIT APPLICATION</button> <!-- For applicant submitting for first time -->
						<a href="" class="btn btn-info" ng-click="redirectToHomepage(null, null)">LEAVE PAGE</a> <!-- For anyone to leave the page -->
					</div>
				</form>

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