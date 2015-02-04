/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var app = angular.module('overview', []);

app.directive('overview', function () {
    return {
        restrict: 'E',
        templateUrl: '/report/moodleanalyst/html/overview.tpl.html',
        controller: [
            '$http', '$scope', function ($http, $scope) {
                $http.get('/report/moodleanalyst/rest/mastREST.php/isUserLoggedIn')
                        .success(function (result) {
                            if(result) {
                                console.log("LOGIN");
                            }
                            else {
                                console.log('Kein LOGIN!');
                            }
                });
                
                
                $scope.vocabulary = null;
                $http.get('/report/moodleanalyst/rest/mastREST.php/vocabulary')
                        .success(function (result) {
                            $scope.vocabulary = result;
                        });
            }],
    };
});

app.controller('HeaderController', function () {
    this.tab = 1;

    this.setTab = function (newValue) {
        this.tab = newValue;
    };

    this.isSet = function (tabName) {
        return this.tab === tabName;
    };
});
app.controller('CourseDetailTabController', function () {
    this.tab = 1;

    this.setTab = function (newValue) {
        this.tab = newValue;
    };

    this.isSet = function (tabName) {
        return this.tab === tabName;
    };
});

app.directive('loader', function () {
    return {
        restrict: 'E',
        template: '<img style="display: block; margin-left: auto; margin-right: auto;" src="/report/moodleanalyst/pix/ajax-loader.gif">'
    };
});

app.directive('coursesearch', function () {
    return {
        restrict: 'E',
        templateUrl: '/report/moodleanalyst/html/coursesearch.tpl.html',
        controller: [
            '$http', '$scope', function ($http, $scope) {
                $scope.courseid = false;
                $scope.gotAllCourses = false;
                $http.get('/report/moodleanalyst/rest/mastREST.php/allCourses')
                        .success(function (result) {
                            $scope.gotAllCourses = true;
                            courseSearchDashboard(result, $scope);
                        });
            }],
        controllerAs: 'courseSearchCtrl'
    }
});

app.directive('usersearch', function () {
    return {
        restrict: 'E',
        templateUrl: '/report/moodleanalyst/html/usersearch.tpl.html',
        controller: [
            '$http', '$scope', function ($http, $scope) {
                $scope.courseid = false;
                $scope.gotAllUsers = false;
                $http.get('/report/moodleanalyst/rest/mastREST.php/allUsers')
                        .success(function (result) {
                            $scope.gotAllUsers = true;
                            userSearchDashboard(result, $scope);
                        });
            }],
        controllerAs: 'userSearchCtrl'
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
                                //console.log(data);
                                $scope.loadingCourse = false;
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
                };
                $scope.changeVisibility = function (courseid, visibility) {
                    $scope.loadingCourse = true;
                    $scope.course = null;
                    $http.get('/report/moodleanalyst/rest/mastREST.php/course/' + courseid + '/setVisibility/' + visibility)
                            .success(function () {
                                            $scope.didSelectACourse(courseid);
                            });
                };
            }],
        controllerAs: 'courseInfoCtrl'
    }
});

app.directive('userinfo', function () {
    return {
        restrict: 'E',
        templateUrl: '/report/moodleanalyst/html/userinfo.tpl.html',
        controller: [
            '$http', '$scope', function ($http, $scope) {
                $scope.didSelectAUser = function (userid) {
                    $scope.selectedUser = null;
                    $http.get('/report/moodleanalyst/rest/mastREST.php/user/' + userid)
                            .success(function (data) {
                                console.log(data);
                                $scope.loadingUser = false;
                                $scope.user = data;
                                coursesOfUserDashboard(data.courses, $scope);
                            });
                };
                
                $scope.addUserToCourse = function(userid, courseid, roleid) {
                    $scope.loadingCourse = true;
                    $scope.loadingUser = true;
                    $scope.user = null;
                    $scope.course = null;
                    $http.get('/report/moodleanalyst/rest/mastREST.php/addUser/' + userid + '/ToCourse/' + courseid + '/withRole/' + roleid)
                            .success(function (data) {
                                $scope.didSelectACourse(courseid);
                                $scope.didSelectAUser(userid);
                            });
                };
                
            }],
        controllerAs: 'userInfoCtrl'
    }
});

