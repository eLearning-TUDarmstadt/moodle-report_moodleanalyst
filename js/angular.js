// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Version details.
 *
 * @package    report
 * @subpackage report_moodleanalyst
 * @copyright  2015, Nils Muzzulini
 * @copyright  2015, Steffen Pegenau
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

var wwwroot = document.getElementById("angularJSloader").getAttribute("wwwroot");
var app = angular.module('overview', []);


/**********************
 ** Directive: MODAL **
 **********************/
app.directive('modal', function () {
    return {
        template: '<div class="modal fade">' +
                '<div class="modal-dialog">' +
                '<div class="modal-content">' +
                '<div class="modal-header">' +
                '<h4 class="modal-title">{{ title }}</h4>' +
                '</div>' +
                '<div class="modal-body" ng-transclude></div>' +
                '</div>' +
                '</div>' +
                '</div>',
        restrict: 'E',
        transclude: true,
        replace: true,
        scope: true,
        link: function postLink(scope, element, attrs) {
            scope.title = attrs.title;

            scope.$watch(attrs.visible, function (value) {
                if (value == true)
                    $(element).modal('show');
                else
                    $(element).modal('hide');
            });

            $(element).on('shown.bs.modal', function () {
                scope.$apply(function () {
                    scope.$parent[attrs.visible] = true;
                });
            });

            $(element).on('hidden.bs.modal', function () {
                scope.$apply(function () {
                    scope.$parent[attrs.visible] = false;
                });
            });
        }
    };
});


/*************************
 ** Directive: OVERVIEW **
 *************************
 * - initializes overview template
 * - checks if user is logged in
 * - initializes vocabulary
 */
app.directive('overview', function () {
    return {
        restrict: 'E',
        templateUrl: wwwroot + '/report/moodleanalyst/php/overview.tpl.php',
        controller: [
            '$http', '$scope', function ($http, $scope) {

                $scope.loadDataUserInfo = function () {
                    $scope.$broadcast('loadDataUserInfo', true);
                };

                $scope.didSelectAUser = function () {
                    $scope.$broadcast('selectedUserIdChanged', $scope.userid);
                };

                $scope.showModal = false;
                $scope.toggleModal = function () {
                    $scope.showModal = !$scope.showModal;
                };
                // check if user is logged in
                $http.get(wwwroot + '/report/moodleanalyst/rest/mastREST.php/isUserLoggedIn')
                        .error(function (data, status, headers, config) {
                            $scope.toggleModal();
                        });
                // initialize vocabulary
                $scope.vocabulary = null;
                $http.get(wwwroot + '/report/moodleanalyst/rest/mastREST.php/vocabulary')
                        .success(function (result) {
                            $scope.vocabulary = result;
                        });
            }]
    };
});


/**********************************************
 ** Controller: COURSE DETAIL TAB CONTROLLER **
 **********************************************
 * - used to control the tab panel on the detailed course info page
 */
app.controller('CourseDetailTabController', ['$scope', function ($scope) {
        $scope.tab = 1;

        $scope.isActivitySelected = false;
        $scope.activity = [];
        $scope.activity.id = null;
        $scope.activity.cm = null;
        $scope.activity.mod = null;
        $scope.activity.visible = null;
        $scope.activity.resourceyesorno = null;

        $scope.setActivity = function (id, cm, mod, visible) {
            $scope.activity.id = id;
            $scope.activity.cm = cm;
            $scope.activity.mod = mod;
            $scope.activity.visible = visible;
            if (mod == "resource") {
                $scope.activity.resourceyesorno = true;
            }
            else {
                $scope.activity.resourceyesorno = false;
            }
            ;
        };

        this.setTab = function (newValue) {
            $scope.tab = newValue;
        };

        $scope.setCourseDetailTab = function (newValue) {
            $scope = newValue;
        };

        this.isSet = function (tabName) {
            return $scope.tab === tabName;
        };
    }]);


/**************************************
 ** Controller: FILES TAB CONTROLLER **
 **************************************
 *
 */
app.controller('FilesController', ['$scope', '$http', function ($scope, $http) {
        $scope.gotAllFiles = false;


        $scope.getAllFiles = function () {
            $http.get(wwwroot + '/report/moodleanalyst/rest/mastREST.php/files')
                    .success(function (result) {
                        $scope.gotAllFiles = true;
                        filesDashboard(result, $scope);
                    });
        };
    }]);


/********************************************************
 ** Controller: ALL URLs TAB CONTROLLER **
 ********************************************************
 *
 */
app.controller('URLsController', ['$scope', '$http', function ($scope, $http) {
        $scope.gotAllURLs = false;


        $scope.getAllURLs = function () {
            var url = wwwroot + '/report/moodleanalyst/rest/mastREST.php/url';
            $http.get(url)
                    .success(function (result) {
                        $scope.gotAllURLs = true;
                        allURLsDashboard(result, $scope, url);
                    });
        };
    }]);


/********************************************************
 ** Controller: COURSES WITH ACTIVITIES TAB CONTROLLER **
 ********************************************************
 *
 */
app.controller('CoursesWithActivitiesController', ['$scope', '$http', function ($scope, $http) {
        $scope.gotAllCoursesWithAcitivities = false;


        $scope.getData = function () {
            var url = wwwroot + '/report/moodleanalyst/rest/mastREST.php/courses/withNumberOfActivities';
            $http.get(url)
                    .success(function (result) {
                        $scope.gotAllCoursesWithAcitivities = true;
                        coursesWithActivitiesDashboard(result, $scope, url);
                    });
        };
    }]);


