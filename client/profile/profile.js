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
   
   console.log($scope.profile);
}]);