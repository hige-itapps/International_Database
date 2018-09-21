//init app
var higeApp = angular.module('HIGE-app', []);

/*Controller to set date inputs and list*/
higeApp.controller('listCtrl', function($scope) {
    //get PHP init variables
    $scope.profiles = scope_profiles;
    console.log($scope.profiles);
    // $scope.applications = scope_applications;
    // $scope.appCycles = scope_appCycles;
    // $scope.isAllowedToSeeApplications = scope_isAllowedToSeeApplications;

    $scope.profiles.forEach(profile=> {
        if(profile.alternate_email != null && profile.alternate_email !== ''){
            profile.primaryEmail = profile.alternate_email;
        }
        else{
            $profile.primaryEmail = profile.login_email;
        }
    });
   
    /*Functions*/

    $scope.keySearch = function(){
        if ($scope.keyTerm == null) {$scope.keyTerm = "";}
        window.location.replace("?search&wildcard=" + $scope.keyTerm);
    }
});