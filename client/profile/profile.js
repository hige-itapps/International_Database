//init app
var higeApp = angular.module('HIGE-app', []);

/*App Controller*/
higeApp.controller('profileCtrl', ['$scope', '$http', function($scope, $http){
   //get PHP init variables
   $scope.profile = scope_profile;
   $scope.isCreating = scope_isCreating;
   
   console.log($scope.profile);
}]);