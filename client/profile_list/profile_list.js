//init app
var higeApp = angular.module('HIGE-app', ['ui.bootstrap']);

/*Controller to set date inputs and list*/
higeApp.controller('listCtrl', function($scope) {
    //get PHP init variables
    var tempProfiles = scope_profiles;
    $scope.profiles = new Array();
    for (var profile in tempProfiles){
        $scope.profiles.push( tempProfiles[profile] );
    }
    $scope.wildcard = scope_wildcard;

    $scope.filteredProfiles = [];

    $scope.pagination = {
        currentPage:  1,
        numPerPage: 12
    };

    $scope.profiles.forEach(function (profile) {
        if(profile.alternate_email != null && profile.alternate_email !== ''){
            profile.primaryEmail = profile.alternate_email;
        }
        else{
            profile.primaryEmail = profile.login_email;
        }
    });
   
    /*Functions*/
    $scope.$watch('pagination.currentPage + pagination.numPerPage', function() {
        var begin = (($scope.pagination.currentPage - 1) * $scope.pagination.numPerPage);
        var end = begin + $scope.pagination.numPerPage;

        $scope.filteredProfiles = $scope.profiles.slice(begin, end);
    });
    
    $scope.keySearch = function(){
        if ($scope.keyTerm == null) {$scope.keyTerm = "";}
        window.location.replace("?search&wildcard=" + $scope.keyTerm);
    }
});