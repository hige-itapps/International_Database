var myApp = angular.module('HIGE-app', []);

/*App Controller*/
myApp.controller('adminCtrl', function($scope, $http) {
    //get PHP init variables
    // $scope.administrators = scope_administrators;
    // $scope.applicationApprovers = scope_applicationApprovers
    // $scope.committee = scope_committee
    // $scope.finalReportApprovers = scope_finalReportApprovers;


    /*Functions*/

    //remove the alert from the page
    $scope.removeAlert = function(){
        $scope.alertMessage = null;
    }


    //add an admin
    // $scope.addAdmin = function(){
    //     $http({
    //         method  : 'POST',
    //         url     : '/../ajax/add_admin.php',
    //         data    : $.param({broncoNetID: $scope.addAdminID, name: $scope.addAdminName}),  // pass in data as strings
    //         headers : { 'Content-Type': 'application/x-www-form-urlencoded' }  // set the headers so angular passing info as form data (not request payload)
    //     })
    //     .then(function (response) {
    //         console.log(response, 'res');
    //         if(typeof response.data.error === 'undefined') //ran function as expected
    //         {
    //             response.data = response.data.trim();//remove blankspace around data
    //             if(response.data === "true")//updated
    //             {
    //                 $scope.getAdmins(); //refresh the form again
    //                 $scope.alertType = "success";
    //                 $scope.alertMessage = "Success! " + $scope.addAdminName + " has been added as an admin.";
    //                 $scope.addAdminName = ""; //reset inputs
    //                 $scope.addAdminID = "";
    //             }
    //             else//didn't update
    //             {
    //                 $scope.alertType = "warning";
    //                 $scope.alertMessage = "Warning: " + $scope.addAdminName + " was not added as an admin.";
    //             }
    //         }
    //         else //failure!
    //         {
    //             console.log(response.data.error);
    //             $scope.alertType = "danger";
    //             $scope.alertMessage = "There was an error when trying to add the admin! Error: " + response.data.error;
    //         }
    //     },function (error){
    //         console.log(error, 'can not get data.');
    //     });
    // };


    //remove an admin
    // $scope.removeAdmin = function(id){
    //     $http({
    //         method  : 'POST',
    //         url     : '/../ajax/remove_admin.php',
    //         data    : $.param({broncoNetID: id}),  // pass in data as strings
    //         headers : { 'Content-Type': 'application/x-www-form-urlencoded' }  // set the headers so angular passing info as form data (not request payload)
    //     })
    //     .then(function (response) {
    //         console.log(response, 'res');
    //         if(typeof response.data.error === 'undefined') //ran function as expected
    //         {
    //             response.data = response.data.trim();//remove blankspace around data
    //             if(response.data === "true")//updated
    //             {
    //                 $scope.getAdmins(); //refresh the form again
    //                 $scope.alertType = "success";
    //                 $scope.alertMessage = "Success! the admin was removed.";
    //             }
    //             else//didn't update
    //             {
    //                 $scope.alertType = "warning";
    //                 $scope.alertMessage = "Warning: unable to remove the admin!";
    //             }
    //         }
    //         else //failure!
    //         {
    //             console.log(response.data.error);
    //             $scope.alertType = "danger";
    //             $scope.alertMessage = "There was an error when trying to remove the admin! Error: " + response.data.error;
    //         }
    //     },function (error){
    //         console.log(error, 'can not get data.');
    //     });
    // };


    //refresh the admin list by getting the most up-to-date list from the database
    // $scope.getAdmins = function(){
    //     $http({
    //         method  : 'POST',
    //         url     : '/../ajax/get_admins.php'
    //     })
    //     .then(function (response) {
    //         console.log(response, 'res');
    //         if(typeof response.data.error === 'undefined') //ran function as expected
    //         {
    //             $scope.administrators = response.data;
    //         }
    //         else //failure!
    //         {
    //             console.log(response.data.error);
    //             $scope.alertType = "danger";
    //             $scope.alertMessage = "There was an error when trying to get the admins list! Error: " + response.data.error;
    //         }
    //     },function (error){
    //         console.log(error, 'can not get data.');
    //     });
    // };


});




