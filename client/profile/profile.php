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

	$profile = null; //profile variable will be set if trying to load one
	$state = null; //no state by default
	$loaded_profile = false; //check if profile was loaded correctly
	$codePending = false; //set if loading a users profile; used to check if a pending confirmation code exists

	$previousProfileID = 0; //set to the ID of the previous profile if this is a pending profile update

	$isAdmin = false; //set to true if signed in with CAS and verified as admin
	if(isset($CASbroncoNetID) && $database->isAdministrator($CASbroncoNetID)){$isAdmin = true;}

	$issues = $database->getIssues();
	$countries = $database->getCountries();
	$regions = $database->getRegions();
	$languages = $database->getLanguages();
	$languageProficiencies = $database->getLanguageProficiencies();
	$countryExperiences = $database->getCountryExperiences();
	$usersMaxLengths = $database->getUsersMaxLengths();
	$otherCountryExperiencesMaxLength = $database->getOtherCountryExperiencesMaxLength();

	if(isset($_GET["id"])){ //if ID is set, get the full user profile
		if(isset($_GET["review"]) && $isAdmin){ //user is an admin trying to review this profile
			$profile = $database->getUserProfile($_GET["id"], false, false); //specifically get pending profile, and both email addresses
			if ($profile) {
				$loaded_profile = true;
				$previousProfileID = $database->doesProfileExist($profile["login_email"]);
				$state = 'AdminReview'; //enter the admin reviw state
			}
		}
		else{ //regular user
			$profile = $database->getUserProfile($_GET["id"]);
			if ($profile) {
				$loaded_profile = true;
				$codePending = $database->isCodePending($profile["email"]);
				$state = 'View'; //enter the View state
			}
		}
	}
	else if(isset($_GET["create"])){
		$state = 'CreatePending'; //set to the create pending state if user wants to create a profile
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
			var scope_codePending = <?php echo json_encode(boolval($codePending)); ?>;
			var scope_previousProfileID = <?php echo json_encode($previousProfileID); ?>;
			var scope_state = <?php echo json_encode($state); ?>;
			var scope_isAdmin = <?php echo json_encode($isAdmin); ?>;
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
		<?php include '../include/site_banner.php'; ?>

	<div id="MainContent" role="main">
		<?php $siteWarning->showIfExists() ?> <!-- show site warning if it exists -->
		<script src="../include/outdatedbrowser.js" nomodule></script> <!-- show site error if outdated -->
		<?php include '../include/noscript.html'; ?> <!-- show site error if javascript is disabled -->

			<!--AngularJS Controller-->
			<div class="container-fluid" ng-controller="profileCtrl" id="profileCtrl">

				<!-- Form base used for different profile purposes -->
				<form enctype="multipart/form-data" class="form-horizontal" id="profileForm" name="profileForm" ng-submit="submit()">

					
					<div class="row">
						<h1 class="title" ng-if="state == null">This Profile Does Not Exist</h1>
						<h1 class="title" ng-if="state === 'View'">User Profile</h1>
						<h1 class="title" ng-if="state === 'AdminReview' && previousProfileID <= 0">New Profile</h1>
						<h1 class="title" ng-if="state === 'AdminReview' && previousProfileID > 0">Updated Profile - <a href="?id={{previousProfileID}}">View Previous</a></h1>
						<h1 class="title" ng-if="state === 'CreatePending' || state === 'EditPending'">Profile Confirmation</h1>
						<h1 class="title" ng-if="state === 'Create'">Create Profile</h1>
						<h1 class="title" ng-if="state === 'Edit'">Edit Profile</h1>
						<h2 class="title expiration" ng-if="expiration_timestamp > 0">Code expires in: {{hoursRemaining}} hour{{hoursRemaining !== 1 ? "s" : ""}}, {{minutesRemaining}} minute{{minutesRemaining !== 1 ? "s" : ""}}</h2>
					</div>
					<!-- Form for viewing or editing profile information -->
					<div class="row profile" ng-show="state === 'View' || state === 'AdminReview' || state === 'Create' || state === 'Edit'">
						<div class="col-md-1"></div>
						<div class="col-md-3 profile-summary" ng-if="state === 'View' || state === 'AdminReview'">
							<h2>{{profile.firstname}} {{profile.lastname}}</h2>
							<hr>
							<h3>{{profile.affiliations}}</h3>
							<h3 ng-if="state === 'View'">{{profile.email}}</h3>
							<h3 ng-if="state === 'AdminReview'">{{profile.login_email}}{{profile.alternate_email ? " (private address, will not publicly appear)" : " (primary contact address)"}}</h3>
							<h3 ng-if="state === 'AdminReview' && profile.alternate_email">{{profile.alternate_email}} (primary contact address)</h3>
							<h3 ng-if="profile.phone">{{profile.phone}}</h3>
							<h3 ng-if="profile.social_link">{{profile.social_link}}</h3>
						</div>
						<div class="col-md-3 profile-summary" ng-if="state === 'Create' || state === 'Edit'">
							<div class="form-group" ng-class="{errorHighlight: errors.loginEmail}">
								<label for="login_email">Login Email Address- this is your WMICH address</label>
								<input disabled type="text" class="form-control" maxlength="{{maxLoginEmail}}" ng-model="profile.login_email" id="login_email" name="login_email" placeholder="Enter WMICH Email Address" />
								<span class="help-block" ng-show="errors.loginEmail" aria-live="polite">{{ errors.loginEmail }}</span> 
							</div>
							<div class="form-group" ng-class="{errorHighlight: errors.firstname}">
								<label for="firstname">First Name (Required) ({{(maxFirstName-profile.firstname.length)}} characters remaining):</label>
								<input type="text" class="form-control" maxlength="{{maxFirstName}}" ng-model="profile.firstname" id="firstname" name="firstname" placeholder="Enter First Name" />
								<span class="help-block" ng-show="errors.firstname" aria-live="polite">{{ errors.firstname }}</span> 
							</div>
							<div class="form-group" ng-class="{errorHighlight: errors.lastname}">
								<label for="lastname">Last Name (Required) ({{(maxLastName-profile.lastname.length)}} characters remaining):</label>
								<input type="text" class="form-control" maxlength="{{maxLastName}}" ng-model="profile.lastname" id="lastname" name="lastname" placeholder="Enter Last Name" />
								<span class="help-block" ng-show="errors.lastname" aria-live="polite">{{ errors.lastname }}</span> 
							</div>
							<hr>
							<div class="form-group" ng-class="{errorHighlight: errors.affiliations}">
								<label for="affiliations">Affiliations (Required) ({{(maxAffiliations-profile.affiliations.length)}} characters remaining):</label>
								<input type="text" class="form-control" maxlength="{{maxAffiliations}}" ng-model="profile.affiliations" id="affiliations" name="affiliations" placeholder="Enter Affiliations" />
								<span class="help-block" ng-show="errors.affiliations" aria-live="polite">{{ errors.affiliations }}</span> 
							</div>
							<div class="form-group" ng-class="{errorHighlight: errors.alternateEmail}">
								<label for="alternate_email">Alternate Primary Email Address- if specified, this address will show up on your profile page instead of your WMICH address, and our emails will only be sent to this address. This cannot be a WMICH address. ({{(maxAlternateEmail-profile.alternate_email.length)}} characters remaining):</label>
								<input type="text" class="form-control" maxlength="{{maxAlternateEmail}}" ng-model="profile.alternate_email" id="alternate_email" name="alternate_email" placeholder="Enter Alternate Email Address" />
								<span class="help-block" ng-show="errors.alternateEmail" aria-live="polite">{{ errors.alternateEmail }}</span> 
							</div>
							<div class="form-group" ng-class="{errorHighlight: errors.phone}">
								<label for="phone">US Phone Number ({{(maxPhone-profile.phone.length)}} digits remaining):</label>
								<input type="text" class="form-control" maxlength="{{maxPhone}}" ng-model="profile.phone" id="phone" name="phone" placeholder="Enter US Phone Number" onkeypress='return (event.which >= 48 && event.which <= 57)'/> <!-- restricted to digits only -->
								<span class="help-block" ng-show="errors.phone" aria-live="polite">{{ errors.phone }}</span> 
							</div>
							<div class="form-group" ng-class="{errorHighlight: errors.social_link}">
								<label for="social_link">Social Link (LinkedIn, Facebook, etc...) ({{(maxSocialLink-profile.social_link.length)}} characters remaining):</label>
								<input type="text" class="form-control" maxlength="{{maxSocialLink}}" ng-model="profile.social_link" id="social_link" name="social_link" placeholder="Enter Social Link" />
								<span class="help-block" ng-show="errors.social_link" aria-live="polite">{{ errors.social_link }}</span> 
							</div>
						</div>
						<div class="col-md-7 profile-info">
							<div ng-class="{errorHighlight: errors.issuesExpertise}">
								<h2>Issues of Expertise{{state === 'Create' || state === 'Edit' ? " (Required)" : ""}}</h2>
								<ul ng-if="state === 'View' || state === 'AdminReview'" class="compactList">
									<li ng-repeat="issue in profile.issues_expertise">{{issue.issue}}</li>
									<li ng-if="profile.issues_expertise_other">{{profile.issues_expertise_other}}</li>
								</ul>
								<div ng-if="state === 'Create' || state === 'Edit'" class="form-group">
									<label for="issuesExpertise">Issues of Expertise:</label>
									<select class="form-control" ng-change="addIssueExpertise()" ng-model="selectIssuesExpertise" id="issuesExpertise" name="issuesExpertise"
										ng-options="issue as issue.issue for issue in issues">
									</select>
								</div>
								<h3 ng-if="state === 'Create' || state === 'Edit'">Selected Issues:</h3>
								<ul ng-if="state === 'Create' || state === 'Edit'" class="user-list">
									<li ng-repeat="issue in profile.issues_expertise">{{issue.issue}} <a href ng-click="removeIssueExpertise($index)" class="btn btn-danger">delete</a></li>
								</ul>
								<div ng-if="state === 'Create' || state === 'Edit'" class="form-group">
									<label for="issuesExpertiseOther">Other Issues of Expertise ({{(maxOtherIssues-profile.issues_expertise_other.length)}} characters remaining):</label>
									<input type="text" class="form-control" maxlength="{{maxOtherIssues}}" ng-model="profile.issues_expertise_other" id="issuesExpertiseOther" name="issuesExpertiseOther" placeholder="Enter Any Other Issues Of Expertise" />
								</div>
								<span class="help-block" ng-show="errors.issuesExpertise" aria-live="polite">{{ errors.issuesExpertise }}</span> 
							</div>
							<div ng-class="{errorHighlight: errors.countriesExpertise}">
								<h2>Countries of Expertise{{state === 'Create' || state === 'Edit' ? " (Required)" : ""}}</h2>
								<ul ng-if="state === 'View' || state === 'AdminReview'" class="compactList">
									<li ng-repeat="country in profile.countries_expertise">{{country.country_name}}</li>
									<li ng-if="profile.countries_expertise_other">{{profile.countries_expertise_other}}</li>
								</ul>
								<div ng-if="state === 'Create' || state === 'Edit'" class="form-group">
									<label for="countriesExpertise">Countries of Expertise:</label>
									<select class="form-control" ng-change="addCountryExpertise()" ng-model="selectCountriesExpertise" id="countriesExpertise" name="countriesExpertise"
										ng-options="country as country.country_name for country in countries">
									</select>
								</div>
								<h3 ng-if="state === 'Create' || state === 'Edit'">Selected Countries:</h3>
								<ul ng-if="state === 'Create' || state === 'Edit'" class="user-list">
									<li ng-repeat="country in profile.countries_expertise">{{country.country_name}} <a href ng-click="removeCountryExpertise($index)" class="btn btn-danger">delete</a></li>
								</ul>
								<div ng-if="state === 'Create' || state === 'Edit'" class="form-group">
									<label for="countriesExpertiseOther">Other Countries of Expertise ({{(maxOtherCountriesExpertise-profile.countries_expertise_other.length)}} characters remaining):</label>
									<input type="text" class="form-control" maxlength="{{maxOtherCountriesExpertise}}" ng-model="profile.countries_expertise_other" id="countriesExpertiseOther" name="countriesExpertiseOther" placeholder="Enter Any Other Countries Of Expertise" />
								</div>
								<span class="help-block" ng-show="errors.countriesExpertise" aria-live="polite">{{ errors.countriesExpertise }}</span> 
							</div>
							<div ng-class="{errorHighlight: errors.regionsExpertise}">
								<h2>Regions of Expertise{{state === 'Create' || state === 'Edit' ? " (Required)" : ""}}</h2>
								<ul ng-if="state === 'View' || state === 'AdminReview'" class="compactList">
									<li ng-repeat="region in profile.regions_expertise">{{region.region}}</li>
									<li ng-if="profile.regions_expertise_other">{{profile.regions_expertise_other}}</li>
								</ul>
								<div ng-if="state === 'Create' || state === 'Edit'" class="form-group">
									<label for="regionsExpertise">Regions of Expertise:</label>
									<select class="form-control" ng-change="addRegionExpertise()" ng-model="selectRegionsExpertise" id="regionsExpertise" name="regionsExpertise"
										ng-options="region as region.region for region in regions">
									</select>
								</div>
								<h3 ng-if="state === 'Create' || state === 'Edit'">Selected Regions:</h3>
								<ul ng-if="state === 'Create' || state === 'Edit'" class="user-list">
									<li ng-repeat="region in profile.regions_expertise">{{region.region}} <a href ng-click="removeRegionExpertise($index)" class="btn btn-danger">delete</a></li>
								</ul>
								<div ng-if="state === 'Create' || state === 'Edit'" class="form-group">
									<label for="regionsExpertiseOther">Other Regions of Expertise ({{(maxOtherRegions-profile.regions_expertise_other.length)}} characters remaining):</label>
									<input type="text" class="form-control" maxlength="{{maxOtherRegions}}" ng-model="profile.regions_expertise_other" id="regionsExpertiseOther" name="regionsExpertiseOther" placeholder="Enter Any Other Regions Of Expertise" />
								</div>
								<span class="help-block" ng-show="errors.regionsExpertise" aria-live="polite">{{ errors.regionsExpertise }}</span> 
							</div>
							<div>
								<h2>Languages</h2>
								<ul ng-if="state === 'View' || state === 'AdminReview'">
									<li class="languages" ng-repeat="language in profile.languages">{{language.name}} -- {{language.proficiency_level.proficiency_level}}</li>
								</ul>
								<div ng-if="state === 'Create' || state === 'Edit'" class="form-group">
									<label for="languages">Languages:</label>
									<select class="form-control" ng-change="addLanguage()" ng-model="selectLanguages" id="languages" name="languages"
										ng-options="language as language.name for language in languages">
									</select>
								</div>
								<h3 ng-if="state === 'Create' || state === 'Edit'">Selected Languages:</h3>
								<ul ng-if="state === 'Create' || state === 'Edit'" class="user-list">
									<li ng-class="{errorHighlight: errors['language '+language.id]}" ng-repeat="language in profile.languages">{{language.name}} 
										<label for="proficiency{{$index}}">Proficiency Level (Required):</label>
										<select class="form-control" ng-model="profile.languages[($index)].proficiency_level" id="proficiency{{$index}}" name="proficiency{{$index}}"
											ng-options="proficiency as proficiency.proficiency_level for proficiency in languageProficiencies track by proficiency.id">
										</select>
										<a href ng-click="removeLanguage($index)" class="btn btn-danger">delete</a>
										<span class="help-block" ng-show="errors['language '+language.id]" aria-live="polite">{{ errors['language '+language.id] }}</span> 
									</li>
								</ul>
							</div>
							<div>
								<h2>Country Experience</h2>
								<ul ng-if="state === 'View' || state === 'AdminReview'">
									<li class="country-experience" ng-repeat="country in profile.countries_experience">
										<h3>{{country.country_name}}</h3>
										<ul>
											<li ng-repeat="experience in country.experiences">{{experience.experience}}</li>
											<li ng-if="country.other_experience">{{country.other_experience}}</li>
										</ul>
									</li>
								</ul>
								<div ng-if="state === 'Create' || state === 'Edit'" class="form-group">
									<label for="countriesExperience">Countries you've lived in:</label>
									<select class="form-control" ng-change="addCountryExperience()" ng-model="selectCountriesExperience" id="countriesExperience" name="countriesExperience"
										ng-options="country as country.country_name for country in countries">
									</select>
								</div>
								<h3 ng-if="state === 'Create' || state === 'Edit'">Selected Countries:</h3>
								<ul ng-if="state === 'Create' || state === 'Edit'" class="user-list">
									<li ng-class="{errorHighlight: errors['country '+country.id]}" ng-repeat="(index, country) in profile.countries_experience">{{country.country_name}} 
										<label for="countryExperience{{index}}">Experiences:</label>
										<select class="form-control"  ng-change="addCountryExperienceLevel(index)" ng-model="profile.countries_experience[index].selectedExperience" id="countryExperience{{index}}" name="countryExperience{{index}}"
											ng-options="countryExperience as countryExperience.experience for countryExperience in countryExperiences">
										</select>
										<a href ng-click="removeCountryExperience(index)" class="btn btn-danger">delete</a>
										<h4>Selected Experiences (Required):</h4>
										<ul>
											<li ng-repeat="experience in profile.countries_experience[index].experiences">{{experience.experience}} 
												<a href ng-click="removeCountryExperienceLevel($parent.index, $index)" class="btn btn-danger">delete</a>
											</li>
										</ul>
										<div class="form-group">
											<label for="countryExperienceOther{{index}}">Other Experience ({{(maxOtherExperience-profile.countries_experience[index].other_experience.length)}} characters remaining):</label>
											<input type="text" class="form-control" maxlength="{{maxOtherExperience}}" ng-model="profile.countries_experience[index].other_experience" id="countryExperienceOther{{index}}" name="countryExperienceOther{{index}}" placeholder="Enter Other Experiences You've Had While In This Country" />
										</div>
										<span class="help-block" ng-show="errors['country '+country.id]" aria-live="polite">{{ errors['country '+country.id] }}</span> 
									</li>
								</ul>
							</div>
						</div>
						<div class="col-md-1"></div>
					</div>
					<!-- Code confirmation form for when user wants to create a profile or edit their profile -->
					<div class="row profile-code" ng-show="state === 'CreatePending' || state === 'EditPending'">
						<div class="col-md-4"></div>
						<div class="col-md-4">
							<div class="input-group" ng-show="state === 'CreatePending'">
								<label for="create_email">WMU Email Address:</label>
								<input type="text" class="form-control" ng-model="create_email" id="create_email" name="create_email" placeholder="Enter your email address" />
							</div>
							<label for="code">To {{state === 'CreatePending' ? "create" : "edit"}} your profile, you must enter your confirmation code:</label>
							<div class="input-group">
								<input type="text" class="form-control" ng-model="code" id="code" name="code" placeholder="Enter your confirmation code">
								<span class="input-group-btn">
									<button class="btn btn-success" ng-click="confirmCode()" type="button"><span class="glyphicon glyphicon-ok" aria-hidden="true">
								</span> CONFIRM CODE</button>
							</span>
							</div>
							<h2 ng-if="state === 'CreatePending' && !codePending">Click 'SEND CODE' to send a confirmation code to the specified email address.</h2>
							<h2 ng-if="state === 'EditPending' && !codePending">Click 'SEND CODE' to send a confirmation code to this profile's email address.</h2>
							<h2 ng-if="codePending">A confirmation code for this profile was sent to {{profile.email}} and is still pending; another cannot be sent at this time.</h2>
						</div>
						<div class="col-md-4"></div>
					</div>
					<!-- Message for when no profile is loaded -->
					<div class="row" ng-show="!profile && (state === 'View' || state === 'AdminReview')">
						<h2>No valid profile selected!</h2>
					</div>


					<div class="alert alert-{{alertType}} alert-dismissible" ng-class="{hideAlert: !alertMessage}">
						<button type="button" title="Close this alert." class="close" aria-label="Close" ng-click="removeAlert()"><span aria-hidden="true">&times;</span></button>{{alertMessage}}
					</div>



					<div class="buttons-group bottom-buttons"> 
						<button ng-show="profile && state === 'View'" type="button" ng-click="initializeEditProfile()" class="btn btn-warning">EDIT PROFILE</button> <!-- To initiate the editing process -->
						<button ng-show="profile && (state === 'CreatePending' || state === 'EditPending')" ng-disabled="codePending" type="button" ng-click="sendCode()" class="btn btn-warning">SEND CODE</button> <!-- To initiate the editing process -->
						<button ng-show="state === 'Create'" type="button" ng-click="createProfile()" class="btn btn-success">SUBMIT</button> <!-- For user submitting for first time -->
						<button ng-show="state === 'Edit'" type="button" ng-click="editProfile()" class="btn btn-success">SUBMIT</button> <!-- For user editing their profile -->
						<button ng-show="state === 'AdminReview'" type="button" ng-click="approveProfile(true)" class="btn btn-success">APPROVE PROFILE</button> <!-- For admin approving profile -->
						<button ng-show="state === 'AdminReview'" type="button" ng-click="approveProfile(false)" class="btn btn-danger">DENY PROFILE</button> <!-- For admin denying profile -->
						<a href="" class="btn btn-info" ng-click="redirectToHomepage(null, null)">LEAVE PAGE</a> <!-- For anyone to leave the page -->

						<div ng-show="isAdmin && (state === 'View' || state ==='AdminReview')" class="delete-button-holder"> <!-- Administrator-only delete profile button -->
							<button type="button" ng-click="deleteProfile()" class="btn btn-danger">DELETE PROFILE</button>
						</div>
					</div>
				</form>

			</div>

		</div>	

		<!-- Shared Site Footer -->
		<?php include '../include/site_footer.php'; ?>
	</body>
</html>