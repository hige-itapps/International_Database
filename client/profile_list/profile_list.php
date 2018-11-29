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

	$searchProfile = null;
	$isSearching = false; //set to true if user is searching at all
	$profiles = [];
	$wildcard = "";
	$advancedSearchEnabled = false; //set to true if 'advanced' appears as a GET parameter
	$issues = $database->getIssues();
	$countries = $database->getCountries();
	$regions = $database->getRegions();
	$languages = $database->getLanguages();
	$languageProficiencies = $database->getLanguageProficiencies();
	$countryExperiences = $database->getCountryExperiences();

	$adminPendingProfiles = false; //set to true if admin is getting pending profiles only

	$alertType = isset($_POST["alert_type"]) ? $_POST["alert_type"] : null; //set the alert type if it exists, otherwise set to null
	$alertMessage = isset($_POST["alert_message"]) ? $_POST["alert_message"] : null; //set the alert type if it exists, otherwise set to null

	if(isset($CASbroncoNetID) && $database->isAdministrator($CASbroncoNetID) && isset($_GET["pending"])){ //admin trying to get pending profiles
		$adminPendingProfiles = true;
	}

	if(isset($_GET["search"])){ //any user trying to search profiles
		$isSearching = true;

		if(isset($_GET["advanced"])){
			$advancedSearchEnabled = true;

			//initialize search parameters to null, and set them as needed.
			$searchName = $searchAffiliations = $searchEmail = $searchPhone = $searchSocialLink = $searchIssuesExpertise = $searchIssuesExpertiseOther =
				$searchCountriesExpertise = $searchCountriesExpertiseOther = $searchRegionsExpertise = $searchRegionsExpertiseOther = $searchLanguages = $searchCountriesExperience = null;

			//set searchProfile parameters, so that any filled-out fields will remain filled out upon reload.

			//profile summary text fields
			if(isset($_GET["name"])){
				$searchName = $_GET["name"];
				$searchProfile["name"] = $searchName;
			}
			if(isset($_GET["affiliations"])){
				$searchAffiliations = $_GET["affiliations"];
				$searchProfile["affiliations"] = $searchAffiliations;
			}
			if(isset($_GET["email"])){
				$searchEmail = $_GET["email"];
				$searchProfile["email"] = $searchEmail;
			}
			if(isset($_GET["phone"])){
				$searchPhone = $_GET["phone"];
				$searchProfile["phone"] = $searchPhone;
			}
			if(isset($_GET["social_link"])){
				$searchSocialLink = $_GET["social_link"];
				$searchProfile["social_link"] = $searchSocialLink;
			}

			//other fields
			//issues of expertise
			if(isset($_GET["issues_expertise"])){
				$searchIssuesExpertise = json_decode($_GET["issues_expertise"]); //decode values
				$new_issues_expertise = []; //init new array
				for($i = 0; $i < sizeof($searchIssuesExpertise); $i++) { $new_issues_expertise[$i] = $issues[$searchIssuesExpertise[$i]]; } //add each issue as it corresponds with the issues array
				$searchProfile["issues_expertise"] = $new_issues_expertise;
			}
			if(isset($_GET["issues_expertise_other"])){
				$searchIssuesExpertiseOther = $_GET["issues_expertise_other"];
				$searchProfile["issues_expertise_other"] = $searchIssuesExpertiseOther;
			}

			//countries of expertise
			if(isset($_GET["countries_expertise"])){
				$searchCountriesExpertise = json_decode($_GET["countries_expertise"]); //decode values
				$new_countries_expertise = []; //init new array
				for($i = 0; $i < sizeof($searchCountriesExpertise); $i++) { $new_countries_expertise[$i] = $countries[$searchCountriesExpertise[$i]]; } //add each country as it corresponds with the countries array
				$searchProfile["countries_expertise"] = $new_countries_expertise;
			}
			if(isset($_GET["countries_expertise_other"])){
				$searchCountriesExpertiseOther = $_GET["countries_expertise_other"];
				$searchProfile["countries_expertise_other"] = $searchCountriesExpertiseOther;
			}

			//regions of expertise
			if(isset($_GET["regions_expertise"])){
				$searchRegionsExpertise = json_decode($_GET["regions_expertise"]); //decode values
				$new_regions_expertise = []; //init new array
				for($i = 0; $i < sizeof($searchRegionsExpertise); $i++) { $new_regions_expertise[$i] = $regions[$searchRegionsExpertise[$i]]; } //add each region as it corresponds with the regions array
				$searchProfile["regions_expertise"] = $new_regions_expertise;
			}
			if(isset($_GET["regions_expertise_other"])){
				$searchRegionsExpertiseOther = $_GET["regions_expertise_other"];
				$searchProfile["regions_expertise_other"] = $searchRegionsExpertiseOther;
			}

			//languages
			if(isset($_GET["languages"])){
				$searchLanguages = json_decode($_GET["languages"]); //decode values
				$new_languages = []; //init new array
				for($i = 0; $i < sizeof($searchLanguages); $i++) { //add each language as it corresponds with the languages array
					if(is_array($searchLanguages[$i])){ //if array (has a specified proficiency)
						$new_languages[$i] = $languages[$searchLanguages[$i][0]];
						$new_languages[$i]["proficiency_level"] = $languages[$searchLanguages[$i][1]];
					}
					else{ //just an integer (no specified proficiency)
						$new_languages[$i] = $languages[$searchLanguages[$i]]; 
					}
				}
				$searchProfile["languages"] = $new_languages;
			}

			//country experience
			if(isset($_GET["countries_experience"])){
				$searchCountriesExperience = json_decode($_GET["countries_experience"]); //decode values
				$new_countries_experience = []; //init new array
				for($i = 0; $i < sizeof($searchCountriesExperience); $i++) { //add each country as it corresponds with the countries array
					if(is_object($searchCountriesExperience[$i])){ //if array (has specified experiences and/or other experience)
						$newID = $searchCountriesExperience[$i]->id;
						$new_countries_experience[$newID] = $countries[$newID];
						$new_countries_experience[$newID]["experiences"] = []; //init experiences array
						
						if (property_exists($searchCountriesExperience[$i], 'experiences')){ //if experiences are specified, then add each one as it corresponds with the countryExperiences array
							$new_experiences = $searchCountriesExperience[$i]->experiences;
							for($e = 0; $e < sizeof($new_experiences); $e++) {
								$new_countries_experience[$newID]["experiences"][] = $countryExperiences[$new_experiences[$e]];
							}
						}

						if (property_exists($searchCountriesExperience[$i], 'other_experience')){
							$new_countries_experience[$newID]["other_experience"] = $searchCountriesExperience[$i]->other_experience;
						}
					}
					else{ //just an integer (no specified experiences)
						$newID = $searchCountriesExperience[$i];
						$new_countries_experience[$newID] = $countries[$newID]; 
						$new_countries_experience[$newID]["experiences"] = [];
					}
				}
				$searchProfile["countries_experience"] = $new_countries_experience;
			}

			//retrieve all relevant profiles
			if(!$adminPendingProfiles){ //for regular users
				$profiles = $database->advancedSearch($searchName, $searchAffiliations, $searchEmail, $searchPhone, $searchSocialLink, $searchIssuesExpertise, $searchIssuesExpertiseOther,
					$searchCountriesExpertise, $searchCountriesExpertiseOther, $searchRegionsExpertise, $searchRegionsExpertiseOther, $searchLanguages, $searchCountriesExperience);
			}
			else{ //for admins searching pending profiles
				$profiles = $database->advancedSearch($searchName, $searchAffiliations, $searchEmail, $searchPhone, $searchSocialLink, $searchIssuesExpertise, $searchIssuesExpertiseOther,
					$searchCountriesExpertise, $searchCountriesExpertiseOther, $searchRegionsExpertise, $searchRegionsExpertiseOther, $searchLanguages, $searchCountriesExperience, null);
			}
		}
		if(isset($_GET["wildcard"])){
			$wildcard = $_GET["wildcard"];
			if(!$adminPendingProfiles){ //for regular users
				$profiles = $database->searchByWildcard($wildcard);
			}
			else{ //admins searching pending profiles
				$profiles = $database->searchByWildcard($wildcard, null);
			}
		}
	}

	if($adminPendingProfiles){ //if the admin is getting pending profiles, go through each profile and determine if it is a new one, or an update to an old one

		if(!isset($_GET["advanced"]) && !isset($_GET["wildcard"])){//if admin hadn't searched anything yet, return all pending profiles by default
			$profiles = $database->getAllUsersSummaries(null);
		}

		foreach ($profiles as $key => &$value) {
			$value["newProfile"] = true;
			if(boolval($database->doesProfileExist($value["email"]))){
				$value["newProfile"] = false;
			}
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
			var scope_searchProfile = <?php echo json_encode($searchProfile); ?>;
			var scope_isSearching = <?php echo json_encode($isSearching); ?>;
			var scope_profiles = <?php echo json_encode($profiles); ?>;
			var scope_wildcard = <?php echo json_encode($wildcard); ?>;
			var scope_advancedSearchEnabled = <?php echo json_encode($advancedSearchEnabled); ?>;
			var scope_issues = <?php echo json_encode($issues); ?>;
			var scope_countries = <?php echo json_encode($countries); ?>;
			var scope_regions = <?php echo json_encode($regions); ?>;
			var scope_languages = <?php echo json_encode($languages); ?>;
			var scope_languageProficiencies = <?php echo json_encode($languageProficiencies); ?>;
			var scope_countryExperiences = <?php echo json_encode($countryExperiences); ?>;
			var scope_adminPendingProfiles = <?php echo json_encode($adminPendingProfiles); ?>;
			var alert_type = <?php echo json_encode($alertType); ?>;
			var alert_message = <?php echo json_encode($alertMessage); ?>;
		</script>
		<!-- AngularJS Script -->
		<script type="module" src="profile_list.js"></script>
	</head>

	<!-- Page Body -->
	<body ng-app="HIGE-app">
		<!-- Shared Site Banner -->
		<?php include '../include/site_banner.php'; ?>

		<div id="MainContent" role="main">
			<?php $siteWarning->showIfExists() ?> <!-- show site warning if it exists -->
			<script src="../include/outdatedbrowser.js" nomodule></script> <!-- show site error if outdated -->
			<?php include '../include/noscript.html'; ?> <!-- show site error if javascript is disabled -->

			<div class="container-fluid" ng-controller="listCtrl" id="listCtrl">
				<div class="row">
					<h1 class="title" ng-if="!adminPendingProfiles">Search The Database</h1>
					<h1 class="title" ng-if="adminPendingProfiles">Search Pending Profiles And Updates</h1>
					<!-- search form -->
					<form enctype="multipart/form-data" class="form-horizontal" id="profileSearchForm" name="profileSearchForm" ng-submit="submit()">
						<!-- Form for regular wildcard search -->
						<div class="form-group" ng-show="!advancedSearchEnabled">
							<label for="wildcardSearch">Search for any name, email address, affiliation, phone number, social link, country, region, issue, or language:</label>
							<div class="input-group">
								<input type="search" class="form-control" ng-model="wildcard" id="wildcardSearch" name="wildcardSearch" placeholder="Enter A Key Term To Search For">
								<span class="input-group-btn">
									<button class="btn btn-primary" type="submit"><span class="glyphicon glyphicon-search" aria-hidden="true">
								</span> Search</button>
							</span>
							</div>
						</div>

						<!-- Form for advanced search -->
						<div ng-show="advancedSearchEnabled" class="advancedSearch">
							<h2>Advanced Search: search any combination of fields. This is an inclusive search, so the profiles you receive have to include every field you fill out. 
							Text fields only need to match partially and are not case sensitive (so for example, typing 'steve' in the name field will return any profiles with the name 'Steven').</h2>
							<!-- Text Field Searches -->
							<div class = "row">
								<div class="col-md-4 form-group">
									<label for="name">Name:</label>
									<input type="text" class="form-control" ng-model="searchProfile.name" id="name" name="name" placeholder="Enter Name"/>
								</div>
								<div class="col-md-4 form-group">
									<label for="affiliations">Affiliations:</label>
									<input type="text" class="form-control" ng-model="searchProfile.affiliations" id="affiliations" name="affiliations" placeholder="Enter Affiliations"/>
								</div>
								<div class="col-md-4 form-group">
									<label for="email">Email Address:</label>
									<input type="text" class="form-control" ng-model="searchProfile.email" id="email" name="email" placeholder="Enter Email Address"/>
								</div>
							</div>
							<div class="row">
								<div class="col-md-6 form-group">
									<label for="phone">Phone Number:</label>
									<input type="text" class="form-control" ng-model="searchProfile.phone" id="phone" name="phone" placeholder="Enter Phone Number" onkeypress='return (event.which >= 48 && event.which <= 57)'/>
								</div>
								<div class="col-md-6 form-group">
									<label for="social_link">Social Link:</label>
									<input type="text" class="form-control" ng-model="searchProfile.social_link" id="social_link" name="social_link" placeholder="Enter Social Link"/>
								</div>
							</div>

							<!-- List Dropdown Searches -->
							<div class = "row">
								<!-- Issues of Expertise -->
								<div class="col-md-4">
									<div class="form-group">
										<label for="issuesExpertise">Issues of Expertise:</label>
										<select class="form-control" ng-change="addIssueExpertise()" ng-model="selectIssuesExpertise" id="issuesExpertise" name="issuesExpertise"
											ng-options="issue as issue.issue for issue in issues">
										</select>
									</div>

									<h3 ng-show="searchProfile.issues_expertise.length > 0">Selected Issues:</h3>
									<ul class="user-list">
										<li ng-repeat="issue in searchProfile.issues_expertise">{{issue.issue}} <a href ng-click="removeIssueExpertise($index)" class="btn btn-danger">delete</a></li>
									</ul>
									<div class="form-group">
										<label for="issuesExpertiseOther">Other Issues of Expertise:</label>
										<input type="text" class="form-control" ng-model="searchProfile.issues_expertise_other" id="issuesExpertiseOther" name="issuesExpertiseOther" placeholder="Enter Any Other Issues Of Expertise" />
									</div>
								</div>
								<!-- Countries of Expertise -->
								<div class="col-md-4">
									<div class="form-group">
										<label for="countriesExpertise">Countries of Expertise:</label>
										<select class="form-control" ng-change="addCountryExpertise()" ng-model="selectCountriesExpertise" id="countriesExpertise" name="countriesExpertise"
											ng-options="country as country.country_name for country in countries">
										</select>
									</div>

									<h3 ng-show="searchProfile.countries_expertise.length > 0">Selected Countries:</h3>
									<ul class="user-list">
										<li ng-repeat="country in searchProfile.countries_expertise">{{country.country_name}} <a href ng-click="removeCountryExpertise($index)" class="btn btn-danger">delete</a></li>
									</ul>
									<div class="form-group">
										<label for="countriesExpertiseOther">Other Countries of Expertise:</label>
										<input type="text" class="form-control" ng-model="searchProfile.countries_expertise_other" id="countriesExpertiseOther" name="countriesExpertiseOther" placeholder="Enter Any Other Countries Of Expertise" />
									</div>
								</div>
								<!-- Regions of Expertise -->
								<div class="col-md-4">
									<div class="form-group">
										<label for="regionsExpertise">Regions of Expertise:</label>
										<select class="form-control" ng-change="addRegionExpertise()" ng-model="selectRegionsExpertise" id="regionsExpertise" name="regionsExpertise"
											ng-options="region as region.region for region in regions">
										</select>
									</div>

									<h3 ng-show="searchProfile.regions_expertise.length > 0">Selected Regions:</h3>
									<ul class="user-list">
										<li ng-repeat="region in searchProfile.regions_expertise">{{region.region}} <a href ng-click="removeRegionExpertise($index)" class="btn btn-danger">delete</a></li>
									</ul>
									<div class="form-group">
										<label for="regionsExpertiseOther">Other Regions of Expertise:</label>
										<input type="text" class="form-control" ng-model="searchProfile.regions_expertise_other" id="regionsExpertiseOther" name="regionsExpertiseOther" placeholder="Enter Any Other Regions Of Expertise" />
									</div>
								</div>
							</div>
							<div class="row">
								<!-- Languages -->
								<div class="col-md-6">
									<div class="form-group">
										<label for="languages">Languages:</label>
										<select class="form-control" ng-change="addLanguage()" ng-model="selectLanguages" id="languages" name="languages"
											ng-options="language as language.name for language in languages">
										</select>
									</div>

									<h3 ng-show="searchProfile.languages.length > 0">Selected Languages:</h3>
									<ul class="user-list">
										<li ng-repeat="language in searchProfile.languages">{{language.name}} 
											<label for="proficiency{{$index}}">Proficiency Level:</label>
											<select class="form-control" ng-model="searchProfile.languages[($index)].proficiency_level" id="proficiency{{$index}}" name="proficiency{{$index}}"
												ng-options="proficiency as proficiency.proficiency_level for proficiency in languageProficiencies track by proficiency.id">
												<option value="">any proficiency</option>
											</select>
											<a href ng-click="removeLanguage($index)" class="btn btn-danger">delete</a>
										</li>
									</ul>
								</div>
								<!-- Country Experience -->
								<div class="col-md-6">
									<div class="form-group">
										<label for="countriesExperience">Country Experience:</label>
										<select class="form-control" ng-change="addCountryExperience()" ng-model="selectCountriesExperience" id="countriesExperience" name="countriesExperience"
											ng-options="country as country.country_name for country in countries">
										</select>
									</div>

									<h3 ng-show="!isObjectEmpty(searchProfile.countries_experience)">Selected Countries:</h3>
									<ul class="user-list">
										<li ng-repeat="(index, country) in searchProfile.countries_experience">{{country.country_name}} 
											<label for="countryExperience{{index}}">Experiences:</label>
											<select class="form-control"  ng-change="addCountryExperienceLevel(index)" ng-model="searchProfile.countries_experience[index].selectedExperience" id="countryExperience{{index}}" name="countryExperience{{index}}"
												ng-options="countryExperience as countryExperience.experience for countryExperience in countryExperiences">
											</select>
											<a href ng-click="removeCountryExperience(index)" class="btn btn-danger">delete</a>
											<h4 ng-show="searchProfile.countries_experience[index].experiences.length > 0">Selected Experiences:</h4>
											<ul>
												<li ng-repeat="experience in searchProfile.countries_experience[index].experiences">{{experience.experience}} 
													<a href ng-click="removeCountryExperienceLevel($parent.index, $index)" class="btn btn-danger">delete</a>
												</li>
											</ul>
											<div class="form-group">
												<label for="countryExperienceOther{{index}}">Other Experience:</label>
												<input type="text" class="form-control" ng-model="searchProfile.countries_experience[index].other_experience" id="countryExperienceOther{{index}}" name="countryExperienceOther{{index}}" placeholder="Enter Any Other Experiences Had In This Country" />
											</div>
										</li>
									</ul>
								</div>
							</div>
						</div>

						<button type="submit" class="btn btn-primary" ng-if="advancedSearchEnabled"><span class="glyphicon glyphicon-search" aria-hidden="true"></span> SEARCH</button>

						<button type="button" class="btn btn-warning" ng-if="!advancedSearchEnabled" ng-click="turnOnAdvancedSearch()">ADVANCED SEARCH</button>
						<button type="button" class="btn btn-warning" ng-if="advancedSearchEnabled" ng-click="turnOffAdvancedSearch()">REGULAR SEARCH</button>
					</form>
				</div>
				<div class="row">
					<h1 class="title" ng-if="oldWildcard">{{profiles.length}} Result{{profiles.length === 1 ? "" : "s"}} For "{{oldWildcard}}":</h2>
					<nav class="col-md-12" aria-label="Top Page List">
						<ul ng-if="profiles.length" uib-pagination total-items="profiles.length" ng-model="pagination.currentPage" items-per-page="pagination.numPerPage"></ul>
					</nav>

					<h2 ng-if="isSearching && !profiles.length">No profiles match the search terms!</h2>

					<div class="col-md-12 profile-list-wrapper">
						<div class="profile-list">
							<div class="profile profile-summary" ng-repeat="profile in filteredProfiles">
								<div class="profile-summary-newProfile" ng-if="profile.newProfile == true"><span>NEW</span></div>
								<div class="profile-summary-updatedProfile" ng-if="profile.newProfile == false"><span>UPDATE</span></div>
								<h2 class="profile-summary-name">{{profile.firstname}} {{profile.lastname}}</h2>
								<hr>
								<h3 class="profile-summary-affiliations" >{{profile.affiliations}}</h3>
								<h3 class="profile-summary-email">{{profile.email}}</h3>
								<h2 class="profile-summary-foundIn" ng-if="oldWildcard">Key Term Found In</h2>
								<ul ng-if="oldWildcard" class="compactList profile-summary-wildcard">
									<li ng-repeat="category in profile.foundIn">{{category}}</li>
								</ul>
								<div class="profile-button">
									<a class="btn btn-success" href="../profile/profile.php?id={{profile.id}}{{adminPendingProfiles ? '&review' : '' }}">View Profile</a>
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

		<!-- Shared Site Footer -->
		<?php include '../include/site_footer.php'; ?>
	</body>
</html>