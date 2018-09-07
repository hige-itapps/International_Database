<?php
	/*Get DB connection*/
	//include_once(dirname(__FILE__) . "/../../functions/database.php");
	//$conn = connection();
?>










<!DOCTYPE html>
<html lang="en">
	
	<!-- Page Head -->
	<head>
		<!-- Shared head content -->
		<?php include '../include/head_content.html'; ?>

		<!-- Set values from PHP on startup, accessible by the AngularJS Script -->
		<!-- <script type="text/javascript">
			var scope_applications = <?php echo json_encode($apps); ?>;
			var scope_appCycles = <?php echo json_encode($appCycles); ?>;
			var scope_isAllowedToSeeApplications = <?php echo json_encode($isAllowedToSeeApplications); ?>;
		</script> -->
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
				<!--<div class="row">
					<div class="col-md-1"></div>-->
				<!--Filter name-->
					<!-- <div class="col-md-2">
						<div class="form-group">
							<label for="filterName">Filter by name:</label>
							<input type="text" ng-model="filterName" class="listInput form-control" id="filterName" name="filterName" />
						</div>
					</div> -->
				<!--Filter cycle-->
					<!-- <div class="col-md-2">
						<div class="form-group">
							<label for="filterCycle">Filter by cycle:</label><br>
							<select ng-options="item as item for item in appCycles track by item" class="listInput" ng-init="filterCycle = appCycles[0]" ng-model="filterCycle" id="filterCycle" name="filterCycle">
								<option value="">All</option>
							</select>
						</div>
					</div> -->
				<!--Filter first date-->
					<!-- <div class="col-md-2">
						<div class="form-group">
							<label for="filterDateFrom">Filter date after:</label>
							<input type="date" ng-model="filterFrom" class="listInput form-control" id="filterDateFrom" name="filterDateFrom" />
						</div>
					</div> -->
				<!--Filter last date-->
					<!-- <div class="col-md-2">
						<div class="form-group">
							<label for="filterDateTo">Filter date up to:</label>
							<input type="date" ng-model="filterTo" class="listInput form-control" id="filterDateTo" name="filterDateTo" />
						</div>
					</div> -->
				<!--Filter status-->
					<!-- <div class="col-md-2">
						<div class="form-group">
							<label for="filterStatus">Filter by status:</label><br>
							<select ng-model="filterStatus" class="listInput" id="filterStatus" name="filterStatus">
								<option value="">All</option>
								<option value="Approved">Approved</option>
								<option value="Pending">Pending</option>
								<option value="Denied">Denied</option>
								<option value="Hold">Hold</option>
							</select>
						</div>
					</div>
					<div class="col-md-1"></div>
				</div>
				<div class="row">
					<div class="col-md-1"></div>
					<div class="col-md-10">
						<table class="table table-striped" id="appTable">
							<caption>Selected Applications:</caption>
							<thead>
								<tr>
									<th>ID</th>
									<th>Name</th>
									<th>Title</th>
									<th>Cycle</th>
									<th>Date Submitted</th>
									<th>Status</th>
									<th>Approval</th>
									<th>Application Link</th>
									<th>Final Report Link</th>
									<th>Final Report Status</th>
								</tr>
							</thead>
							<tbody> -->
								<!-- Apply all filters to the list based on: dates, cycles, name, and status -->
								<!-- <tr ng-repeat="application in (filteredApplications = (applications | dateFilter:filterFrom:filterTo | filter: (!!filterCycle || undefined)&&{cycle: filterCycle} | filter: {name: filterName, status: filterStatus}))">
									<td>{{ application.id }}</td>
									<td>{{ application.name }}</td>
									<td>{{ application.title }}</td>
									<td>{{ application.cycle }}</td>
									<td>{{ application.dateSubmitted | date: 'MM/dd/yyyy'}}</td>
									<td class="{{application.status}}">{{ application.status }}</td>
									<td>{{application.deptChairApproval}}</td>
									<td><a href="../application/application.php?id={{application.id}}">Application</a></td>
									<td ng-if="application.FinalReport"><a href="../final_report/final_report.php?id={{application.id}}">Final Report</a></td>					<td class="{{application.FinalReport.status}}" ng-if="application.FinalReport">{{application.FinalReport.status}}</td>
										<td ng-if="application.FinalReportCreate"><a href="../final_report/final_report.php?id={{application.id}}">Create Final Report</a></td>	<td ng-if="application.FinalReportCreate">N/A</td>
										<td ng-if="!application.FinalReport && !application.FinalReportCreate">N/A</td>										<td ng-if="!application.FinalReport && !application.FinalReportCreate">N/A</td>
								</tr>
							</tbody>
						</table>
					</div>
					<div class="col-md-1"></div>
				</div> -->

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