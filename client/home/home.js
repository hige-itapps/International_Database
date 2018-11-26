//init app
var higeApp = angular.module('HIGE-app', []);

/*Controller to set date inputs and list*/
higeApp.controller('homeCtrl', function($scope, $filter) {
    //get PHP init variables
    $scope.isAdmin = scope_isAdmin; //set admin privileges if any

    $scope.alertType = alert_type; //set the alert type if any
    $scope.alertMessage = alert_message; //set the alert message if any

    /*Functions*/

    //remove the alert from the page
    $scope.removeAlert = function(){
        $scope.alertMessage = null;
    }
});