/***********************
 ** Directive: LOADER **
 ***********************
 */
app.directive('loader', function () {
    return {
        restrict: 'E',
        template: '<img style="display: block; margin-left: auto; margin-right: auto;" src="' + wwwroot + '/report/moodleanalyst/pix/ajax-loader.gif">'
    };
});


/******************************
 ** Directive: COURSE SEARCH **
 ******************************
 * - initializes course search template
 * - loads all courses and draws dashboard
 * - loads all empty courses and draws dashboard
 */
app.directive('coursesearch', function () {
    return {
        restrict: 'E',
        templateUrl: wwwroot + '/report/moodleanalyst/php/coursesearch.tpl.php',
        controller: [
            '$http', '$scope', function ($http, $scope) {
                $scope.loadDataCourseSearch = function () {
                    $scope.courseid = false;
                    $scope.gotAllCourses = false;
                    $scope.gotAllEmptyCourses = false;
                    // load all courses from database
                    $http.get(wwwroot + '/report/moodleanalyst/rest/mastREST.php/allCourses')
                            .success(function (result) {
                                $scope.gotAllCourses = true;
                                // initialize course search dashboard
                                courseSearchDashboard(result, $scope);

                                /* commented out for performance reasons */
                                /*
                                 // load all empty courses from database
                                 $http.get(wwwroot + '/report/moodleanalyst/rest/mastREST.php/courses/getEmpty')
                                 .success(function (result) {
                                 $scope.gotAllEmptyCourses = true;
                                 // draw empty courses dashboard
                                 emptyCoursesDashboard(result, $scope);
                                 });
                                 */
                            });
                };

                //initial load
                $scope.loadDataCourseSearch();


                $scope.downloadDataTableAsCSV = function () {
                    downloadCSV($scope.csv_out);
                };
            }],
        controllerAs: 'courseSearchCtrl'
    };
});


/****************************
 ** Directive: USER SEARCH **
 ****************************
 * - initializes user search template
 * - loads all users and draws user search dashboard
 * - draws inactive users dashboard
 */
app.directive('usersearch', function () {
    return {
        restrict: 'E',
        templateUrl: wwwroot + '/report/moodleanalyst/php/usersearch.tpl.php',
        controller: [
            '$http', '$scope', function ($http, $scope) {
                $scope.loadDataUserSearch = function () {
                    $scope.courseid = false;
                    $scope.gotAllUsers = false;
                    // load all users from database
                    $http.get(wwwroot + '/report/moodleanalyst/rest/mastREST.php/user')
                            .success(function (result) {
                                $scope.gotAllUsers = true;
                                // initialize user search dashboard
                                userSearchDashboard(result, $scope);
                                $http.get(wwwroot + '/report/moodleanalyst/rest/mastREST.php/allUsers')
                                        .success(function (result) {
                                            $scope.gotAllUsers = true;
                                            // initialize inactive users dashboard
                                            inactiveUsersDashboard(result, $scope);
                                        });
                            });
                };
                // initial load
                $scope.loadDataUserSearch();
            }],
        controllerAs: 'userSearchCtrl'
    };
});


/****************************
 ** Directive: COURSE INFO **
 ****************************
 * - initializes course info template
 * - displays detailed information about a selected course
 *      including users and activities in the course
 * - includes function to change visibility of the course
 */
app.directive('courseinfo', function () {
    return {
        restrict: 'E',
        templateUrl: wwwroot + '/report/moodleanalyst/php/courseinfo.tpl.php',
        controller: [
            '$http', '$scope', function ($http, $scope) {
                $scope.isActivitySelected = false;
                $scope.selectedActivity = [];
                $scope.selectedActivity.id = null;
                $scope.selectedActivity.cm = null;
                $scope.selectedActivity.mod = null;

                $scope.didSelectACourse = function (courseid) {
                    console.log("Selected Course: " + courseid);
                    $scope.loadDataCourseInfo = function () {
                        // load course info from database
                        courseid = courseid.replace('.', '');
                        courseid = courseid.replace(',', ''); // Fixes en_* locale
                        $http.get(wwwroot + '/report/moodleanalyst/rest/mastREST.php/course/' + courseid)
                                .success(function (data) {
                                    $scope.loadingCourse = false;
                                    $scope.course = data;
                                    // load users in course from database
                                    $http.get(wwwroot + '/report/moodleanalyst/rest/mastREST.php/course/getPersons/' + courseid)
                                            .success(function (result) {
                                                // initialize users in course dashboard
                                                usersInCourseDashboard(result, $scope);
                                                // load activities in course from database
                                                $http.get(wwwroot + '/report/moodleanalyst/rest/mastREST.php/course/getActivities/' + courseid)
                                                        .success(function (result) {
                                                            // initialize activities in course dashboard
                                                            activitiesInCourseDashboard(result, $scope);
                                                        });
                                            });
                                });
                    };
                    // initial load
                    $scope.loadDataCourseInfo();
                };

                // function to change visibility of the selected course
                $scope.changeVisibility = function (courseid, visibility) {
                    $scope.loadingCourse = true;
                    $scope.course = null;
                    $http.get(wwwroot + '/report/moodleanalyst/rest/mastREST.php/course/' + courseid + '/setVisibility/' + visibility)
                            .success(function () {
                                // reload course info
                                $scope.loadDataCourseInfo();
                                $scope.loadDataUserInfo()
                            });
                };
            }],
        controllerAs: 'courseInfoCtrl'
    };
});


