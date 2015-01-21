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

app.directive('coursesearch', function () {
    return {
        restrict: 'E',
        templateUrl: '/report/moodleanalyst/html/coursesearch.tpl.html',
        controller: [
            '$http', '$scope', function ($http, $scope) {
                $scope.courseid = false;
                $http.get('/report/moodleanalyst/rest/mastREST.php/allCourses')
                        .success(function (result) {
                            courseSearchDashboard(result, $scope);
                        });
            }],
        controllerAs: 'courseSearchCtrl'
    }
});

app.directive('courseinfo', function () {
    return {
        restrict: 'E',
        templateUrl: '/report/moodleanalyst/html/courseinfo.tpl.html',
        controller: [
            '$http', '$scope', function ($http, $scope) {
                $scope.didSelectACourse = function (courseid) {
                    $http.get('/report/moodleanalyst/rest/mastREST.php/course/' + courseid)
                    .success(function (data) {
                        $scope.course = data;
                            $http.get('/report/moodleanalyst/rest/mastREST.php/course/getPersons/' + courseid)
                                    .success(function (result) {
                                            usersInCourseDashboard(result, $scope);
                                    });
                            $http.get('/report/moodleanalyst/rest/mastREST.php/course/getActivities/' + courseid)
                                    .success(function (result) {
                                            activitiesInCourseDashboard(result, $scope);
                                    });
                    });
                }
            }],
        controllerAs: 'ctrl'
    }
});
var activitiesInCourseDashboard = function (result, $scope) {
    var data = new google.visualization.DataTable(result);

    // Create a dashboard
    var dashboard = new google.visualization.Dashboard(document.getElementById('dashboardActivitiesInCourse'));

    // Create a search box to search for the users lastname.
    var nameFilter = new google.visualization.ControlWrapper({
        controlType: 'StringFilter',
        containerId: 'activitiesInCourse_name_filter',
        options: {
            filterColumnIndex: 2,
            matchType: 'any',
            ui: {
                //label: 'Kurs suchen:'
            }
        }
    });

    // Create a category picker to filter section nr.
    var sectionnrCategoryPicker = new google.visualization.ControlWrapper({
        'controlType': 'CategoryFilter',
        'containerId': 'activities_sectionnr_filter',
        options: {
            filterColumnIndex: 4,
            ui: {
                label: '',
                allowTyping: false
            }
        }
    });
    
    // Create a category picker to filter section name.
    var sectionCategoryPicker = new google.visualization.ControlWrapper({
        'controlType': 'CategoryFilter',
        'containerId': 'activities_section_filter',
        options: {
            filterColumnIndex: 4,
            ui: {
                label: '',
                allowTyping: false
            }
        }
    });
    
    // Create a category picker to filter section name.
    var typeCategoryPicker = new google.visualization.ControlWrapper({
        'controlType': 'CategoryFilter',
        'containerId': 'activities_type_filter',
        options: {
            filterColumnIndex: 4,
            ui: {
                label: '',
                allowTyping: false
            }
        }
    });


    // Create the table to display.
    var table = new google.visualization.ChartWrapper({
        chartType: 'Table',
        containerId: 'activitiesInCourse_table',
        options: {
            showRowNumber: false,
            page: 'enable',
            pageSize: 25,
            allowHtml: true,
            sortColumn: 0,
            sortAscending: false
        }
    });

    // Establish dependencies.
    dashboard.bind([nameFilter, sectionnrCategoryPicker, sectionCategoryPicker, typeCategoryPicker], [table]);

    // Draw the dashboard.
    dashboard.draw(data);

    // Define what to do when selecting a table row.
    function selectHandler() {
        var selection = table.getChart().getSelection();
        $scope.courseid = table.getDataTable().getFormattedValue(selection[0].row, 0);
        $scope.didSelectACourse($scope.courseid);
    }
    ;

    // Setup listener to listen for clicks on table rows and process the selectHandler.
    google.visualization.events.addListener(table, 'select', selectHandler);
};


