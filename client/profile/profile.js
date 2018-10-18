//init app
var higeApp = angular.module('HIGE-app', []);

/*App Controller*/
higeApp.controller('profileCtrl', ['$scope', '$http', function($scope, $http){
    //get PHP init variables
    $scope.profile = scope_profile;
    $scope.isCreating = scope_isCreating;
    $scope.isEditing = $scope.isCreating; //copy the 'isCreating' value to 'isEditing'
    $scope.issues = scope_issues;
    $scope.countries = scope_countries;
    $scope.regions = scope_regions;
    $scope.languages = scope_languages;
    $scope.languageProficiencies = scope_languageProficiencies;
    $scope.countryExperiences = scope_countryExperiences;
    $scope.codePending = scope_codePending;
    
    $scope.usersMaxLengths = scope_usersMaxLengths;
    $scope.maxOtherExperience = scope_maxOtherExperience;

    //user variables
    if(!$scope.profile){$scope.profile = [];}
    if(!$scope.profile.issues_expertise){$scope.profile.issues_expertise = [];}
    if(!$scope.profile.countries_expertise){$scope.profile.countries_expertise = [];}
    if(!$scope.profile.regions_expertise){$scope.profile.regions_expertise = [];}
    if(!$scope.profile.languages){$scope.profile.languages = [];}
    if(!$scope.profile.countries_experience){$scope.profile.countries_experience = {};}
    $scope.errors = []; //list of form errors
    $scope.code = ''; //the user's specified code
    $scope.expiration_timestamp = null; //set to the code's expiration timestamp if there is one
    $scope.wantsToEdit = false; //set to true if user wishes to edit their profile
    $scope.codeVerified = false; //set to true if the code & login_email combination is verified as correct

    $scope.maxFirstName = $scope.usersMaxLengths["firstname"];
    $scope.maxLastName = $scope.usersMaxLengths["lastname"];
    $scope.maxAffiliations = $scope.usersMaxLengths["affiliations"];
    $scope.maxLoginEmail = $scope.usersMaxLengths["login_email"];
    $scope.maxAlternateEmail = $scope.usersMaxLengths["alternate_email"];
    $scope.maxPhone = $scope.usersMaxLengths["phone"];
    $scope.maxSocialLink = $scope.usersMaxLengths["social_link"];
    $scope.maxOtherIssues = $scope.usersMaxLengths["issues_expertise_other"];
    $scope.maxOtherCountriesExpertise = $scope.usersMaxLengths["countries_expertise_other"];
    $scope.maxOtherRegions = $scope.usersMaxLengths["regions_expertise_other"];
    


    /*Functions*/

    $scope.addIssueExpertise = function(){
        var issue = JSON.parse(JSON.stringify(this.selectIssuesExpertise)); //deep copy object

        if(indexOfID(this.profile.issues_expertise, issue) < 0){this.profile.issues_expertise.push(issue);} //push if new
        this.selectIssuesExpertise = ""; //clear selection
    }

    $scope.addCountryExpertise = function(){
        var country = JSON.parse(JSON.stringify(this.selectCountriesExpertise)); //deep copy object

        if(indexOfID(this.profile.countries_expertise, country) < 0){this.profile.countries_expertise.push(country);} //push if new
        this.selectCountriesExpertise = ""; //clear selection
    }

    $scope.addRegionExpertise = function(){
        var region = JSON.parse(JSON.stringify(this.selectRegionsExpertise)); //deep copy object

        if(indexOfID(this.profile.regions_expertise, region) < 0){this.profile.regions_expertise.push(region);} //push if new
        this.selectRegionsExpertise = ""; //clear selection
    }

    $scope.addLanguage = function(){
        var language = JSON.parse(JSON.stringify(this.selectLanguages)); //deep copy object

        if(indexOfID(this.profile.languages, language) < 0){this.profile.languages.push(language);} //push if new
        this.selectLanguages = ""; //clear selection
    }

    $scope.addCountryExperience = function(){
        var country = JSON.parse(JSON.stringify(this.selectCountriesExperience)); //deep copy object

        if(indexOfID(this.profile.countries_experience, country) < 0){country.experiences = []; country.other_experience = ""; this.profile.countries_experience[country.id] = country;} //add if new
        this.selectCountriesExperience = ""; //clear selection
    }

    $scope.addCountryExperienceLevel = function(index){
        var experience = JSON.parse(JSON.stringify(this.profile.countries_experience[index].selectedExperience)); //deep copy object

        if(indexOfID(this.profile.countries_experience[index].experiences, experience) < 0){this.profile.countries_experience[index].experiences.push(experience);} //push if new
        this.profile.countries_experience[index].selectedExperience = ""; //clear selection
    }


    $scope.removeIssueExpertise = function(index){
        this.profile.issues_expertise.splice(index, 1)
    }

    $scope.removeCountryExpertise = function(index){
        this.profile.countries_expertise.splice(index, 1)
    }

    $scope.removeRegionExpertise = function(index){
        this.profile.regions_expertise.splice(index, 1)
    }

    $scope.removeLanguage = function(index){
        this.profile.languages.splice(index, 1)
    }

    $scope.removeCountryExperience = function(index){
        delete this.profile.countries_experience[index];
    }

    $scope.removeCountryExperienceLevel = function(parentIndex, index){
        this.profile.countries_experience[parentIndex].experiences.splice(index, 1);
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


    //create a new profile; send data to the server for verification- if accepted, then redirect to homepage with message, otherwise display errors
    $scope.createProfile = function() {
        console.log($scope.profile);

        if(!confirm ('By submitting, your profile will be added to the database, and will become publicly searchable once an admin has approved it. ')) {return;} //submission confirmation required
        var fd = new FormData();
        
        $scope.loadingAlert(); //start a loading alert

        //loop through form data, appending each field to the profile object
        for (var key in $scope.profile) {
            if ($scope.profile.hasOwnProperty(key)) {
                fd.append(key, JSON.stringify($scope.profile[key]));
            }
        }

        $http({
            method  : 'POST',
            url     : '../api.php?create_profile',
            data    : fd,  // pass in the profile object
            transformRequest: angular.identity,
            headers : { 'Content-Type': undefined } //don't encode the profile array
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
                    if(Object.keys($scope.errors).length === 1){$scope.alertMessage = "There was an error with your submission: " + $scope.errors["other"];}//just the other error
                    else{$scope.alertMessage = "There was an error with your submission, please double check your form for errors, then try resubmitting. In addition, there was another error with your submission: " + $scope.errors["other"];}//the other error + normal errors
                }
                else {$scope.alertMessage = "There was an error with your submission, please double check your form for errors, then try resubmitting.";}//just normal errors
            }
            else{ //no errors
                $scope.errors = []; //clear any old errors
                var newAlertType = "success";
                var newAlertMessage = "Success! Your profile has been submitted and is awaiting administrator approval. Once approved, it will be publicly searchable.";
                $scope.redirectToHomepage(newAlertType, newAlertMessage); //redirect to the homepage with the message
            }
           
        },function (error){
            console.log(error, 'can not get data.');
            $scope.alertType = "danger";
            $scope.alertMessage = "There was an unexpected error when trying to create your profile: " + error.status + " " + error.statusText + ". Please contact an admin about this issue!";
        });
    }


    //initiate the editing process
    $scope.editProfile = function(){
        $scope.wantsToEdit = true;
    }


    //try to send a confirmation code
    $scope.sendCode = function(){
        $scope.loadingAlert(); //start a loading alert
        $http({
            method  : 'POST',
            url     : '../api.php?send_code',
            data    : $.param({email: $scope.profile.email}),  // pass in the profile object
            headers : { 'Content-Type': 'application/x-www-form-urlencoded' }  //standard paramater encoding
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
                $scope.alertType = "danger";
                $scope.alertMessage = "Error sending code: " + JSON.stringify(response.data.error);
            }
            else{ //no errors
                $scope.alertType = "success";
                $scope.alertMessage = "A confirmation code was successfully sent out to this profile's email address.";
                $scope.codePending = true;
            }
           
        },function (error){
            console.log(error, 'can not get data.');
            $scope.alertType = "danger";
            $scope.alertMessage = "There was an unexpected error when trying to send your code: " + error.status + " " + error.statusText + ". Please contact an admin about this issue!";
        });
    }


    //try to confirm a confirmation code
    $scope.confirmCode = function(){
        $scope.loadingAlert(); //start a loading alert
        $http({
            method  : 'POST',
            url     : '../api.php?confirm_code',
            data    : $.param({userID: $scope.profile.id, email: $scope.profile.email, code: $scope.code}),  // pass in the profile object
            headers : { 'Content-Type': 'application/x-www-form-urlencoded' }  //standard paramater encoding
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
                $scope.alertType = "danger";
                $scope.alertMessage = "Error confirming code: " + JSON.stringify(response.data.error);
            }
            else{ //no errors
                $scope.alertType = "success";
                $scope.alertMessage = "Confirmation Code Verified! Please submit your edits before the specified expiration time.";
                $scope.wantsToEdit = false;
                $scope.isEditing = true;
                $scope.profile.login_email = response.data.login_email; //set the wmu address
                $scope.profile.alternate_email = response.data.alternate_email; //set the optional non-wmu address
                $scope.expiration_timestamp = response.data.expiration_time; //set the expiration time
            }
           
        },function (error){
            console.log(error, 'can not get data.');
            $scope.alertType = "danger";
            $scope.alertMessage = "There was an unexpected error when trying to confirm your code: " + error.status + " " + error.statusText + ". Please contact an admin about this issue!";
        });
    }


    //redirect the user to the homepage. Optionally, send an alert which will show up on the next page, consisting of a type(success, warning, danger, etc.) and message
    $scope.redirectToHomepage = function(alert_type, alert_message){
        var homeURL = '../home/home.php'; //url to homepage, required in order to correctly pass POST data

        if(alert_type == null) //if no alert message to send, simply redirect
        {
            if($scope.isEditing)
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