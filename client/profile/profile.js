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
    $scope.formData = []; //for form data
    $scope.formData.userIssuesExpertise = [];
    $scope.formData.userCountriesExpertise = [];
    $scope.formData.userRegionsExpertise = [];
    $scope.formData.userLanguages = [];
    $scope.formData.userCountriesExperience = [];
    $scope.errors = []; //list of form errors


    //setup if user is creating a new application
    if($scope.isCreating){
        $scope.maxFirstName = $scope.usersMaxLengths["firstname"];
        $scope.maxLastName = $scope.usersMaxLengths["lastname"];
        $scope.maxAffiliations = $scope.usersMaxLengths["affiliations"];
        $scope.maxAlternateEmail = $scope.usersMaxLengths["alternate_email"];
        $scope.maxPhone = $scope.usersMaxLengths["phone"];
        $scope.maxSocialLink = $scope.usersMaxLengths["social_link"];
        $scope.maxOtherIssues = $scope.usersMaxLengths["issues_expertise_other"];
        $scope.maxOtherCountriesExpertise = $scope.usersMaxLengths["countries_expertise_other"];
        $scope.maxOtherRegions = $scope.usersMaxLengths["regions_expertise_other"];
    }
    


    /*Functions*/

    $scope.addIssueExpertise = function(){
        var issue = JSON.parse(JSON.stringify(this.selectIssuesExpertise)); //deep copy object

        if(indexOfID(this.formData.userIssuesExpertise, issue) < 0){this.formData.userIssuesExpertise.push(issue);} //push if new
        this.selectIssuesExpertise = ""; //clear selection
    }

    $scope.addCountryExpertise = function(){
        var country = JSON.parse(JSON.stringify(this.selectCountriesExpertise)); //deep copy object

        if(indexOfID(this.formData.userCountriesExpertise, country) < 0){this.formData.userCountriesExpertise.push(country);} //push if new
        this.selectCountriesExpertise = ""; //clear selection
    }

    $scope.addRegionExpertise = function(){
        var region = JSON.parse(JSON.stringify(this.selectRegionsExpertise)); //deep copy object

        if(indexOfID(this.formData.userRegionsExpertise, region) < 0){this.formData.userRegionsExpertise.push(region);} //push if new
        this.selectRegionsExpertise = ""; //clear selection
    }

    $scope.addLanguage = function(){
        var language = JSON.parse(JSON.stringify(this.selectLanguages)); //deep copy object

        if(indexOfID(this.formData.userLanguages, language) < 0){this.formData.userLanguages.push(language);} //push if new
        this.selectLanguages = ""; //clear selection
    }

    $scope.addCountryExperience = function(){
        var country = JSON.parse(JSON.stringify(this.selectCountriesExperience)); //deep copy object

        if(indexOfID(this.formData.userCountriesExperience, country) < 0){country.experiences = []; country.otherExperience = ""; this.formData.userCountriesExperience.push(country);} //push if new
        this.selectCountriesExperience = ""; //clear selection
    }

    $scope.addCountryExperienceLevel = function(index){
        var experience = JSON.parse(JSON.stringify(this.formData.userCountriesExperience[index].selectedExperience)); //deep copy object

        if(indexOfID(this.formData.userCountriesExperience[index].experiences, experience) < 0){this.formData.userCountriesExperience[index].experiences.push(experience);} //push if new
        this.formData.userCountriesExperience[index].selectedExperience = ""; //clear selection
    }


    $scope.removeIssueExpertise = function(index){
        this.formData.userIssuesExpertise.splice(index, 1)
    }

    $scope.removeCountryExpertise = function(index){
        this.formData.userCountriesExpertise.splice(index, 1)
    }

    $scope.removeRegionExpertise = function(index){
        this.formData.userRegionsExpertise.splice(index, 1)
    }

    $scope.removeLanguage = function(index){
        this.formData.userLanguages.splice(index, 1)
    }

    $scope.removeCountryExperience = function(index){
        this.formData.userCountriesExperience.splice(index, 1)
    }

    $scope.removeCountryExperienceLevel = function(parentIndex, index){
        this.formData.userCountriesExperience[parentIndex].experiences.splice(index, 1);
    }


    /*Custom function that compares a given object's 'id' value to the given array's objects' 'id's to see if there is a match. Returns the index if it exists, or -1 otherwise.
    NOTE- it is assumed that both the specified object and objects in the specified array have the 'id' property!*/
    function indexOfID(testArray, testObject){
        for (var i = 0; i < testArray.length; i++) {
            if(testArray[i].id === testObject.id){return testObject.id;}
        }
        
        return -1;
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


    //submit the application - use a different function depending on the submitFunction variable
    $scope.submit = function(){
        if($scope.submitFunction === 'createProfile'){$scope.createProfile();}
    }


    //create a new profile; send data to the server for verification- if accepted, then redirect to homepage with message, otherwise display errors
    $scope.createProfile = function() {
        if(!confirm ('By submitting, your profile will become publicly searchable once an admin has approved it. ')) {return;} //submission confirmation required
        var fd = new FormData();
        
        $scope.loadingAlert(); //start a loading alert

        //loop through form data, appending each field to the FormData object
        for (var key in $scope.formData) {
            if ($scope.formData.hasOwnProperty(key)) {
                //console.log(key + " -> " + JSON.stringify($scope.formData[key]));
                fd.append(key, JSON.stringify($scope.formData[key]));
            }
        }

        $http({
            method  : 'POST',
            url     : '/../api.php?create_profile',
            data    : fd,  // pass in the FormData object
            transformRequest: angular.identity,
            headers : { 'Content-Type': undefined } //don't encode the formData array
        })
        .then(function (response) {
            console.log(response, 'res');
            //data = response.data;
            if(typeof response.data.success === 'undefined'){ //unexpected result!
                console.log(JSON.stringify(response, null, 4));
                $scope.alertType = "danger";
                $scope.alertMessage = "There was an unexpected error with your submission! Server response: " + JSON.stringify(response, null, 4);
            }
            else if(!response.data.success){ //there was at least 1 error
                $scope.errors = response.data.errors;
                $scope.alertType = "danger";
                if(typeof $scope.errors["other"] !== 'undefined') //there was an 'other' (non-normal) error
                {
                    if(Object.keys($scope.errors).length === 1){$scope.alertMessage = "There was a generic error with your submission: " + $scope.errors["other"];}//just the other error
                    else{$scope.alertMessage = "There was an error with your submission, please double check your form for errors, then try resubmitting. In addition, there was a generic error with your submission: " + $scope.errors["other"];}//the other error + normal errors
                }
                else {$scope.alertMessage = "There was an error with your submission, please double check your form for errors, then try resubmitting.";}//just normal errors
            }
            else{ //no errors
                $scope.errors = []; //clear any old errors
                var newAlertType = null;
                var newAlertMessage = null;
            }
           
        },function (error){
            console.log(error, 'can not get data.');
            $scope.alertType = "danger";
            $scope.alertMessage = "There was an unexpected error when trying to create your profile: " + error.status + " " + error.statusText + ". Please contact an admin about this issue!";
        });
    }


    //redirect the user to the homepage. Optionally, send an alert which will show up on the next page, consisting of a type(success, warning, danger, etc.) and message
    $scope.redirectToHomepage = function(alert_type, alert_message){
        var homeURL = '../home/home.php'; //url to homepage

        if(alert_type == null) //if no alert message to send, simply redirect
        {
            if($scope.isCreating)
            {
                if(!confirm ('Are you sure you want to leave this page? Any unsaved data will be lost.')){return;} //don't leave page if user decides not to
            }
            window.location.replace(homeURL);
        }
        else //if there IS an alert message to send, fill out an invisible form & submit so the data can be sent as POST
        {
            var form = $('<form type="hidden" action="' + homeURL + '" method="post">' +
                '<input type="text" name="alert_type" value="' + alert_type + '" />' +
                '<input type="text" name="alert_message" value="' + alert_message + '" />' +
            '</form>');
            $('body').append(form);
            form.submit();
        }
    }

}]);