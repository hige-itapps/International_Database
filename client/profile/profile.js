//init app
var higeApp = angular.module('HIGE-app', []);

/*App Controller*/
higeApp.controller('profileCtrl', ['$scope', '$http', '$timeout', function($scope, $http, $timeout){
    //get PHP init variables
    $scope.profile = scope_profile;
    $scope.state = scope_state; //control the state of the page ('CreatePending', 'Create', 'EditPending', 'Edit', 'View') to determine how to render everything

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
    $scope.codeVerified = false; //set to true if the code & login_email combination is verified as correct
    //$scope.millisRemaining = 0; //milliseconds remaining in the countdown
    $scope.secondsRemaining = 0; //seconds remaining in the countdown
    $scope.minutesRemaining = 0;
    $scope.hoursRemaining = 0;

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


    //tick down the remaining time
    //Source: https://stackoverflow.com/questions/45999422/angularjs-countdown-timer-in-decreasing-order
    $scope.countdown_tick = function(){
        //How many milliseconds remaining: expiration time - current time
        $scope.totalSecondsRemaining = $scope.expiration_timestamp - new Date().getTime()/1000;
        /*console.log($scope.expiration_timestamp);
        console.log(new Date().getTime());
        console.log($scope.millisRemaining);*/
        
        if ($scope.totalSecondsRemaining <= 0) {//timer is done, so stop it
            $scope.secondsRemaining = 0;
            $scope.minutesRemaining = 0;
            $scope.hoursRemaining = 0;
            console.log('Your time is up!');
        }
        else{
            $scope.secondsRemaining = Math.floor((($scope.totalSecondsRemaining) % 60));
            $scope.minutesRemaining = Math.floor((($scope.totalSecondsRemaining / (60)) % 60));
            $scope.hoursRemaining = Math.floor((($scope.totalSecondsRemaining / (60 * 60)) % 24));
            
            //recurse and tick again after 1 second
            $timeout($scope.countdown_tick, 1000);
        }
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
                $scope.alertMessage = "There was an unexpected error with your submission! Please let an administrator know the details and time of this issue.";
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
            $scope.alertMessage = "There was an unexpected error when trying to create your profile! Please let an administrator know the details and time of this issue.";
        });
    }


    //initiate the editing process
    $scope.editProfile = function(){
        $scope.state = "EditPending";
    }


    //try to send a confirmation code
    $scope.sendCode = function(){
        $scope.loadingAlert(); //start a loading alert
        var email = $scope.state === 'CreatePending' ? $scope.create_email : $scope.profile.email; //decide to use the new email address specified if creating, or the existing one if editing
        var creating = $scope.state === 'CreatePending' ? true : false; //determine if creating a new profile or just editing

        $http({
            method  : 'POST',
            url     : '../api.php?send_code',
            data    : $.param({email: email, creating: creating}),  // pass in the profile object
            headers : { 'Content-Type': 'application/x-www-form-urlencoded' }  //standard paramater encoding
        })
        .then(function (response) {
            console.log(response, 'res');
            //data = response.data;
            if(typeof response.data.success === 'undefined'){ //unexpected result!
                console.log(JSON.stringify(response, null, 4));
                $scope.alertType = "danger";
                $scope.alertMessage = "There was an unexpected error with your submission! Please let an administrator know the details and time of this issue.";
            }
            else if(!response.data.success){ //there was at least 1 error
                $scope.alertType = "danger";
                $scope.alertMessage = "Error sending code: " + JSON.stringify(response.data.error);
            }
            else{ //no errors
                $scope.alertType = "success";
                $scope.alertMessage = "A confirmation code was successfully sent out to the appropriate email address.";
                if($scope.state === "EditPending"){
                    $scope.codePending = true;
                }
            }
           
        },function (error){
            console.log(error, 'can not get data.');
            $scope.alertType = "danger";
            $scope.alertMessage = "There was an unexpected error when trying to send your code! Please let an administrator know the details and time of this issue.";
        });
    }


    //try to confirm a confirmation code
    $scope.confirmCode = function(){
        $scope.loadingAlert(); //start a loading alert
        var email = $scope.state === 'CreatePending' ? $scope.create_email : $scope.profile.email; //decide to use the new email address specified if creating, or the existing one if editing
        var userID = $scope.state === 'CreatePending' ? null : $scope.profile.id; //only pass in the userID if editing, not creating

        $http({
            method  : 'POST',
            url     : '../api.php?confirm_code',
            data    : $.param({userID: userID, email: email, code: $scope.code}),  // pass in the profile object
            headers : { 'Content-Type': 'application/x-www-form-urlencoded' }  //standard paramater encoding
        })
        .then(function (response) {
            console.log(response, 'res');
            //data = response.data;
            if(typeof response.data.success === 'undefined'){ //unexpected result!
                console.log(JSON.stringify(response, null, 4));
                $scope.alertType = "danger";
                $scope.alertMessage = "There was an unexpected error with your submission! Please let an administrator know the details and time of this issue.";
            }
            else if(!response.data.success){ //there was at least 1 error
                $scope.alertType = "danger";
                $scope.alertMessage = "Error confirming code: " + JSON.stringify(response.data.error);
            }
            else{ //no errors
                $scope.alertType = "success";
                $scope.alertMessage = "Confirmation Code Verified! Please submit your edits before the specified expiration time.";
                if($scope.state === "CreatePending"){
                    $scope.state = "Create";
                    $scope.profile.login_email = email; //set the wmu address
                }
                else if($scope.state === "EditPending"){
                    $scope.state = "Edit";
                    $scope.profile.login_email = response.data.login_email; //set the wmu address
                    $scope.profile.alternate_email = response.data.alternate_email; //set the optional non-wmu address
                }
                $scope.expiration_timestamp = response.data.expiration_time; //set the expiration time
                $scope.countdown_tick();//start the expiration countdown
            }
           
        },function (error){
            console.log(error, 'can not get data.');
            $scope.alertType = "danger";
            $scope.alertMessage = "There was an unexpected error when trying to confirm your code! Please let an administrator know the details and time of this issue.";
        });
    }


    //redirect the user to the homepage. Optionally, send an alert which will show up on the next page, consisting of a type(success, warning, danger, etc.) and message
    $scope.redirectToHomepage = function(alert_type, alert_message){
        var homeURL = '../home/home.php'; //url to homepage, required in order to correctly pass POST data

        if(alert_type == null) //if no alert message to send, simply redirect
        {
            if($scope.state === "Create" || $scope.state === "Edit")
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