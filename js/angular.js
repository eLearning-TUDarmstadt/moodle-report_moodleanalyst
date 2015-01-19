/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var app = angular.module('overview', []);

app.directive('overview', function() {
    return {
        restrict: 'E',
        templateUrl: '/report/moodleanalyst/html/overview.tpl.html'
    };
});