/**************************
 ** Directive: USER INFO **
 **************************
 * - initializes user info template
 * - displays detailed information about a selected user
 * - includes a function to add a user to a course
 */
app.directive('userinfo', function () {
    return {
        restrict: 'E',
        templateUrl: wwwroot + '/report/moodleanalyst/php/userinfo.tpl.php',
        controller: [
            '$http', '$scope', '$element', '$attrs', function ($http, $scope, $element, $attrs) {

                $scope.activeUsers = $attrs.activeusers;
                var loadDataUserInfo;

                var didSelectAUser = function (userid) {
                    loadDataUserInfo = function () {
                        // Delete dot as decimal mark (13.345 => 13345)
                        userid = userid.replace('.', '');
                        userid = userid.replace(',', ''); // Fixes en_* locale

                        // load user info from database
                        $http.get(wwwroot + '/report/moodleanalyst/rest/mastREST.php/user/' + userid)
                                .success(function (data) {
                                    $scope.loadingUser = false;
                                    $scope.user = data;
                                    // initialize courses of user dashboard
                                    coursesOfUserDashboard(data.courses, $scope, $element);
                                });
                    };
                    // initial load
                    loadDataUserInfo();
                };

                $scope.$on('selectedUserIdChanged', function (event, userid) {
                    didSelectAUser(userid);
                });

                $scope.$on('loadDataUserInfo', function (event) {
                    if (typeof loadDataUserInfo === 'function') {
                        loadDataUserInfo();
                    }
                });

                // function to add user to a course
                $scope.addUserToCourse = function (userid, courseid, roleid) {
                    $scope.loadingCourse = true;
                    $scope.loadingUser = true;
                    //$scope.user = null;
                    //$scope.course = null;
                    $http.get(wwwroot + '/report/moodleanalyst/rest/mastREST.php/addUser/' + userid + '/ToCourse/' + courseid + '/withRole/' + roleid)
                            .success(function () {
                                //reload data
                                $scope.loadDataCourseInfo();
                                loadDataUserInfo();
                                $scope.userAddedToCourse = $scope.user.firstname.v + " " + $scope.user.lastname.v;
                            });
                };
            }],
        controllerAs: 'userInfoCtrl'
    };
});


/*********************
 ** Filter: toArray **
 *********************
 * - puts the contents of an object into an array so it can be sorted using angularJS functions in the html code.
 */
app.filter('toArray', function () {
    return function (obj) {
        var result = [];
        angular.forEach(obj, function (val) {
            result.push(val);
        });
        return result;
    };
});

/********************************
 ** Directive: NEW COURSE FORM **
 ********************************/
app.directive('newcourseform', function () {
    return {
        restrict: 'E',
        templateUrl: wwwroot + '/report/moodleanalyst/php/createnewcourse.tpl.php',
        controller: [
            '$http', '$scope', function ($http, $scope) {
                $http.get(wwwroot + '/report/moodleanalyst/rest/mastREST.php/course/new/options')
                        .success(function (data) {
                            $scope.password = "randompassword";
                            $scope.allCategories = data.categories;
                        });

                // reset form
                $scope.reset = function () {
                    $scope.shortname = null;
                    $scope.fullname = null;
                    $scope.category = null;
                    $scope.password = "randompassword";
                };

                $scope.createNewCourse = function () {
                    var password = "";

                    if ($scope.password == "randompassword") {
                        //console.log("RANDOM!");
                        password = generatePassword();
                    }
                    if ($scope.password == "userpassword") {
                        //console.log("userpassword");
                        password = $scope.userpassword;
                    }

                    var params = {
                        'shortname': $scope.shortname,
                        'fullname': $scope.fullname,
                        'category': $scope.category,
                        'password': password
                    };
                    $http.post(wwwroot + '/report/moodleanalyst/rest/mastREST.php/course/new', params)
                            .success(function (data) {
                                if (data.error) {
                                    alert(data.error);
                                }
                                else {
                                    $scope.loadingCourse = true;
                                    $scope.course = null;
                                    $scope.courseid = data.course;
                                    $scope.didSelectACourse($scope.courseid);
                                    $("#myTabList a[href=\"#tabs-1\"]").tab("show");
                                }
                            })
                            .error(function () {
                                alert("error occured");
                            });
                    /*
                     $http.post('/report/moodleanalyst/rest/mastREST.php/course/new', {})
                     .success = function (data, status, headers, config) {
                     console.log(data);
                     console.log(status);
                     console.log(headers);
                     console.log(config);

                     console.log("POST-DATA: " + data);
                     if (data.error) {
                     alert(data.error);
                     } else {
                     $scope.loadingCourse = true;
                     $scope.course = null;
                     $scope.courseid = data.course;
                     $scope.didSelectACourse($scope.courseid);
                     }
                     };

                     .error = function (data, status) {
                     alert("Error: " + status);
                     };
                     */
                };
                $scope.master = {};
                $scope.update = function (user) {
                    $scope.master = angular.copy(user);
                };
            }],
        controllerAs: 'newCourseCtrl'
    };
});

/*************************************
 ** Dashboard: ACTIVITIES IN COURSE **
 *************************************/