var activitiesInCourseDashboard = function (result, $scope) {
    var data = new google.visualization.DataTable(result);

    // Create a dashboard
    var dashboard = new google.visualization.Dashboard(document.getElementById('dashboardActivitiesInCourse'));

    // Create a search box to search for the activity name.
    var nameFilter = new google.visualization.ControlWrapper({
        controlType: 'StringFilter',
        containerId: 'activitiesInCourse_name_filter_div',
        options: {
            filterColumnIndex: 3,
            matchType: 'any',
            ui: {
                //label: 'Kurs suchen:'
            }
        }
    });

    // Create a category picker to filter section nr.
    var sectionnrCategoryPicker = new google.visualization.ControlWrapper({
        'controlType': 'CategoryFilter',
        'containerId': 'activities_sectionnr_filter_div',
        options: {
            filterColumnIndex: 0,
            ui: {
                label: '',
                allowTyping: false
            }
        }
    });

    // Create a category picker to filter section name.
    var sectionCategoryPicker = new google.visualization.ControlWrapper({
        'controlType': 'CategoryFilter',
        'containerId': 'activities_section_filter_div',
        options: {
            filterColumnIndex: 1,
            ui: {
                label: '',
                allowTyping: false
            }
        }
    });

    // Create a category picker to filter section name.
    var typeCategoryPicker = new google.visualization.ControlWrapper({
        'controlType': 'CategoryFilter',
        'containerId': 'activities_type_filter_div',
        options: {
            filterColumnIndex: 2,
            ui: {
                label: '',
                allowTyping: false
            }
        }
    });


    // Create the table to display.
    var table = new google.visualization.ChartWrapper({
        chartType: 'Table',
        containerId: 'activitiesInCourse_table_div',
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
};
var usersInCourseDashboard = function (result, $scope) {
    var data = new google.visualization.DataTable(result);

    // Create a dashboard
    var dashboard = new google.visualization.Dashboard(document.getElementById('dashboardUsersInCourse'));

    // Create a search box to search for the users name.
    var nameFilter = new google.visualization.ControlWrapper({
        controlType: 'StringFilter',
        containerId: 'usersInCourse_name_filter_div',
        options: {
            filterColumnIndex: 4,
            matchType: 'any',
            ui: {
                //label: 'Kurs suchen:'
            }
        }
    });

    // Create a category picker to filter role.
    var roleCategoryPicker = new google.visualization.ControlWrapper({
        'controlType': 'CategoryFilter',
        'containerId': 'usersInCourse_role_filter_div',
        options: {
            filterColumnIndex: 3,
            ui: {
                caption: $scope.vocabulary.role,
                //label: '',
                allowTyping: false
            }
        }
    });


    // Create the table to display.
    var table = new google.visualization.ChartWrapper({
        chartType: 'Table',
        containerId: 'usersInCourse_table_div',
        options: {
            showRowNumber: false,
            page: 'enable',
            pageSize: 25,
            allowHtml: true,
            sortColumn: 2,
            sortAscending: true
        },
        view: {
            //removed 4 (=full name)
            columns: [0, 1, 2, 3]
        }
    });

    // Establish dependencies.
    dashboard.bind([nameFilter, roleCategoryPicker], [table]);

    // Draw the dashboard.
    dashboard.draw(data);

    // Define what to do when selecting a table row.
    function selectHandler() {
        $scope.loadingUser = true;
        $scope.user = null;
        var selection = table.getChart().getSelection();
        $scope.userid = table.getDataTable().getFormattedValue(selection[0].row, 0);
        $scope.didSelectAUser($scope.userid);
        $("html, body").animate({scrollTop: 0}, 800);
    };

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
        containerId: 'courses_name_filter_div',
        options: {
            filterColumnIndex: 3,
            matchType: 'any',
            ui: {
                label: $scope.vocabulary.course
            }
        }
    });

    // Create a category picker to filter by grand parent category.
    var grandparentCategoryPicker = new google.visualization.ControlWrapper({
        'controlType': 'CategoryFilter',
        'containerId': 'courses_grandparentcategory_filter_div',
        options: {
            filterColumnIndex: 1,
            ui: {
                caption: $scope.vocabulary.grandparentcategory,
                //label: $scope.vocabulary.category,
                allowTyping: false
            }
        }
    });

    // Create a category picker to filter by Fachbereich.
    var parentCategoryPicker = new google.visualization.ControlWrapper({
        'controlType': 'CategoryFilter',
        'containerId': 'courses_parentcategory_filter_div',
        options: {
            filterColumnIndex: 2,
            ui: {
                caption: $scope.vocabulary.parentcategory,
                //label: '',
                allowTyping: false
            }
        }
    });

    // Create the table to display.
    var table = new google.visualization.ChartWrapper({
        chartType: 'Table',
        containerId: 'courses_table_div',
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
        $scope.loadingCourse = true;
        $scope.course = null;
        $scope.courseid = table.getDataTable().getFormattedValue(selection[0].row, 0);
        $scope.didSelectACourse($scope.courseid);
        //window.scrollTo(0,0);
        $("html, body").animate({scrollTop: 0}, 800);
    };

    // Setup listener to listen for clicks on table rows and process the selectHandler.
    google.visualization.events.addListener(table, 'select', selectHandler);
};
var userSearchDashboard = function (result, $scope) {
    var data = new google.visualization.DataTable(result);

    // Create a dashboard
    var dashboard = new google.visualization.Dashboard(document.getElementById('dashboardUserSearch'));

    // Create a search box to search for the user name.
    var userNameFilter = new google.visualization.ControlWrapper({
        controlType: 'StringFilter',
        containerId: 'user_name_filter_div',
        options: {
            filterColumnIndex: 5,
            matchType: 'any',
            ui: {
                //label: 'Nutzer suchen:'
            }
        }
    });

    // Create the table to display.
    var table = new google.visualization.ChartWrapper({
        chartType: 'Table',
        containerId: 'user_table_div',
        options: {
            showRowNumber: false,
            page: 'enable',
            pageSize: 25,
        },
        view: {
            columns: [1, 2, 3, 4]
        }
    });

    // Establish dependencies.
    dashboard.bind([userNameFilter], [table]);

    // Draw the dashboard.
    dashboard.draw(data);

    // Define what to do when selecting a table row.
    function selectHandler() {
        $scope.loadingUser = true;
        $scope.user = null;
        var selection = table.getChart().getSelection();
        $scope.userid = table.getDataTable().getFormattedValue(selection[0].row, 0);
        //console.log($scope.userid);
        $scope.didSelectAUser($scope.userid);
        $("html, body").animate({scrollTop: 0}, 800);
    };


    // Setup listener to listen for clicks on table rows and process the selectHandler.
    google.visualization.events.addListener(table, 'select', selectHandler);

};
var coursesOfUserDashboard = function (result, $scope) {
    console.log(result);
    var data = new google.visualization.DataTable(result);

    // Create a dashboard
    var dashboard = new google.visualization.Dashboard(document.getElementById('dashboardCoursesOfUser'));

    // Create a search box to search for the users name.
    var nameFilter = new google.visualization.ControlWrapper({
        controlType: 'StringFilter',
        containerId: 'coursesOfUser_name_filter_div',
        options: {
            filterColumnIndex: 3,
            matchType: 'any',
            ui: {
                label: $scope.vocabulary.name
            }
        }
    });

    // Create a category picker to filter grand parent category.
    var grandparentCategoryPicker = new google.visualization.ControlWrapper({
        'controlType': 'CategoryFilter',
        'containerId': 'coursesOfUser_grandparentcategory_filter_div',
        options: {
            filterColumnIndex: 1,
            ui: {
                caption: $scope.vocabulary.grandparentcategory,
                label: '',
                allowTyping: false
            }
        }
    });
    
    // Create a category picker to filter parent category.
    var parentCategoryPicker = new google.visualization.ControlWrapper({
        'controlType': 'CategoryFilter',
        'containerId': 'coursesOfUser_parentcategory_filter_div',
        options: {
            filterColumnIndex: 2,
            ui: {
                caption: $scope.vocabulary.parentcategory,
                label: '',
                allowTyping: false
            }
        }
    });
    
    // Create a category picker to filter role
    var roleCategoryPicker = new google.visualization.ControlWrapper({
        'controlType': 'CategoryFilter',
        'containerId': 'coursesOfUser_role_filter_div',
        options: {
            filterColumnIndex: 4,
            ui: {
                caption: $scope.vocabulary.role,
                label: '',
                allowTyping: false
            }
        }
    });


    // Create the table to display.
    var table = new google.visualization.ChartWrapper({
        chartType: 'Table',
        containerId: 'coursesOfUser_table_div',
        options: {
            showRowNumber: false,
            page: 'enable',
            pageSize: 25,
            allowHtml: true,
            sortColumn: 2,
            sortAscending: true
        },/*
        view: {
            //removed 4 (=full name)
            columns: [0, 1, 2, 3]
        }*/
    });

    // Establish dependencies.
    dashboard.bind([nameFilter, grandparentCategoryPicker, parentCategoryPicker, roleCategoryPicker], [table]);

    // Draw the dashboard.
    dashboard.draw(data);

    // Define what to do when selecting a table row.
    function selectHandler() {
        $scope.loadingCourse = true;
        $scope.ucourse = null;
        var selection = table.getChart().getSelection();
        $scope.courseid = table.getDataTable().getFormattedValue(selection[0].row, 0);
        $scope.didSelectACourse($scope.courseid);
        $("html, body").animate({scrollTop: 0}, 800);
    };

    // Setup listener to listen for clicks on table rows and process the selectHandler.
    google.visualization.events.addListener(table, 'select', selectHandler);
};