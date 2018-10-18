<?php
	/*Get DB connection*/
	include_once(dirname(__FILE__) . "/../../server/DatabaseHelper.php");
	$database = new DatabaseHelper();

	$profiles = [];
	$wildcard = "";

	if(isset($_GET["search"])){
		if(isset($_GET["wildcard"])){
			$wildcard = $_GET["wildcard"];
			$profiles = $database->searchByWildcard($wildcard);
		}
	}

	$database->close();
?>










<!DOCTYPE html>
<html lang="en">
	
	<!-- Page Head -->
	<head>
		<!-- Shared head content -->
		<?php include '../include/head_content.html'; ?>
		<!-- for pagination code only used on this page -->
		<script src="https://angular-ui.github.io/bootstrap/ui-bootstrap-tpls-2.5.0.js"></script>

		<!-- Set values from PHP on startup, accessible by the AngularJS Script -->
		<script type="text/javascript">
			var scope_profiles = <?php echo json_encode($profiles); ?>;
			var scope_wildcard = <?php echo json_encode($wildcard); ?>;
		</script>
		<!-- AngularJS Script -->
		<script type="module" src="profile_list.js"></script>
	</head>

	<!-- Page Body -->
	<body ng-app="HIGE-app">
		<!-- Shared Site Banner -->
		<?php include '../include/site_banner.html'; ?>

		<div id="MainContent" role="main">
			<script src="../include/outdatedbrowser.js" nomodule></script> <!-- show site error if outdated -->
			<?php include '../include/noscript.html'; ?> <!-- show site error if javascript is disabled -->

			<div class="container-fluid" ng-controller="listCtrl" id="listCtrl">
				<div class="row">
					<h1 class="title">Search The Database</h1>
					<!-- search form -->
					<form enctype="multipart/form-data" class="form-horizontal" id="profileSearchForm" name="profileSearchForm" ng-submit="submit()">
						<div class="form-group">
							<label for="keySearch">Search for any name, email address, affiliation, phone number, social link, country, region, issue, or language:</label>
							<div class="input-group">
								<input type="search" class="form-control" ng-model="keyTerm" id="keySearch" name="keySearch" placeholder="Enter A Key Term To Search For">
								<span class="input-group-btn">
									<button class="btn btn-primary" ng-click="keySearch()" type="submit"><span class="glyphicon glyphicon-search" aria-hidden="true">
								</span> Search</button>
							</span>
							</div>
						</div>
					</form>
				</div>
				<div class="row">
					<h1 class="title" ng-if="wildcard">{{profiles.length}} Result{{profiles.length === 1 ? "" : "s"}} For "{{wildcard}}":</h2>
					<nav class="col-md-12" aria-label="Top Page List">
						<ul ng-if="profiles.length" uib-pagination total-items="profiles.length" ng-model="pagination.currentPage" items-per-page="pagination.numPerPage"></ul>
					</nav>
					<div class="col-md-12 profile-list-wrapper">
						<div class="profile-list">
							<div class="profile profile-summary" ng-repeat="profile in filteredProfiles">
								<h2 class="profile-summary-name">{{profile.firstname}} {{profile.lastname}}</h2>
								<hr>
								<h3 class="profile-summary-affiliations" >{{profile.affiliations}}</h3>
								<h3 class="profile-summary-email">{{profile.email}}</h3>
								<h2 ng-if="wildcard">Key Term Found In</h2>
								<ul ng-if="wildcard" class="compactList profile-summary-keyterm">
									<li ng-repeat="category in profile.foundIn">{{category}}</li>
								</ul>
								<div class="profile-button">
									<a class="btn btn-success" href="../profile/profile.php?id={{profile.id}}">View Profile</a>
								</div>
							</div>
						</div>
					</div>
					<nav class="col-md-12" aria-label="Bottom Page List">
						<ul ng-if="profiles.length" uib-pagination total-items="profiles.length" ng-model="pagination.currentPage" items-per-page="pagination.numPerPage"></ul>
					</nav>
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