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
	$languages = []; //fill with languages if creating profile
	$languageProficiencies = [];
	$countryExperiences = [];
	$usersMaxLengths = null;
	$otherCountryExperiencesMaxLength = null;

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
		$languages = $database->getLanguages();
		$languageProficiencies = $database->getLanguageProficiencies();
		$countryExperiences = $database->getCountryExperiences();
		$usersMaxLengths = $database->getUsersMaxLengths();
		$otherCountryExperiencesMaxLength = $database->getOtherCountryExperiencesMaxLength();
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
			var scope_languages = <?php echo json_encode($languages); ?>;
			var scope_languageProficiencies = <?php echo json_encode($languageProficiencies); ?>;
			var scope_countryExperiences = <?php echo json_encode($countryExperiences); ?>;
			var scope_usersMaxLengths = <?php echo json_encode($usersMaxLengths); ?>;
			var scope_maxOtherExperience = <?php echo json_encode($otherCountryExperiencesMaxLength); ?>;
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
						<div class="col-md-3 profile-summary" ng-if="!isCreating">
							<h2>{{profile.firstname}} {{profile.lastname}}</h2>
							<hr>
							<h3>{{profile.affiliations}}</h3>
							<h3>{{profile.primaryEmail}}</h3>
							<h3 ng-if="profile.phone">{{profile.phone}}</h3>
							<h3 ng-if="profile.social_link">{{profile.social_link}}</h3>
						</div>
						<div class="col-md-3 profile-summary" ng-if="isCreating">
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
								<label for="email">Alternate Primary Email Address- if specified, this address will show up on your profile page instead of your WMICH address, and our emails will only be sent to this address. ({{(maxEmail-formData.email.length)}} characters remaining):</label>
								<input type="text" class="form-control" maxlength="{{maxEmail}}" ng-model="formData.email" id="email" name="email" placeholder="Enter Email Address" />
								<span class="help-block" ng-show="errors.email" aria-live="polite">{{ errors.email }}</span> 
							</div>
							<div class="form-group">
								<label for="phone">Phone Number ({{(maxPhone-formData.phone.length)}} digits remaining):</label>
								<input type="text" class="form-control" maxlength="{{maxPhone}}" ng-model="formData.phone" id="phone" name="phone" placeholder="Enter Phone Number" />
								<span class="help-block" ng-show="errors.phone" aria-live="polite">{{ errors.phone }}</span> 
							</div>
							<div class="form-group">
								<label for="socialLink">Social Link ({{(maxSocialLink-formData.socialLink.length)}} characters remaining):</label>
								<input type="text" class="form-control" maxlength="{{maxSocialLink}}" ng-model="formData.socialLink" id="socialLink" name="socialLink" placeholder="Enter Social Link" />
								<span class="help-block" ng-show="errors.socialLink" aria-live="polite">{{ errors.socialLink }}</span> 
							</div>
						</div>
						<div class="col-md-7 profile-info">
							<div>
							<h2>Issues of Expertise{{isCreating ? " (Required)" : ""}}</h2>
								<ul ng-if="!isCreating" class="compactList">
									<li ng-repeat="issue in profile.issues_expertise">{{issue}}</li>
									<li ng-if="profile.issues_expertise_other">{{profile.issues_expertise_other}}</li>
								</ul>
								<div ng-if="isCreating" class="form-group">
									<label for="issuesExpertise">Issues of Expertise:</label>
									<select class="form-control" ng-change="addIssueExpertise()" ng-model="selectIssuesExpertise" id="issuesExpertise" name="issuesExpertise"
										ng-options="issue as issue.issue for issue in issues">
									</select>
								</div>
								<h3 ng-if="isCreating">Selected Issues:</h3>
								<ul ng-if="isCreating" class="user-list">
									<li ng-repeat="issue in userIssuesExpertise">{{issue.issue}} <a href ng-click="removeIssueExpertise($index)" class="delete-button">delete</a></li>
								</ul>
								<div ng-if="isCreating" class="form-group">
									<label for="issuesExpertiseOther">Other Issues of Expertise ({{(maxOtherIssues-formData.issuesExpertiseOther.length)}} characters remaining):</label>
									<input type="text" class="form-control" maxlength="{{maxOtherIssues}}" ng-model="formData.issuesExpertiseOther" id="issuesExpertiseOther" name="issuesExpertiseOther" placeholder="Enter Any Other Issues Of Expertise" />
									<span class="help-block" ng-show="errors.issuesExpertiseOther" aria-live="polite">{{ errors.issuesExpertiseOther }}</span> 
								</div>
							</div>
							<div>
								<h2>Countries of Expertise{{isCreating ? " (Required)" : ""}}</h2>
								<ul ng-if="!isCreating" class="compactList">
									<li ng-repeat="country in profile.countries_expertise">{{country}}</li>
									<li ng-if="profile.countries_expertise_other">{{profile.countries_expertise_other}}</li>
								</ul>
								<div ng-if="isCreating" class="form-group">
									<label for="countriesExpertise">Countries of Expertise:</label>
									<select class="form-control" ng-change="addCountryExpertise()" ng-model="selectCountriesExpertise" id="countriesExpertise" name="countriesExpertise"
										ng-options="country as country.country_name for country in countries">
									</select>
								</div>
								<h3 ng-if="isCreating">Selected Countries:</h3>
								<ul ng-if="isCreating" class="user-list">
									<li ng-repeat="country in userCountriesExpertise">{{country.country_name}} <a href ng-click="removeCountryExpertise($index)" class="delete-button">delete</a></li>
								</ul>
								<div ng-if="isCreating" class="form-group">
									<label for="countriesExpertiseOther">Other Countries of Expertise ({{(maxOtherCountriesExpertise-formData.countriesExpertiseOther.length)}} characters remaining):</label>
									<input type="text" class="form-control" maxlength="{{maxOtherCountriesExpertise}}" ng-model="formData.countriesExpertiseOther" id="countriesExpertiseOther" name="countriesExpertiseOther" placeholder="Enter Any Other Countries Of Expertise" />
									<span class="help-block" ng-show="errors.countriesExpertiseOther" aria-live="polite">{{ errors.countriesExpertiseOther }}</span> 
								</div>
							</div>
							<div>
								<h2>Regions of Expertise{{isCreating ? " (Required)" : ""}}</h2>
								<ul ng-if="!isCreating" class="compactList">
									<li ng-repeat="region in profile.regions_expertise">{{region}}</li>
									<li ng-if="profile.regions_expertise_other">{{profile.regions_expertise_other}}</li>
								</ul>
								<div ng-if="isCreating" class="form-group">
									<label for="regionsExpertise">Regions of Expertise:</label>
									<select class="form-control" ng-change="addRegionExpertise()" ng-model="selectRegionsExpertise" id="regionsExpertise" name="regionsExpertise"
										ng-options="region as region.region for region in regions">
									</select>
								</div>
								<h3 ng-if="isCreating">Selected Regions:</h3>
								<ul ng-if="isCreating" class="user-list">
									<li ng-repeat="region in userRegionsExpertise">{{region.region}} <a href ng-click="removeRegionExpertise($index)" class="delete-button">delete</a></li>
								</ul>
								<div ng-if="isCreating" class="form-group">
									<label for="regionsExpertiseOther">Other Regions of Expertise ({{(maxOtherRegions-formData.regionsExpertiseOther.length)}} characters remaining):</label>
									<input type="text" class="form-control" maxlength="{{maxOtherRegions}}" ng-model="formData.regionsExpertiseOther" id="regionsExpertiseOther" name="regionsExpertiseOther" placeholder="Enter Any Other Regions Of Expertise" />
									<span class="help-block" ng-show="errors.regionsExpertiseOther" aria-live="polite">{{ errors.regionsExpertiseOther }}</span> 
								</div>
							</div>
							<div>
								<h2>Languages</h2>
								<ul ng-if="!isCreating">
									<li class="languages" ng-repeat="language in profile.languages">{{language.language}} -- {{language.proficiency}}</li>
								</ul>
								<div ng-if="isCreating" class="form-group">
									<label for="languages">Languages:</label>
									<select class="form-control" ng-change="addLanguage()" ng-model="selectLanguages" id="languages" name="languages"
										ng-options="language as language.name for language in languages">
									</select>
								</div>
								<h3 ng-if="isCreating">Selected Languages:</h3>
								<ul ng-if="isCreating" class="user-list">
									<li ng-repeat="language in userLanguages">{{language.name}} 
										<label for="proficiency{{$index}}">Proficiency Level (Required):</label>
										<select class="form-control" ng-model="userLanguages[($index)].proficiency_level" id="proficiency{{$index}}" name="proficiency{{$index}}"
											ng-options="proficiency as proficiency.proficiency_level for proficiency in languageProficiencies">
										</select>
										<a href ng-click="removeLanguage($index)" class="delete-button">delete</a>
									</li>
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
								<div ng-if="isCreating" class="form-group">
									<label for="countriesExperience">Countries you've lived in:</label>
									<select class="form-control" ng-change="addCountryExperience()" ng-model="selectCountriesExperience" id="countriesExperience" name="countriesExperience"
										ng-options="country as country.country_name for country in countries">
									</select>
								</div>
								<h3 ng-if="isCreating">Selected Countries:</h3>
								<ul ng-if="isCreating" class="user-list">
									<li ng-repeat="country in userCountriesExperience">{{country.country_name}} 
										<label for="countryExperience{{$index}}">Experiences:</label>
										<select class="form-control"  ng-change="addCountryExperienceLevel($index)" ng-model="userCountriesExperience[$index].selectedExperience" id="countryExperience{{$index}}" name="countryExperience{{$index}}"
											ng-options="countryExperience as countryExperience.experience for countryExperience in countryExperiences">
										</select>
										<a href ng-click="removeCountryExperience($index)" class="delete-button">delete</a>
										<h4>Selected Experiences (Required):</h4>
										<ul>
											<li ng-repeat="experience in userCountriesExperience[$index].experiences">{{experience.experience}} 
												<a href ng-click="removeCountryExperienceLevel($parent.$index, $index)" class="delete-button">delete</a>
											</li>
										</ul>
										<div class="form-group">
											<label for="countryExperienceOther{{$index}}">Other Experience ({{(maxOtherExperience-userCountriesExperience[$index].otherExperience.length)}} characters remaining):</label>
											<input type="text" class="form-control" maxlength="{{maxOtherExperience}}" ng-model="userCountriesExperience[$index].otherExperience" id="countryExperienceOther{{$index}}" name="countryExperienceOther{{$index}}" placeholder="Enter Other Experiences You've Had While In This Country" />
										</div>
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