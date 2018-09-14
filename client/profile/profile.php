<?php
	/*Get DB connection*/
	include_once(dirname(__FILE__) . "/../../server/database.php");

	$database = new DatabaseHelper();

	$profile = null; //profile variable will be set if trying to load one
	$isCreating = false; //variable set to true if creating a new profile
	$loaded_profile = false; //check if profile was loaded correctly
	$issues = []; //fill with issues if creating profile
	$countries = []; //fill with countries if creating profile
	$regions = []; //fill with regions if creating profile

	if(isset($_GET["id"])) //if ID is set, get the full user profile
	{
		$profile = $database->getUserProfile($_GET["id"]);
		if ($profile) {$loaded_profile = true;}
	}
	else if(isset($_GET["create"]))
	{
		$isCreating = true;
		$issues = $database->getIssues();
		$countries = $database->getCountries();
		$regions = $database->getRegions();
	}

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
			var scope_profile = <?php echo json_encode($profile); ?>;
			var scope_isCreating = <?php echo json_encode($isCreating); ?>;
			var scope_issues = <?php echo json_encode($issues); ?>;
			var scope_countries = <?php echo json_encode($countries); ?>;
			var scope_regions = <?php echo json_encode($regions); ?>;
		</script>
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
			<div class="container-fluid" ng-controller="profileCtrl" id="profileCtrl">

				<!-- <div ng-cloak ng-show="isAdmin || isAdminUpdating" class="buttons-group"> 
					<button type="button" ng-click="toggleAdminUpdate()" class="btn btn-warning">TURN {{isAdminUpdating ? "OFF" : "ON"}} ADMIN UPDATE MODE</button>
					<button type="button" ng-click="populateForm(null)" class="btn btn-warning">RELOAD SAVED DATA</button>
					<button type="button" ng-click="insertApplication()" class="btn btn-warning">SUBMIT CHANGES</button>
				</div> -->

					<!-- profile form -->
				<form enctype="multipart/form-data" class="form-horizontal" id="profileForm" name="profileForm" ng-submit="submit()">

					
					<div class="row">
						<h1 class="title">{{isCreating ? "Create " : ""}}Profile:</h1>
					</div>
					<div class="row profile">
						<div class="col-md-1"></div>
						<div class="col-md-4 profile-summary" ng-if="!isCreating">
							<h2>{{profile.firstname}} {{profile.lastname}}</h2>
							<hr>
							<h3>{{profile.affiliations}}</h3>
							<h3>{{profile.email}}</h3>
							<h3 ng-if="profile.phone">{{profile.phone}}</h3>
							<h3 ng-if="profile.social_link">{{profile.social_link}}</h3>
						</div>
						<div class="col-md-4 profile-summary" ng-if="isCreating">
							<div class="form-group">
								<label for="firstName">First Name (Required) ({{(maxFirstName-formData.firstName.length)}} characters remaining):</label>
								<input type="text" class="form-control" maxlength="{{maxFirstName}}" ng-model="formData.firstName" id="firstName" name="firstName" placeholder="Enter First Name" />
								<span class="help-block" ng-show="errors.firstName" aria-live="polite">{{ errors.firstName }}</span> 
							</div>
							<div class="form-group">
								<label for="lastName">Last Name (Required) ({{(maxLastName-formData.lastName.length)}} characters remaining):</label>
								<input type="text" class="form-control" maxlength="{{maxLastName}}" ng-model="formData.lastName" id="lastName" name="lastName" placeholder="Enter Last Name" />
								<span class="help-block" ng-show="errors.lastName" aria-live="polite">{{ errors.lastName }}</span> 
							</div>
							<hr>
							<div class="form-group">
								<label for="affiliations">Affiliations (Required) ({{(maxAffiliations-formData.affiliations.length)}} characters remaining):</label>
								<input type="text" class="form-control" maxlength="{{maxAffiliations}}" ng-model="formData.affiliations" id="affiliations" name="affiliations" placeholder="Enter Affiliations" />
								<span class="help-block" ng-show="errors.affiliations" aria-live="polite">{{ errors.affiliations }}</span> 
							</div>
							<div class="form-group">
								<label for="email">Email Address (Required) ({{(maxEmail-formData.email.length)}} characters remaining):</label>
								<input type="text" class="form-control" maxlength="{{maxEmail}}" ng-model="formData.email" id="email" name="email" placeholder="Enter Email Address" />
								<span class="help-block" ng-show="errors.email" aria-live="polite">{{ errors.email }}</span> 
							</div>
							<div class="form-group">
								<label for="phone">Phone Number ({{(maxPhone-formData.phone.length)}} characters remaining):</label>
								<input type="text" class="form-control" maxlength="{{maxPhone}}" ng-model="formData.phone" id="phone" name="phone" placeholder="Enter Phone Number" />
								<span class="help-block" ng-show="errors.phone" aria-live="polite">{{ errors.phone }}</span> 
							</div>
							<div class="form-group">
								<label for="socialLink">Social Link ({{(maxSocialLink-formData.socialLink.length)}} characters remaining):</label>
								<input type="text" class="form-control" maxlength="{{maxSocialLink}}" ng-model="formData.socialLink" id="socialLink" name="socialLink" placeholder="Enter Social Link" />
								<span class="help-block" ng-show="errors.socialLink" aria-live="polite">{{ errors.socialLink }}</span> 
							</div>
						</div>
						<div class="col-md-6 profile-info">
							<div>
							<h2>Issues of Expertise</h2>
								<ul ng-if="!isCreating">
									<li ng-repeat="issue in profile.issues_expertise">{{issue}}</li>
									<li ng-if="profile.issues_expertise_other">{{profile.issues_expertise_other}}</li>
								</ul>
								<div ng-if="isCreating" class="form-group">
									<label for="issues-expertise">Issues of Expertise (select one):</label>
									<select class="form-control" id="issues-expertise" name="issues-expertise">
										<option ng-repeat="issue in issues">{{issue}}</option>
									</select>
								</div>
							</div>
							<div>
								<h2>Countries of Expertise</h2>
								<ul ng-if="!isCreating">
									<li ng-repeat="country in profile.countries_expertise">{{country}}</li>
									<li ng-if="profile.countries_expertise_other">{{profile.countries_expertise_other}}</li>
								</ul>
								<div ng-if="isCreating" class="form-group">
									<label for="countries-expertise">Countries of Expertise (select one):</label>
									<select class="form-control" id="countries-expertise" name="countries-expertise">
										<option ng-repeat="country in countries">{{country}}</option>
									</select>
								</div>
							</div>
							<div>
								<h2>Regions of Expertise</h2>
								<ul ng-if="!isCreating">
									<li ng-repeat="region in profile.regions_expertise">{{region}}</li>
									<li ng-if="profile.regions_expertise_other">{{profile.regions_expertise_other}}</li>
								</ul>
								<div ng-if="isCreating" class="form-group">
									<label for="regions-expertise">Regions of Expertise (select one):</label>
									<select class="form-control" id="regions-expertise" name="regions-expertise">
										<option ng-repeat="region in regions">{{region}}</option>
									</select>
								</div>
							</div>
							<div>
								<h2>Languages</h2>
								<ul ng-if="!isCreating">
									<li class="languages" ng-repeat="language in profile.languages">{{language.language}} -- {{language.proficiency}}</li>
								</ul>
							</div>
							<div>
								<h2>Country Experience</h2>
								<ul ng-if="!isCreating">
									<li class="country-experience" ng-repeat="(country, experiences) in profile.countries_experience">
										<h3>{{country}}</h3>
										<ul>
											<li ng-repeat="experience in experiences.experience">{{experience}}</li>
											<li ng-if="experiences.other_experience">{{experiences.other_experience}}</li>
										</ul>
									</li>
								</ul>
							</div>
						</div>
						<div class="col-md-1"></div>
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