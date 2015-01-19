/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var app = angular.module('overview', []);

app.directive('overview', function () {
    return {
        restrict: 'E',
        templateUrl: '/report/moodleanalyst/html/overview.tpl.html'
    };
});

app.directive('courseinfo', function () {
    return {
        restrict: 'E',
        templateUrl: '/report/moodleanalyst/html/courseinfo.tpl.html',
        controller: [
            '$http','$scope', function ($http, $scope) {
                this.course = [];
                $http.get('https://mdl-alpha.un.hrz.tu-darmstadt.de/report/moodleanalyst/rest/router.php/course/id/64')
                        .success(function (result) {
                            $scope.course = result.Records[0];
                            console.log($scope.course);
                        });
            }],
        controllerAs: 'ctrl'
    }
});