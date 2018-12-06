var myApp = angular.module('HIGE-app', []);

/*App Controller*/
myApp.controller('adminCtrl', function($scope, $http) {
    //get PHP init variables
    $scope.numberOfPendingProfiles = scope_numberOfPendingProfiles;
    $scope.administrators = scope_administrators;
    $scope.siteWarning = scope_siteWarningString;

    var tempEmailSentDate = new Date(parseInt(var_reminderEmailsLastSent+"000")); //set the date w/seconds and convert to milliseconds by moving 3 decimal places
    $scope.reminderEmailsLastSent = tempEmailSentDate.toLocaleString(); //get the datetime as a local time string (day/month/year hour/minute/second)

    var tempDatabaseBackupDate = new Date(parseInt(var_databaseLastBackedUp+"000")); //set the date w/seconds and convert to milliseconds by moving 3 decimal places
    $scope.databaseLastBackedUp = tempDatabaseBackupDate.toLocaleString(); //get the datetime as a local time string (day/month/year hour/minute/second)

    
    /*Functions*/

    //remove the alert from the page
    $scope.removeAlert = function(){
        $scope.alertMessage = null;
    }

    //display a generic loading alert to the page
    $scope.loadingAlert = function(){
        $scope.alertType = "info";
        $scope.alertMessage = "Loading...";
    }



    //add an admin
    $scope.addAdmin = function(){
        $scope.loadingAlert();
        $http({
            method  : 'POST',
            url     : '../api.php?add_admin',
            data    : $.param({broncoNetID: JSON.stringify($scope.addAdminID), name: JSON.stringify($scope.addAdminName)}),  // pass in data as strings
            headers : { 'Content-Type': 'application/x-www-form-urlencoded' }  // set the headers so angular passing info as form data (not request payload)
        })
        .then(function (response) {
            console.log(response, 'res');
            if(typeof response.data.error === 'undefined') //ran function as expected
            {
                response.data = response.data.trim();//remove blankspace around data
                if(response.data === "true")//updated
                {
                    $scope.getAdmins(); //refresh the form again
                    $scope.alertType = "success";
                    $scope.alertMessage = "Success! " + $scope.addAdminName + " has been added as an admin.";
                    $scope.addAdminName = ""; //reset inputs
                    $scope.addAdminID = "";
                }
                else//didn't update
                {
                    $scope.alertType = "warning";
                    $scope.alertMessage = "Warning: " + $scope.addAdminName + " was not added as an admin.";
                }
            }
            else //failure!
            {
                console.log(response.data.error);
                $scope.alertType = "danger";
                $scope.alertMessage = "There was an error when trying to add the admin! " + response.data.error;
            }
        },function (error){
            console.log(error, 'can not get data.');
        });
    };



    //remove an admin
    $scope.removeAdmin = function(id){
        if(!confirm ("Are you sure you want to remove this person with id '"+id+"' from the administrators list?")) {return;} //delete confirmation required

        $scope.loadingAlert();
        $http({
            method  : 'POST',
            url     : '../api.php?remove_admin',
            data    : $.param({broncoNetID: JSON.stringify(id)}),  // pass in data as strings
            headers : { 'Content-Type': 'application/x-www-form-urlencoded' }  // set the headers so angular passing info as form data (not request payload)
        })
        .then(function (response) {
            console.log(response, 'res');
            if(typeof response.data.error === 'undefined') //ran function as expected
            {
                response.data = response.data.trim();//remove blankspace around data
                if(response.data === "true")//updated
                {
                    $scope.getAdmins(); //refresh the form again
                    $scope.alertType = "success";
                    $scope.alertMessage = "Success! the admin was removed.";
                }
                else//didn't update
                {
                    $scope.alertType = "warning";
                    $scope.alertMessage = "Warning: unable to remove the admin!";
                }
            }
            else //failure!
            {
                console.log(response.data.error);
                $scope.alertType = "danger";
                $scope.alertMessage = "There was an error when trying to remove the admin! " + response.data.error;
            }
        },function (error){
            console.log(error, 'can not get data.');
        });
    };



    //refresh the admin list by getting the most up-to-date list from the database
    $scope.getAdmins = function(){
        $scope.loadingAlert();
        $http({
            method  : 'POST',
            url     : '../api.php?get_admins'
        })
        .then(function (response) {
            console.log(response, 'res');
            if(typeof response.data.error === 'undefined') //ran function as expected
            {
                $scope.administrators = response.data;
            }
            else //failure!
            {
                console.log(response.data.error);
                $scope.alertType = "danger";
                $scope.alertMessage = "There was an error when trying to get the admins list! " + response.data.error;
            }
        },function (error){
            console.log(error, 'can not get data.');
        });
    };



    //save the site warning message
    $scope.saveSiteWarning = function(){
        if(confirm ("Are you sure you want to save this site warning? It will become visible to every user on every page until it is cleared."))
        {
            $scope.loadingAlert();
            $http({
                method  : 'POST',
                url     : '../api.php?save_site_warning',
                data    : $.param({siteWarning: JSON.stringify($scope.siteWarning)}),  // pass in data as strings
                headers : { 'Content-Type': 'application/x-www-form-urlencoded' }  // set the headers so angular passing info as form data (not request payload)
            })
            .then(function (response) {
                console.log(response, 'res');
                if(typeof response.data.error === 'undefined') //ran function as expected
                {
                    $scope.alertType = "success";
                    $scope.alertMessage = "Success! the site warning was saved.";
                }
                else //failure!
                {
                    console.log(response.data.error);
                    $scope.alertType = "danger";
                    $scope.alertMessage = "There was an error when trying to save the site warning! " + response.data.error;
                }
            },function (error){
                console.log(error, 'can not get data.');
            });
        }
    };
    //clear the site warning message
    $scope.clearSiteWarning = function(){
        if(confirm ("Are you sure you want to clear this site warning? It will no longer appear to any users on any page."))
        {
            $scope.loadingAlert();
            $http({
                method  : 'POST',
                url     : '../api.php?clear_site_warning'
            })
            .then(function (response) {
                console.log(response, 'res');
                if(typeof response.data.error === 'undefined') //ran function as expected
                {
                    $scope.alertType = "success";
                    $scope.alertMessage = "Success! the site warning was cleared.";
                    $scope.siteWarning = null;
                }
                else //failure!
                {
                    console.log(response.data.error);
                    $scope.alertType = "danger";
                    $scope.alertMessage = "There was an error when trying to clear the site warning! " + response.data.error;
                }
            },function (error){
                console.log(error, 'can not get data.');
            });
        }
    };

});