var activitiesInCourseDashboard = function (result, $scope) {
    $scope.activityIsSelected = false;

    var data = new google.visualization.DataTable(result);

    // Create a dashboard.
    var dashboard = new google.visualization.Dashboard(document.getElementById('dashboardActivitiesInCourse'));

    // Create a search box to search for the activity name.
    var nameFilter = new google.visualization.ControlWrapper({
        controlType: 'StringFilter',
        containerId: 'activitiesInCourse_name_filter_div',
        options: {
            filterColumnIndex: 3,
            matchType: 'any',
            ui: {
                label: $scope.vocabulary.name
            }
        }
    });

    // Create a category picker to filter by section name.
    var sectionCategoryPicker = new google.visualization.ControlWrapper({
        'controlType': 'CategoryFilter',
        'containerId': 'activities_section_filter_div',
        options: {
            filterColumnIndex: 1,
            ui: {
                label: '',
                caption: $scope.vocabulary.section,
                allowTyping: false
            }
        }
    });

    // Create a category picker to filter by activity.
    var typeCategoryPicker = new google.visualization.ControlWrapper({
        'controlType': 'CategoryFilter',
        'containerId': 'activities_type_filter_div',
        options: {
            filterColumnIndex: 2,
            ui: {
                label: '',
                caption: $scope.vocabulary.activity,
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
            width: '100%',
            page: 'enable',
            pageSize: 25,
            allowHtml: true
                    //sortColumn: 0,
                    //sortAscending: false
        },
        view: {
            // 0: instance
            // 1: section name
            // 2: localised activity type
            // 3: activity name
            // 4: mod - moodle internal mod name, for example forum, chat, assign, choice
            // 5: course module id (cm)
            // 6: visible (1 || 0)
            columns: [1, 2, 3, 6]
        }
    });

    // Establish dependencies.
    dashboard.bind([nameFilter, sectionCategoryPicker, typeCategoryPicker], [table]);

    // Draw the dashboard.
    dashboard.draw(data);

    // Define what to do when selecting a table row.
    function selectHandler() {
        var selection = table.getChart().getSelection()[0];
        selection = table.getDataTable().getTableRowIndex(selection.row);

        var id = data.getFormattedValue(selection, 0);
        var cm = data.getFormattedValue(selection, 5);
        var mod = data.getFormattedValue(selection, 4);
        var visible = data.getFormattedValue(selection, 6);

        var scope = angular.element(document.getElementById("tabController")).scope();
        scope.$apply(function () {
            scope.setActivity(id, cm, mod, visible);
        });
    }
    ;

    // Setup listener to listen for clicks on table rows and process the selectHandler.
    google.visualization.events.addListener(table, 'select', selectHandler);

    google.visualization.events.addListener(nameFilter, 'ready', function () {
        $('.google-visualization-controls-stringfilter input').prop('placeholder', $scope.vocabulary.search + '...');
    });
};

/********************************
 ** Dashboard: USERS IN COURSE **
 ********************************/
var usersInCourseDashboard = function (result, $scope) {
    var data = new google.visualization.DataTable(result);

    // Create a dashboard.
    var dashboard = new google.visualization.Dashboard(document.getElementById('dashboardUsersInCourse'));

    // Create a search box to search for the users full name.
    var nameFilter = new google.visualization.ControlWrapper({
        controlType: 'StringFilter',
        containerId: 'usersInCourse_name_filter_div',
        options: {
            filterColumnIndex: 5,
            matchType: 'any',
            ui: {
                label: $scope.vocabulary.fullnameuser
            }
        },
        state: {
            // if user was directly enrolled, set filter to that user
            value: $scope.userAddedToCourse
        }
    });

    // Create a category picker to filter by role.
    var roleCategoryPicker = new google.visualization.ControlWrapper({
        'controlType': 'CategoryFilter',
        'containerId': 'usersInCourse_role_filter_div',
        options: {
            filterColumnIndex: 4,
            ui: {
                caption: $scope.vocabulary.role,
                label: '',
                allowTyping: false
            }
        }
    });

    // helper function to set role filter and change tab to enrolled users
    $scope.setRoleFilterForUsersInCourseDashboard = function (rolestring) {
        roleCategoryPicker.setState({'selectedValues': [rolestring]});
        roleCategoryPicker.draw();
        var scope = angular.element($('#tabController')).scope();
        scope.tab = 1;
    };

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
            // 0: id
            // 1: username
            // 2: first name
            // 3: last name
            // 4: role
            // 5: full name
            columns: [0, 1, 2, 3, 4]
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
    }
    ;

    // Setup listener to listen for clicks on table rows and process the selectHandler.
    google.visualization.events.addListener(table, 'select', selectHandler);

    google.visualization.events.addListener(nameFilter, 'ready', function () {
        $('.google-visualization-controls-stringfilter input').prop('placeholder', $scope.vocabulary.search + '...');
    });
};

/******************************
 ** Dashboard: COURSE SEARCH **
 ******************************/
