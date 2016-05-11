<?php
require(dirname(__FILE__) . '/../../../config.php');
require_capability('report/moodleanalyst:view', \context::instance_by_id(10));
$wwwroot = $CFG->wwwroot;
?>

<!--
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

This page displays detailed Information about a course
-->

<div id="courseInfo">
    <div class="panel panel-primary">
        <!--
        ======================================================
        The panel header consists of:
            1) the course's short name
            2) a button group that consists of 4 button links:
                2.1) edit course settings
                2.2) show/hide course from student's view
                2.2) delete course
                2.4) refresh button
                2.5) close button
        ======================================================
        -->
        <div class="panel-heading">
            <div class="panel-title row">
                <div class="col-md-12">
                    <!-- 1) course's short name (dimmed if not visible to students -->
                    <a href="<?php echo $wwwroot ?>/course/view.php?id={{course.data.id.v}}" target="_blank" style="color: #000000">
                        <h ng-class="{'dimmed': course.data.visible.v == 0}">{{course.data.shortname.v}}</h>
                    </a>
                    <br />
                    <i ng-show="course.data.visible.v == 0" style="color: #000000">{{vocabulary.coursehidden}}</i>

                    <!-- 2) button group -->
                    <div class="btn-group btn-group-xs pull-right" role="group" ng-show="course.data">
                        
                        <!-- 2.1) edit course settings button -->
                        <a href="<?php echo $wwwroot ?>/course/edit.php?id={{course.data.id.v}}" target="_blank" title="{{vocabulary.editsettings}}">
                            <button type="button" class="btn btn-default" aria-label="Edit Course">
                                <span><img src="<?php echo $wwwroot ?>/pix/t/edit.png"></span>
                            </button>
                        </a>
                        
                        <!-- 2.2) hide/show course to users button -->
                        <a title="{{vocabulary.hide}}">
                            <button type="button" class="btn btn-default" aria-label="Hide Course from Students" ng-show="course.data.visible.v == 1" ng-click="changeVisibility(course.data.id.v, 0);">
                                <span><img src="<?php echo $wwwroot ?>/pix/t/hide.png"></span>
                            </button>
                        </a>
                        <a title="{{vocabulary.show}}">
                            <button type="button" class="btn btn-default" aria-label="Make Course visible to Students" ng-show="course.data.visible.v == 0" ng-click="changeVisibility(course.data.id.v, 1);">
                                <span><img src="<?php echo $wwwroot ?>/pix/t/show.png"></span>
                            </button>
                        </a>
                        
                        <!-- 2.3) delete course button -->
                        <a href="<?php echo $wwwroot ?>/course/delete.php?id={{course.data.id.v}}" target="_blank" title="{{vocabulary.delete}}">
                            <button type="button" class="btn btn-default" aria-label="Delete Course">
                                <span><img src="<?php echo $wwwroot ?>/pix/t/delete.png"></span>
                            </button>
                        </a>
                        
                        <!-- 2.4) refresh button -->
                        <!--
                        <a title="{{vocabulary.refresh}}">
                            <button type="button" class="btn btn-default" aria-label="Refresh" ng-click="loadDataCourseInfo()">
                                <span><img src="/pix/i/reload.png"></span>
                            </button>
                        </a>
                        -->
                        
                        <!-- 2.5) close button -->
                        <a title="{{vocabulary.hidesection}}">
                            <button type="button" class="btn btn-default" aria-label="Close" ng-click="courseid = false;">
                                <span><img src="<?php echo $wwwroot ?>/pix/t/switch_minus.png"></span>
                            </button>
                        </a>
                    </div> <!-- btn-group -->
                </div> <!-- col-md-12 -->
            </div>  <!-- panel-title -->
        </div>  <!-- panel-heading -->

        <!--
        =================================================================================
        The panel body consists of 3 nested panels:
            1) general information about the course (such as course id, full name, etc.)
            2) overview of enrolled users
            3) a tab panel consisting of 3 tabs:
                3.1) enrolled users (with filter-by-role and name search) listed in an interactive table where selecting a user opens the userinfo page
                3.2) activities (an overview of all activities in the course
                3.3) enrolment methods
        =================================================================================
        -->
        <div class="panel-body">
            <loader ng-show="loadingCourse"></loader>
            
            <!-- 1) panel "general information about the course" -->
            <div class="panel panel-default" ng-hide="loadingCourse">
                <div class="panel-heading panel-title">
                    <div class="row">
                        <div class="col-md-9">
                            {{vocabulary.courseinfo}}
                        </div>
                        
                        <!-- refresh button -->
                        <div class="col-md-3">
                            <button type="button" class="btn btn-default pull-right" aria-label="Refresh" ng-click="loadDataCourseInfo()" title="{{vocabulary.refresh}}">
                                <span><img src="<?php echo $wwwroot ?>/pix/i/reload.png"></span>
                            </button>
                        </div>
                    </div> <!-- row -->
                </div> <!-- panel-heading panel-title -->
                
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-4">{{course.data.id.string}}</div>
                        <div class="col-md-8">{{course.data.id.v}}</div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">{{course.data.shortname.string}}</div>
                        <div class="col-md-8">{{course.data.shortname.v}}</div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">{{course.data.fullname.string}}</div>
                        <div class="col-md-8">{{course.data.fullname.v}}</div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">{{course.data.idnumber.string}}</div>
                        <div class="col-md-8">{{course.data.idnumber.v}}</div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">{{course.data.visible.string}}</div>
                        <div class="col-md-8">{{course.data.visible.v}}</div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">{{course.data.category.string}}</div>
                        <div class="col-md-8">{{course.data.category.v}}</div>
                    </div>
                </div> <!-- panel-body -->
            </div> <!-- panel panel-default -->
                
            <p></p>
            
            <!-- 2) panel "enrolled users overview" -->
            <div class="panel panel-default" ng-hide="loadingCourse">
                <div class="panel-heading panel-title">
                    <div class="row">
                        <div class="col-md-9">
                            <a href="<?php echo $wwwroot ?>/enrol/users.php?id={{course.data.id.v}}" target="_blank">{{vocabulary.enrolledusers}}: {{course.data.personsInCourse}}</a>
                        </div>
                        
                        <!-- refresh button -->
                        <div class="col-md-3">
                            <button type="button" class="btn btn-default pull-right" aria-label="Refresh" ng-click="loadDataCourseInfo()" title="{{vocabulary.refresh}}">
                                <span><img src="<?php echo $wwwroot ?>/pix/i/reload.png"></span>
                            </button>
                        </div>
                    </div> <!-- row -->
                </div> <!-- panel-heading panel-title -->
                
                <div class="panel-body">
                    <div class="row" ng-repeat="role in course.data.rolesInCourse| orderBy:role.sortorder">
                        <div class="col-md-4"><a ng-click="setRoleFilterForUsersInCourseDashboard(role.name);">{{role.name}}</a></div>
                        <div class="col-md-8">{{role.number}}</div>
                    </div>
                </div>
            </div>

            <!-- 3) panel "tab-panel" -->
            <div class="tab panel panel-default" ng-controller="CourseDetailTabController
                        as CourseDetailTab" ng-hide="loadingCourse" id="tabController">
                
                <ul class="nav nav-pills panel-heading">
                    <li ng-class="{ active: CourseDetailTab.isSet(1) }">
                        <a href ng-click="CourseDetailTab.setTab(1)">{{vocabulary.enrolledusers}}</a>
                    </li>
                    <li ng-class="{ active: CourseDetailTab.isSet(2) }">
                        <a href ng-click="CourseDetailTab.setTab(2)">{{vocabulary.activities}}</a>
                    </li>
                    <li ng-class="{ active: CourseDetailTab.isSet(3) }">
                        <a href ng-click="CourseDetailTab.setTab(3)">{{vocabulary.enrolmentmethods}}</a>
                    </li>
                    
                    <!-- refresh button -->
                    <button type="button" class="btn btn-default pull-right" aria-label="Refresh" ng-click="loadDataCourseInfo()" title="{{vocabulary.refresh}}">
                        <span><img src="<?php echo $wwwroot ?>/pix/i/reload.png"></span>
                    </button>
                </ul>
                
                <div class="panel-body">
                    <!-- 3.1) TAB 1 - enrolled users, detailed -->
                    <div ng-show="CourseDetailTab.isSet(1)" id="dashboardUsersInCourse">
                        <div class="panel panel-default panel-heading row">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div id="usersInCourse_name_filter_div"></div>
                                        <div id="usersInCourse_role_filter_div"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="panel-body" id="usersInCourse_table_div"></div>
                        </div> <!-- panel panel-default panel-heading row -->
                    </div>

                    <!-- 3.2) TAB 2 - activities in course -->
                    <div ng-show="CourseDetailTab.isSet(2)" id="dashboardActivitiesInCourse">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <div class="panel-title"></div>
                                <div id="activitiesInCourse_name_filter_div"></div>
                                <div class="row">
                                    <!-- <div class="col-md-4" id="activities_sectionnr_filter_div"></div> -->
                                    <div class="col-md-6" id="activities_section_filter_div"></div>
                                    <div class="col-md-6" id="activities_type_filter_div"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="panel-body">
                            <div class="btn-group" role="group" aria-label="..." ng-show="activity.id">
                                <a href="<?php echo $wwwroot ?>/mod/{{activity.mod}}/view.php?id={{activity.cm}}" target="_blank">
                                    <button type="button" class="btn btn-default" ng-hide="activity.resourceyesorno">
                                        <span><img src="<?php echo $wwwroot ?>/pix/t/switch_plus.png"></span> {{vocabulary.view}}
                                    </button>
                                    
                                    <button type="button" class="btn btn-default" ng-show="activity.resourceyesorno">
                                        <span><img src="<?php echo $wwwroot ?>/pix/t/restore.png"></span> {{vocabulary.downloadfile}}
                                    </button>
                                </a>
                                
                                <a href="<?php echo $wwwroot ?>/course/mod.php?update={{activity.cm}}" target="_blank">
                                    <button type="button" class="btn btn-default">
                                        <span><img src="<?php echo $wwwroot ?>/pix/t/edit.png"></span> {{vocabulary.editsettings}}
                                    </button>
                                </a>
                                <!-- <a href="/course/mod.php?hide={{activity.cm}}" target="_blank" ng-show="activity.visible == 1"><button type="button" class="btn btn-default"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span> {{vocabulary.show}}</button></a>-->
                                <!-- <a href="/course/mod.php?show={{activity.cm}}" target="_blank" ng-show="activity.visible == 0"><button type="button" class="btn btn-default"><span class="glyphicon glyphicon-eye-close" aria-hidden="true"></span> {{vocabulary.hide}}</button></a>-->
                            </div>
                            <p></p>
                            <div id="activitiesInCourse_table_div"></div>
                        </div> <!-- panel-body -->
                    </div>
                    
                    <!-- 3.3) TAB 3 - enrolment methods -->
                    <div ng-show="CourseDetailTab.isSet(3)" id="dashboardEnrolmentMethods">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <div class="panel-title"><a target="_blank" href="<?php echo $wwwroot ?>/enrol/instances.php?id={{course.data.id.v}}">{{vocabulary.enrolmentmethods}}</a></div>
                            </div>

                            <div class="panel-body">
                                <div ng-repeat="enrol in course.data.enrolmentmethods">
                                    <div class="row"  ng-class="{'dimmed': enrol.visible == 0}">
                                        <div class="col-md-6" ng-hide="enrol.enrol == 'guest'">
                                            <a href="<?php echo $wwwroot ?>/enrol/{{enrol.enrol}}/edit.php?courseid={{course.data.id.v}}&id={{enrol.id}}" target="_blank">{{enrol.name}}</a>
                                        </div>
                                        <div class="col-md-6" ng-show="enrol.enrol == 'guest'">
                                            <a href="<?php echo $wwwroot ?>/course/edit.php?id={{course.data.id.v}}" target="_blank">{{enrol.name}}</a>
                                        </div>
                                        <div class="col-md-3">
                                            {{vocabulary.user}}: {{enrol.number}}
                                        </div>
                                        <div class="col-md-3" ng-show="enrol.password">
                                            {{vocabulary.password}}: {{enrol.password}}
                                        </div>
                                    </div>
                                    <p></p>
                                </div>
                            </div>  <!-- panel-body -->
                        </div> <!-- panel panel-default -->
                    </div>
                    
                </div> <!-- panel-body -->
            </div>  <!-- tab panel panel-default -->
        </div>  <!-- panel-body -->
    </div>  <!-- panel-primary -->
</div>