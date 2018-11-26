//init app
var higeApp = angular.module('HIGE-app', ['ui.bootstrap']);

/*Controller to set date inputs and list*/
higeApp.controller('listCtrl', function($scope) {
    //get PHP init variables
    $scope.searchProfile = scope_searchProfile; //profile to do an advanced search with

    $scope.isSearching = scope_isSearching; //boolean for if user is searching at all

    $scope.alertType = alert_type; //set the alert type if any
    $scope.alertMessage = alert_message; //set the alert message if any

    var tempProfiles = scope_profiles;
    $scope.profiles = new Array();
    for (var profile in tempProfiles){
        $scope.profiles.push( tempProfiles[profile] );
    }

    $scope.wildcard = scope_wildcard; //empty by default
    $scope.oldWildcard = $scope.wildcard; //copy of wildcard, so that the old term can be displayed
    $scope.advancedSearchEnabled = scope_advancedSearchEnabled; //will be false by default
    $scope.issues = scope_issues;
    $scope.countries = scope_countries;
    $scope.regions = scope_regions;
    $scope.languages = scope_languages;
    $scope.languageProficiencies = scope_languageProficiencies;
    $scope.countryExperiences = scope_countryExperiences;

    $scope.adminPendingProfiles = scope_adminPendingProfiles;

    $scope.filteredProfiles = [];

    //setup base searchProfile if not setup yet
    if(!$scope.searchProfile){$scope.searchProfile = [];}
    if(!$scope.searchProfile.issues_expertise){$scope.searchProfile.issues_expertise = [];}
    if(!$scope.searchProfile.countries_expertise){$scope.searchProfile.countries_expertise = [];}
    if(!$scope.searchProfile.regions_expertise){$scope.searchProfile.regions_expertise = [];}
    if(!$scope.searchProfile.languages){$scope.searchProfile.languages = [];}
    if(!$scope.searchProfile.countries_experience){$scope.searchProfile.countries_experience = {};}

    $scope.pagination = {
        currentPage:  1,
        numPerPage: 8
    };
   
    /*Functions*/

    $scope.submit = function(){
        //alert("submitting!");
        if($scope.advancedSearchEnabled){
            $scope.advancedSearch();
        }else{
            $scope.wildcardSearch();
        }
    }

    $scope.addIssueExpertise = function(){
        var issue = JSON.parse(JSON.stringify(this.selectIssuesExpertise)); //deep copy object

        if(indexOfID(this.searchProfile.issues_expertise, issue) < 0){this.searchProfile.issues_expertise.push(issue);} //push if new
        this.selectIssuesExpertise = ""; //clear selection
    }

    $scope.addCountryExpertise = function(){
        var country = JSON.parse(JSON.stringify(this.selectCountriesExpertise)); //deep copy object

        if(indexOfID(this.searchProfile.countries_expertise, country) < 0){this.searchProfile.countries_expertise.push(country);} //push if new
        this.selectCountriesExpertise = ""; //clear selection
    }

    $scope.addRegionExpertise = function(){
        var region = JSON.parse(JSON.stringify(this.selectRegionsExpertise)); //deep copy object

        if(indexOfID(this.searchProfile.regions_expertise, region) < 0){this.searchProfile.regions_expertise.push(region);} //push if new
        this.selectRegionsExpertise = ""; //clear selection
    }

    $scope.addLanguage = function(){
        var language = JSON.parse(JSON.stringify(this.selectLanguages)); //deep copy object

        if(indexOfID(this.searchProfile.languages, language) < 0){this.searchProfile.languages.push(language);} //push if new
        this.selectLanguages = ""; //clear selection
    }

    $scope.addCountryExperience = function(){
        var country = JSON.parse(JSON.stringify(this.selectCountriesExperience)); //deep copy object

        if(indexOfID(this.searchProfile.countries_experience, country) < 0){country.experiences = []; country.other_experience = ""; this.searchProfile.countries_experience[country.id] = country;} //add if new
        this.selectCountriesExperience = ""; //clear selection
    }

    $scope.addCountryExperienceLevel = function(index){
        var experience = JSON.parse(JSON.stringify(this.searchProfile.countries_experience[index].selectedExperience)); //deep copy object

        if(indexOfID(this.searchProfile.countries_experience[index].experiences, experience) < 0){this.searchProfile.countries_experience[index].experiences.push(experience);} //push if new
        this.searchProfile.countries_experience[index].selectedExperience = ""; //clear selection
    }


    $scope.removeIssueExpertise = function(index){
        this.searchProfile.issues_expertise.splice(index, 1)
    }

    $scope.removeCountryExpertise = function(index){
        this.searchProfile.countries_expertise.splice(index, 1)
    }

    $scope.removeRegionExpertise = function(index){
        this.searchProfile.regions_expertise.splice(index, 1)
    }

    $scope.removeLanguage = function(index){
        this.searchProfile.languages.splice(index, 1)
    }

    $scope.removeCountryExperience = function(index){
        delete this.searchProfile.countries_experience[index];
    }

    $scope.removeCountryExperienceLevel = function(parentIndex, index){
        this.searchProfile.countries_experience[parentIndex].experiences.splice(index, 1);
    }


    //display a generic loading alert to the page
    $scope.loadingAlert = function(){
        $scope.alertType = "info";
        $scope.alertMessage = "Loading...";
    }
    //remove the alert from the page
    $scope.removeAlert = function(){
        $scope.alertMessage = null;
    }


    /*Custom function that compares a given object's 'id' value to the given array's objects' 'id's to see if there is a match. Returns the index if it exists, or -1 otherwise.
    NOTE- it is assumed that both the specified object and objects in the specified array all have the 'id' property!*/
    function indexOfID(testArray, testObject){
        for (var i = 0; i < testArray.length; i++) {
            if(testArray[i].id === testObject.id){return testObject.id;}
        }
        return -1;
    }

    /* Function that returns an array of IDs for a given array of objects that contain an 'id' value. Returns id as an integer, not a string. 
    This can be reused for issues, countries, and regions of expertise arrays.
    NOTE- it is assumed that the objects in the specified array all have the 'id' property!*/
    function IDArrayFromObjectsArray(testArray){
        var retArray = [];
        for (var i = 0; i < testArray.length; i++) {
            retArray.push(parseInt(testArray[i].id));
        }
        return retArray;
    }

    /* Function that returns an array of specified languages in the format [[1, 2], 2, 3]. For each value in this array, if it is just 1 integer, then it is just the ID of the language.
    If it is a sub array of 2 values [x, y], the first value is the language ID, and the second is the proficiency ID.*/
    function languageIDsArray(){
        var retArray = [];
        var languages = $scope.searchProfile.languages;
        for (var i = 0; i < languages.length; i++){
            if(languages[i].proficiency_level){ //proficiency level included
                retArray.push([parseInt(languages[i].id), parseInt(languages[i].proficiency_level.id)]);
            }
            else{ //no specified proficiency
                retArray.push(parseInt(languages[i].id));
            }
        }
        return retArray;
    }

    /* Function that returns an array of specified country experiences in the format [1, ["id":2, "experiences":[1,2], "other_experience":other experience"]]. For each value in this array, if it is just 1 integer, then it is just the ID of the country.
    If is a sub list of 2 or more values, "id" is the country id, "experiences" is an array of country experiences IDs, and "other_experience" is the other experience string.*/
    function countryExperienceIDsArray(){
        var retArray = [];
        var countries = $scope.searchProfile.countries_experience;
        for (var country in countries) {
            //get values for this country
            if (countries.hasOwnProperty(country)) {
                var countryVals = countries[country];

                var countryID = parseInt(countryVals.id); //get ID

                var tempExperiences = [];
                for (var i = 0; i < countryVals.experiences.length; i++){ //get experiences as IDs only
                    tempExperiences.push(parseInt(countryVals.experiences[i].id));
                }

                //push data to retArray
                if(tempExperiences.length > 0 || countryVals.other_experience){ //other data besides just country ID
                    var finalData = {"id" : countryID}; //start off list with ID
                    if(tempExperiences.length > 0){finalData["experiences"] = tempExperiences;} //push regular experiences if applicable
                    if(countryVals.other_experience){finalData["other_experience"] = countryVals.other_experience;} //push other experience if applicable
                    retArray.push(finalData);
                }
                else{
                    retArray.push(countryID);
                }
            }
        }
        return retArray;
    }



    /*Checks to see if an object has any keys*/
    $scope.isObjectEmpty = function(item){
        return Object.keys(item).length === 0;
    }



    $scope.$watch('pagination.currentPage + pagination.numPerPage', function() {
        var begin = (($scope.pagination.currentPage - 1) * $scope.pagination.numPerPage);
        var end = begin + $scope.pagination.numPerPage;

        $scope.filteredProfiles = $scope.profiles.slice(begin, end);
    });
    
    $scope.wildcardSearch = function(){
        if ($scope.wildcard == null) {$scope.wildcard = "";}

        if(!$scope.adminPendingProfiles){ //for regular users, redirect with wildcard
            window.location.replace("?search&wildcard=" + $scope.wildcard);
        }
        else{ //for admins searching pending profiles, specify that with &pending
            window.location.replace("?search&pending&wildcard=" + $scope.wildcard);
        }
    }

    $scope.advancedSearch = function(){
        var searchURL = "?search&advanced"; //the URL to be redirected to
        if($scope.adminPendingProfiles){
            searchURL += "&pending"; //specify pending only if admin is searching pending profiles
        }
        //add parameters here as necessary
        
        //add profile summary text fields
        if($scope.searchProfile.name){searchURL += "&name="+encodeURIComponent($scope.searchProfile.name);} //add name if specified
        if($scope.searchProfile.affiliations){searchURL += "&affiliations="+encodeURIComponent($scope.searchProfile.affiliations);} //add affiliations if specified
        if($scope.searchProfile.email){searchURL += "&email="+encodeURIComponent($scope.searchProfile.email);} //add email address if specified
        if($scope.searchProfile.phone){searchURL += "&phone="+encodeURIComponent($scope.searchProfile.phone);} //add phone number if specified
        if($scope.searchProfile.social_link){searchURL += "&social_link="+encodeURIComponent($scope.searchProfile.social_link);} //add social link if specified

        //add other fields
        if($scope.searchProfile.issues_expertise.length != 0){searchURL += "&issues_expertise="+encodeURIComponent(JSON.stringify(IDArrayFromObjectsArray($scope.searchProfile.issues_expertise)));} //add issues of expertise if specified
        if($scope.searchProfile.issues_expertise_other){searchURL += "&issues_expertise_other="+encodeURIComponent($scope.searchProfile.issues_expertise_other);} //add other issues of expertise if specified

        if($scope.searchProfile.countries_expertise.length != 0){searchURL += "&countries_expertise="+encodeURIComponent(JSON.stringify(IDArrayFromObjectsArray($scope.searchProfile.countries_expertise)));} //add countries of expertise if specified
        if($scope.searchProfile.countries_expertise_other){searchURL += "&countries_expertise_other="+encodeURIComponent($scope.searchProfile.countries_expertise_other);} //add other countries of expertise if specified
        
        if($scope.searchProfile.regions_expertise.length != 0){searchURL += "&regions_expertise="+encodeURIComponent(JSON.stringify(IDArrayFromObjectsArray($scope.searchProfile.regions_expertise)));} //add regions of expertise if specified
        if($scope.searchProfile.regions_expertise_other){searchURL += "&regions_expertise_other="+encodeURIComponent($scope.searchProfile.regions_expertise_other);} //add other regions of expertise if specified

        if($scope.searchProfile.languages.length != 0){searchURL += "&languages="+encodeURIComponent(JSON.stringify(languageIDsArray()));} //add languages if specified

        if(Object.keys($scope.searchProfile.countries_experience).length != 0){searchURL += "&countries_experience="+encodeURIComponent(JSON.stringify(countryExperienceIDsArray()));} //add country experiences if specified

        window.location.replace(searchURL);
    }

    $scope.turnOnAdvancedSearch = function(){
        $scope.advancedSearchEnabled = true;
    }

    $scope.turnOffAdvancedSearch = function(){
        $scope.advancedSearchEnabled = false;
    }
});