var courseSearchDashboard = function (result, $scope) {
    var data = new google.visualization.DataTable(result);

    //$scope.csv_out = dataTableToCSV(data);

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
                //label: $scope.vocabulary.course
            }
        }
    });

    // Create a search box to search for the course ID.
    var idFilter = new google.visualization.ControlWrapper({
        controlType: 'StringFilter',
        containerId: 'courses_id_filter_div',
        options: {
            filterColumnIndex: 0,
            matchType: 'any',
            ui: {
                //label: ''
            }
        }
    });

    // Create a category picker to filter by grandparent category.
    var grandparentCategoryPicker = new google.visualization.ControlWrapper({
        'controlType': 'CategoryFilter',
        'containerId': 'courses_grandparentcategory_filter_div',
        options: {
            filterColumnIndex: 1,
            ui: {
                caption: $scope.vocabulary.grandparentcategory,
                label: '',
                allowTyping: false
            }
        }
    });

    // Create a category picker to filter by parentcategory.
    var parentCategoryPicker = new google.visualization.ControlWrapper({
        'controlType': 'CategoryFilter',
        'containerId': 'courses_parentcategory_filter_div',
        options: {
            filterColumnIndex: 2,
            ui: {
                caption: $scope.vocabulary.parentcategory,
                label: '',
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
        },
        view: {
            // 0: id
            // 1: grandparentcategory
            // 2: parentcategory
            // 3: course name
            // 4: visibility
            // 5: ID + course name
            columns: [0, 1, 2, 3, 4]
        }
    });

    // Establish dependencies.
    dashboard.bind([nameFilter, idFilter, grandparentCategoryPicker, parentCategoryPicker], [table]);

    // Draw the dashboard.
    dashboard.draw(data);

    // Define what to do when selecting a table row.
    function selectHandler() {
        var selection = table.getChart().getSelection();
        $scope.loadingCourse = true;
        $scope.course = null;
        $scope.courseid = table.getDataTable().getFormattedValue(selection[0].row, 0);
        $scope.didSelectACourse($scope.courseid);
        $("html, body").animate({scrollTop: 0}, 800);
    };

    function stateChangeHandler() {
        $scope.csv_out = dataTableToCSV(table.getDataTable());
    };

    // Setup listener to listen for clicks on table rows and process the selectHandler.
    google.visualization.events.addListener(table, 'select', selectHandler);

    google.visualization.events.addListener(nameFilter, 'ready', function () {
        $('.google-visualization-controls-stringfilter input').prop('placeholder', $scope.vocabulary.search + '...');
    });

    // Setup listeners for statechange in the slider range filters.
    google.visualization.events.addListener(grandparentCategoryPicker, 'statechange', stateChangeHandler);
    google.visualization.events.addListener(parentCategoryPicker, 'statechange', stateChangeHandler);
    google.visualization.events.addListener(nameFilter, 'statechange', stateChangeHandler);
    google.visualization.events.addListener(idFilter, 'statechange', stateChangeHandler);
};

/******************************
 ** Dashboard: EMPTY COURSES **
 ******************************/