var usersInCourseDashboard = function (result, $scope) {
    var data = new google.visualization.DataTable(result);

    // Create a dashboard
    var dashboard = new google.visualization.Dashboard(document.getElementById('dashboardUsersInCourse'));

    // Create a search box to search for the users lastname.
    var nameFilter = new google.visualization.ControlWrapper({
        controlType: 'StringFilter',
        containerId: 'usersInCourse_name_filter',
        options: {
            filterColumnIndex: 2,
            matchType: 'any',
            ui: {
                //label: 'Kurs suchen:'
            }
        }
    });

    // Create a category picker to filter role.
    var roleCategoryPicker = new google.visualization.ControlWrapper({
        'controlType': 'CategoryFilter',
        'containerId': 'usersInCourse_role_filter',
        options: {
            filterColumnIndex: 4,
            ui: {
                //caption: 'Nach Semester filtern',
                label: '',
                allowTyping: false
            }
        }
    });


    // Create the table to display.
    var table = new google.visualization.ChartWrapper({
        chartType: 'Table',
        containerId: 'usersInCourse_table',
        options: {
            showRowNumber: false,
            page: 'enable',
            pageSize: 25,
            allowHtml: true,
            sortColumn: 0,
            sortAscending: false
        }
    });

    // Establish dependencies.
    dashboard.bind([nameFilter, roleCategoryPicker], [table]);

    // Draw the dashboard.
    dashboard.draw(data);

    // Define what to do when selecting a table row.
    function selectHandler() {
        var selection = table.getChart().getSelection();
        $scope.courseid = table.getDataTable().getFormattedValue(selection[0].row, 0);
        $scope.didSelectACourse($scope.courseid);
    }
    ;

    // Setup listener to listen for clicks on table rows and process the selectHandler.
    google.visualization.events.addListener(table, 'select', selectHandler);
};

var courseSearchDashboard = function (result, $scope) {
    var data = new google.visualization.DataTable(result);

    // Create a dashboard
    var dashboard = new google.visualization.Dashboard(document.getElementById('dashboardCourseSearch'));

    // Create a search box to search for the course name.
    var nameFilter = new google.visualization.ControlWrapper({
        controlType: 'StringFilter',
        containerId: 'courses_name_filter',
        options: {
            filterColumnIndex: 3,
            matchType: 'any',
            ui: {
                //label: 'Kurs suchen:'
            }
        }
    });

    // Create a category picker to filter by grand parent category.
    var grandparentCategoryPicker = new google.visualization.ControlWrapper({
        'controlType': 'CategoryFilter',
        'containerId': 'courses_grandparentcategory_filter',
        options: {
            filterColumnIndex: 1,
            ui: {
                //caption: 'Nach Semester filtern',
                label: '',
                allowTyping: false
            }
        }
    });

    // Create a category picker to filter by Fachbereich.
    var parentCategoryPicker = new google.visualization.ControlWrapper({
        'controlType': 'CategoryFilter',
        'containerId': 'courses_parentcategory_filter',
        options: {
            filterColumnIndex: 2,
            ui: {
                //caption: 'Nach Fachbereich filtern',
                label: '',
                allowTyping: false
            }
        }
    });

    // Create the table to display.
    var table = new google.visualization.ChartWrapper({
        chartType: 'Table',
        containerId: 'courses_table',
        options: {
            showRowNumber: false,
            page: 'enable',
            pageSize: 25,
            allowHtml: true,
            sortColumn: 0,
            sortAscending: false
        }
    });

    // Establish dependencies.
    dashboard.bind([nameFilter, grandparentCategoryPicker, parentCategoryPicker], [table]);

    // Draw the dashboard.
    dashboard.draw(data);

    // Define what to do when selecting a table row.
    function selectHandler() {
        var selection = table.getChart().getSelection();
        $scope.courseid = table.getDataTable().getFormattedValue(selection[0].row, 0);
        $scope.didSelectACourse($scope.courseid);
    }
    ;

    // Setup listener to listen for clicks on table rows and process the selectHandler.
    google.visualization.events.addListener(table, 'select', selectHandler);
};