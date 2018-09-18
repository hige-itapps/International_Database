//init app
var higeApp = angular.module('HIGE-app', []);

/*App Controller*/
higeApp.controller('profileCtrl', ['$scope', '$http', function($scope, $http){
    //get PHP init variables
    $scope.profile = scope_profile;
    $scope.isCreating = scope_isCreating;
    $scope.issues = scope_issues;
    $scope.countries = scope_countries;
    $scope.regions = scope_regions;
    $scope.languages = scope_languages;
    $scope.languageProficiencies = scope_languageProficiencies;
    $scope.countryExperiences = scope_countryExperiences;
    
    $scope.usersMaxLengths = scope_usersMaxLengths;
    $scope.maxOtherExperience = scope_maxOtherExperience;

    //user variables
    $scope.userIssuesExpertise = [];
    $scope.userCountriesExpertise = [];
    $scope.userRegionsExpertise = [];
    $scope.userLanguages = [];
    $scope.userCountriesExperience = [];


    //setup if user is creating a new application
    if($scope.isCreating){
        $scope.maxFirstName = $scope.usersMaxLengths["firstname"];
        $scope.maxLastName = $scope.usersMaxLengths["lastname"];
        $scope.maxAffiliations = $scope.usersMaxLengths["affiliations"];
        $scope.maxEmail = $scope.usersMaxLengths["email"];
        $scope.maxPhone = $scope.usersMaxLengths["phone"];
        $scope.maxSocialLink = $scope.usersMaxLengths["social_link"];
        $scope.maxOtherIssues = $scope.usersMaxLengths["issues_expertise_other"];
        $scope.maxOtherCountriesExpertise = $scope.usersMaxLengths["countries_expertise_other"];
        $scope.maxOtherRegions = $scope.usersMaxLengths["regions_expertise_other"];
    }
    


    /*Functions*/

    $scope.addIssueExpertise = function(){
        var issue = this.selectIssuesExpertise;

        if(this.userIssuesExpertise.indexOf(issue) === -1){this.userIssuesExpertise.push(issue);} //push if new
        this.selectIssuesExpertise = ""; //clear selection
    }

    $scope.addCountryExpertise = function(){
        var country = this.selectCountriesExpertise;

        if(this.userCountriesExpertise.indexOf(country) === -1){this.userCountriesExpertise.push(country);} //push if new
        this.selectCountriesExpertise = ""; //clear selection
    }

    $scope.addRegionExpertise = function(){
        var region = this.selectRegionsExpertise;

        if(this.userRegionsExpertise.indexOf(region) === -1){this.userRegionsExpertise.push(region);} //push if new
        this.selectRegionsExpertise = ""; //clear selection
    }

    $scope.addLanguage = function(){
        var language = this.selectLanguages;

        if(this.userLanguages.indexOf(language) === -1){this.userLanguages.push(language);} //push if new
        this.selectLanguages = ""; //clear selection
    }

    $scope.addCountryExperience = function(){
        var country = this.selectCountriesExperience;

        var isDuplicate = false;
        for (var i = 0; i < this.userCountriesExperience.length; i++) { //check for duplicate country IDs
            if(this.userCountriesExperience[i].id === country.id) {isDuplicate = true;}
        }

        if(!isDuplicate){country.experiences = []; country.otherExperience = ""; this.userCountriesExperience.push(country);} //push if new
        this.selectCountriesExperience = ""; //clear selection
    }

    $scope.addCountryExperienceLevel = function(index){
        var experience = this.userCountriesExperience[index].selectedExperience;

        if(this.userCountriesExperience[index].experiences.indexOf(experience) === -1){this.userCountriesExperience[index].experiences.push(experience);} //push if new
        this.userCountriesExperience[index].selectedExperience = ""; //clear selection
    }


    $scope.removeIssueExpertise = function(index){
        this.userIssuesExpertise.splice(index, 1)
    }

    $scope.removeCountryExpertise = function(index){
        this.userCountriesExpertise.splice(index, 1)
    }

    $scope.removeRegionExpertise = function(index){
        this.userRegionsExpertise.splice(index, 1)
    }

    $scope.removeLanguage = function(index){
        this.userLanguages.splice(index, 1)
    }

    $scope.removeCountryExperience = function(index){
        this.userCountriesExperience.splice(index, 1)
    }

    $scope.removeCountryExperienceLevel = function(parentIndex, index){
        this.userCountriesExperience[parentIndex].experiences.splice(index, 1);
    }

}]);