var emptyCoursesDashboard = function (result, $scope) {
    var data = new google.visualization.DataTable(result);

    // Create a dashboard.
    var dashboard = new google.visualization.Dashboard(document.getElementById('dashboardEmptyCourses'));

    // Create a search box to search for the course name.
    var nameFilter = new google.visualization.ControlWrapper({
        controlType: 'StringFilter',
        containerId: 'emptycourses_courses_name_filter_div',
        options: {
            filterColumnIndex: 3,
            matchType: 'any',
            ui: {
                label: $scope.vocabulary.course
            }
        }
    });

    // Create a category picker to filter by grandparent category.
    var grandparentCategoryPicker = new google.visualization.ControlWrapper({
        'controlType': 'CategoryFilter',
        'containerId': 'emptycourses_courses_grandparentcategory_filter_div',
        options: {
            filterColumnIndex: 1,
            ui: {
                caption: $scope.vocabulary.grandparentcategory,
                label: '',
                allowTyping: false
            }
        }
    });

    // Create a category picker to filter by parent category.
    var parentCategoryPicker = new google.visualization.ControlWrapper({
        'controlType': 'CategoryFilter',
        'containerId': 'emptycourses_courses_parentcategory_filter_div',
        options: {
            filterColumnIndex: 2,
            ui: {
                caption: $scope.vocabulary.parentcategory,
                label: '',
                allowTyping: false
            }
        }
    });

    // Create the table to display.
    var table = new google.visualization.ChartWrapper({
        chartType: 'Table',
        containerId: 'emptycourses_courses_table_div',
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
        $("html, body").animate({scrollTop: 0}, 800);
    }
    ;

    // Setup listener to listen for clicks on table rows and process the selectHandler.
    google.visualization.events.addListener(table, 'select', selectHandler);

    google.visualization.events.addListener(nameFilter, 'ready', function () {
        $('.google-visualization-controls-stringfilter input').prop('placeholder', $scope.vocabulary.search + '...');
    });
};

/****************************
 ** Dashboard: USER SEARCH **
 ****************************/
var userSearchDashboard = function (result, $scope) {
    var data = new google.visualization.DataTable(result);

    // Create a dashboard
    var dashboard = new google.visualization.Dashboard(document.getElementById('dashboardUserSearch'));

    // Create a search box to search for the user's full name.
    var userNameFilter = new google.visualization.ControlWrapper({
        controlType: 'StringFilter',
        containerId: 'user_name_filter_div',
        options: {
            filterColumnIndex: 5,
            matchType: 'any',
            ui: {
                label: $scope.vocabulary.search
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
            pageSize: 25
        },
        view: {
            // 0: id
            // 1: username
            // 2: first name
            // 3: last name
            // 4: email address
            // 5: full name
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
        $scope.didSelectAUser($scope.userid);
        //$scope.$broadcast('selectedUserIdChanged', $scope.userid);
        $("html, body").animate({scrollTop: 0}, 800);
    }
    ;

    // Setup listener to listen for clicks on table rows and process the selectHandler.
    google.visualization.events.addListener(table, 'select', selectHandler);

    google.visualization.events.addListener(userNameFilter, 'ready', function () {
        $('.google-visualization-controls-stringfilter input').prop('placeholder', $scope.vocabulary.search + '...');
    });
};

/*******************************
 ** Dashboard: INACTIVE USERS **
 *******************************/
var inactiveUsersDashboard = function (result, $scope) {
    var data = new google.visualization.DataTable(result);

    // Create a dashboard
    var dashboard = new google.visualization.Dashboard(document.getElementById('dashboardInactiveUsers_div'));

    // Create a search box to search for the user name.
    var dateOfLastAccessFilter = new google.visualization.ControlWrapper({
        controlType: 'DateRangeFilter',
        containerId: 'inactiveUsers_dateOfLastAccessFilter_div',
        options: {
            filterColumnIndex: 6
        }
    });

    var timeSinceLastAccessFilter = new google.visualization.ControlWrapper({
        controlType: 'NumberRangeFilter',
        containerId: 'inactiveUsers_timeSinceLastAccessFilter_div',
        options: {
            filterColumnIndex: 7
        }
    });

    // Create the table to display.
    var table = new google.visualization.ChartWrapper({
        chartType: 'Table',
        containerId: 'inactiveUsers_user_table_div',
        options: {
            showRowNumber: false,
            page: 'enable',
            pageSize: 25,
            // Only counts the displayed rows!
            sortColumn: 5,
            sortAscending: false,
            ui: {
                format: {
                    //pattern: ""
                }
            }
        },
        view: {
            // 0: id
            // 1: username
            // 2: first name
            // 3: last name
            // 4: email address
            // 5: full name
            // 6: last access
            // 7: days since last access
            columns: [1, 2, 3, 4, 6, 7]
        }
    });

    // Establish dependencies.
    dashboard.bind([dateOfLastAccessFilter, timeSinceLastAccessFilter], [table]);

    // Draw the dashboard.
    dashboard.draw(data);

    // Define what to do when selecting a table row.
    function selectHandler() {
        $scope.loadingUser = true;
        $scope.user = null;
        var selection = table.getChart().getSelection();
        $scope.userid = table.getDataTable().getFormattedValue(selection[0].row, 0);
        $scope.didSelectAUser($scope.userid);
        //$scope.$broadcast('selectedUserIdChanged', $scope.userid);
        $("html, body").animate({scrollTop: 0}, 800);
    };

    $scope.numberOfRowsShownInactiveUsers = data.getNumberOfRows();
    // Define what to do when changing the state on one of the slider range filters.
    function stateChangeHandler() {
        $scope.numberOfRowsShownInactiveUsers = table.getDataTable().getNumberOfRows();
        $scope.loadDataCourseSearch();
    };

    // Setup listener to listen for clicks on table rows and process the selectHandler.
    google.visualization.events.addListener(table, 'select', selectHandler);

    // Setup listeners for statechange in the slider range filters.
    google.visualization.events.addListener(dateOfLastAccessFilter, 'statechange', stateChangeHandler);
    google.visualization.events.addListener(timeSinceLastAccessFilter, 'statechange', stateChangeHandler);
};

/********************************
 ** Dashboard: COURSES OF USER **
 ********************************/
var coursesOfUserDashboard = function (result, $scope, element) {
    var data = new google.visualization.DataTable(result);

    // Create a dashboard
    //var dashboard = new google.visualization.Dashboard(document.getElementById('dashboardCoursesOfUser'));
    var dashboard = new google.visualization.Dashboard($(element).find(".dashboardCoursesOfUser").first());
    // Create a search box to search for a course name.
    var nameFilter = new google.visualization.ControlWrapper({
        controlType: 'StringFilter',
        containerId: 'coursesOfUser_name_filter_div_' + $scope.activeUsers,
        options: {
            filterColumnIndex: 3,
            matchType: 'any',
            ui: {
                label: $scope.vocabulary.name
            }
        }
    });

    // Create a category picker to filter by grandparent category.
    var grandparentCategoryPicker = new google.visualization.ControlWrapper({
        'controlType': 'CategoryFilter',
        'containerId': 'coursesOfUser_grandparentcategory_filter_div_' + $scope.activeUsers,
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
        'containerId': 'coursesOfUser_parentcategory_filter_div_' + $scope.activeUsers,
        options: {
            filterColumnIndex: 2,
            ui: {
                caption: $scope.vocabulary.parentcategory,
                label: '',
                allowTyping: false
            }
        }
    });

    // Create a category picker to filter by role.
    var roleCategoryPicker = new google.visualization.ControlWrapper({
        'controlType': 'CategoryFilter',
        'containerId': 'coursesOfUser_role_filter_div_' + $scope.activeUsers,
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
        containerId: 'coursesOfUser_table_div_' + $scope.activeUsers,
        options: {
            showRowNumber: false,
            page: 'enable',
            pageSize: 25,
            allowHtml: true,
            sortColumn: 2,
            sortAscending: true
        },
        view: {
            // 0: id
            // 1: grandparent category
            // 2: parent category
            // 3: course name
            // 4: user's role in course
            // 5: visibility
            columns: [0, 1, 2, 3, 4, 5]
        }
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
    }
    ;

    // Setup listener to listen for clicks on table rows and process the selectHandler.
    google.visualization.events.addListener(table, 'select', selectHandler);

    google.visualization.events.addListener(nameFilter, 'ready', function () {
        $('.google-visualization-controls-stringfilter input').prop('placeholder', $scope.vocabulary.search + '...');
    });
};

/**********************
 ** Dashboard: FILES **
 **********************/
var filesDashboard = function (result, $scope) {
    var data = new google.visualization.DataTable(result);

    // Create a dashboard
    var dashboard = new google.visualization.Dashboard(document.getElementById('dashboardfiles_div'));

    // Create a search box to search for a file by name.
    var nameFilter = new google.visualization.ControlWrapper({
        controlType: 'StringFilter',
        containerId: 'files_name_filter_div',
        options: {
            filterColumnIndex: 1,
            matchType: 'any',
            ui: {
                //label: $scope.vocabulary.name
            }
        }
    });

    // Create a search box to search for a file by file title.
    var filenameFilter = new google.visualization.ControlWrapper({
        controlType: 'StringFilter',
        containerId: 'files_filename_filter_div',
        options: {
            filterColumnIndex: 2,
            matchType: 'any',
            ui: {
                //label: $scope.vocabulary.name
            }
        }
    });

    // Create a range filter to filter by filesize.
    var fileSizeFilter = new google.visualization.ControlWrapper({
        controlType: 'NumberRangeFilter',
        containerId: 'files_filesize_filter_div',
        options: {
            filterColumnIndex: 3
        }
    });

    // Create a category picker to filter by mime type.
    var mimeTypePicker = new google.visualization.ControlWrapper({
        'controlType': 'CategoryFilter',
        'containerId': 'files_mimetype_filter_div',
        options: {
            filterColumnIndex: 4,
            ui: {
                caption: $scope.vocabulary.choosedots,
                label: 'mimetype',
                allowTyping: false
            }
        }
    });

    // Create the table to display.
    var table = new google.visualization.ChartWrapper({
        chartType: 'Table',
        containerId: 'files_table_div',
        options: {
            showRowNumber: false,
            page: 'enable',
            pageSize: 100,
            allowHtml: true
                    //sortColumn: 2,
                    //sortAscending: true
        },
        view: {
            // 0: course id
            // 1: file title
            // 2: filename
            // 3: filesize
            // 4: mime type
            //columns: [0, 1, 2, 3, 4, 5]
        }
    });

    // Establish dependencies.
    dashboard.bind([nameFilter, filenameFilter, fileSizeFilter, mimeTypePicker], [table]);

    // Draw the dashboard.
    dashboard.draw(data);

    // Define what to do when selecting a table row.
    function selectHandler() {
        /*
         $scope.loadingCourse = true;
         $scope.ucourse = null;
         var selection = table.getChart().getSelection();
         $scope.courseid = table.getDataTable().getFormattedValue(selection[0].row, 0);
         $scope.didSelectACourse($scope.courseid);
         $("html, body").animate({scrollTop: 0}, 800);
         */
        console.log(result);
    }
    ;

    // Setup listener to listen for clicks on table rows and process the selectHandler.
    google.visualization.events.addListener(table, 'select', selectHandler);

    google.visualization.events.addListener(nameFilter, 'ready', function () {
        $('.google-visualization-controls-stringfilter input').prop('placeholder', $scope.vocabulary.search + '...');
    });

    google.visualization.events.addListener(filenameFilter, 'ready', function () {
        $('.google-visualization-controls-stringfilter input').prop('placeholder', $scope.vocabulary.search + '...');
    });
};

/****************************************
 ** Dashboard: COURSES WITH ACTIVITIES **
 ****************************************/
var coursesWithActivitiesDashboard = function (result, $scope, url) {
    var data = new google.visualization.DataTable(result);

    // Create a dashboard
    var dashboard = new google.visualization.Dashboard(document.getElementById('dashboardcourseswithactivities_div'));

    // Create a category picker to filter by parent category.
    var grandparentFilter = new google.visualization.ControlWrapper({
        'controlType': 'CategoryFilter',
        'containerId': 'courseswithactivities_grandparent_filter_div',
        options: {
            filterColumnIndex: 1,
            ui: {
                caption: $scope.vocabulary.grandparentcategory,
                label: '',
                allowTyping: false
            }
        }
    });

    var parentFilter = new google.visualization.ControlWrapper({
        'controlType': 'CategoryFilter',
        'containerId': 'courseswithactivities_parent_filter_div',
        options: {
            filterColumnIndex: 2,
            ui: {
                caption: $scope.vocabulary.parentcategory,
                label: '',
                allowTyping: false
            }
        }
    });

    // Create a search box to search for a course name.
    var nameFilter = new google.visualization.ControlWrapper({
        controlType: 'StringFilter',
        containerId: 'courseswithactivities_name_filter_div',
        options: {
            filterColumnIndex: 3,
            matchType: 'any',
            ui: {
                //label: $scope.vocabulary.name
            }
        }
    });

    var numberFilter = new google.visualization.ControlWrapper({
        controlType: 'NumberRangeFilter',
        containerId: 'courseswithactivities_number_filter_div',
        options: {
            filterColumnIndex: 4,
            ui: {
                labelStacking: "horizontal"
            }
        }
    });

    var visibilityFilter = new google.visualization.ControlWrapper({
        controlType: 'CategoryFilter',
        containerId: 'courseswithactivities_visibility_filter_div',
        options: {
            filterColumnIndex: 5,
            ui: {
                caption: $scope.vocabulary.visible,
                label: '',
                allowTyping: false
            }
        }
    });

    // Create the table to display.
    var table = new google.visualization.ChartWrapper({
        chartType: 'Table',
        containerId: 'courseswithactivities_table_div',
        options: {
            showRowNumber: false,
            page: 'enable',
            pageSize: 100,
            allowHtml: true,
            sortColumn: 0,
            sortAscending: false
        },
        view: {
            // 0: activity id
            // 1: grandparent category
            // 2: parent category
            // 3: course name
            // 4: number of activities
            // 5: visibility
            //columns: [0, 1, 2, 3, 4, 5]
        }
    });

    // Establish dependencies.
    dashboard.bind([grandparentFilter, parentFilter, nameFilter, numberFilter, visibilityFilter], [table]);

    // Draw the dashboard.
    dashboard.draw(data);

    // Define what to do when selecting a table row.
    function selectHandler() {
        $scope.loadingCourse = true;
        $scope.course = null;
        var selection = table.getChart().getSelection();
        $scope.courseid = table.getDataTable().getFormattedValue(selection[0].row, 0);
        $scope.didSelectACourse($scope.courseid);
        //$('#myTabList a:first').tab('show');
        $("html, body").animate({scrollTop: 0}, 800);
    }
    ;

    // Setup listener to listen for clicks on table rows and process the selectHandler.
    google.visualization.events.addListener(table, 'select', selectHandler);

    google.visualization.events.addListener(nameFilter, 'ready', function () {
        $('.google-visualization-controls-stringfilter input').prop('placeholder', $scope.vocabulary.search + '...');
    });
};

/**
 * Helper Function for generating a random password
 * @returns {String} 10 char long random password
 */
function generatePassword() {
    var length = 10,
            charset = "abcdefghijklnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789",
            retVal = "";
    for (var i = 0, n = charset.length; i < length; ++i) {
        retVal += charset.charAt(Math.floor(Math.random() * n));
    }
    return retVal;
}


/*********************
 ** Dashboard: URLs **
 *********************/
var allURLsDashboard = function (result, $scope, url) {
    var data = new google.visualization.DataTable(result);

    // Create a dashboard
    var dashboard = new google.visualization.Dashboard(document.getElementById('dashboardURLs'));


    // Create a category picker to filter by parent category.
    var grandparentFilter = new google.visualization.ControlWrapper({
        'controlType': 'CategoryFilter',
        'containerId': 'urls_grandparentcategory_filter_div',
        options: {
            filterColumnIndex: 2,
            ui: {
                caption: $scope.vocabulary.grandparentcategory,
                label: '',
                allowTyping: false
            }
        }
    });

    var parentFilter = new google.visualization.ControlWrapper({
        'controlType': 'CategoryFilter',
        'containerId': 'urls_parentcategory_filter_div',
        options: {
            filterColumnIndex: 3,
            ui: {
                caption: $scope.vocabulary.parentcategory,
                label: '',
                allowTyping: false
            }
        }
    });

    // Create a search box to search for a course name.
    var nameFilter = new google.visualization.ControlWrapper({
        controlType: 'StringFilter',
        containerId: 'urls_name_filter_div',
        options: {
            filterColumnIndex: 5,
            matchType: 'any',
            ui: {
                //label: $scope.vocabulary.name
            }
        }
    });

    // Create a search box to search for a course name.
    var courseShortnameFilter = new google.visualization.ControlWrapper({
        controlType: 'StringFilter',
        containerId: 'urls_course_shortname_filter_div',
        options: {
            filterColumnIndex: 4,
            matchType: 'any',
            ui: {
                //label: $scope.vocabulary.name
            }
        }
    });

    var urlFilter = new google.visualization.ControlWrapper({
        controlType: 'StringFilter',
        containerId: 'urls_url_filter_div',
        options: {
            filterColumnIndex: 6,
            matchType: 'any',
            ui: {
                labelStacking: "horizontal"
            }
        }
    });


    // Create the table to display.
    var table = new google.visualization.ChartWrapper({
        chartType: 'Table',
        containerId: 'urls_table_div',
        options: {
            showRowNumber: false,
            page: 'enable',
            pageSize: 100,
            allowHtml: true,
            sortColumn: 0,
            sortAscending: false
        },
        view: {
            // 0: id
            // 1: course id
            // 2: grandparent category
            // 3: parent category
            // 4: course shortname
            // 5: course fullname
            // 6: url
            columns: [0, 1, 2, 3, 4, 5, 6]
        }
    });

    // Establish dependencies.
    dashboard.bind([grandparentFilter, parentFilter, nameFilter, urlFilter, courseShortnameFilter], [table]);

    // Draw the dashboard.
    dashboard.draw(data);

    // Define what to do when selecting a table row.
    function selectHandler() {
        $scope.loadingCourse = true;
        $scope.course = null;
        var selection = table.getChart().getSelection();
        $scope.courseid = table.getDataTable().getFormattedValue(selection[0].row, 1);
        console.log("Hello World!" + $scope.courseid);
        $scope.didSelectACourse($scope.courseid);
        //$('#myTabList a:first').tab('show');
        $("html, body").animate({scrollTop: 0}, 800);
    }
    ;

    // Setup listener to listen for clicks on table rows and process the selectHandler.
    google.visualization.events.addListener(table, 'select', selectHandler);

    google.visualization.events.addListener(nameFilter, 'ready', function () {
        $('.google-visualization-controls-stringfilter input').prop('placeholder', $scope.vocabulary.search + '...');
    });

};
