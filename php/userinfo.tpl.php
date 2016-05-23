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

This page displays detailed Information about a user.
-->

<div>
    <div class="panel panel-primary">
        <!--
        ======================================================
        The panel header consists of:
            1) the user's name
            2) a button group containing 2 buttons:
                2.1) a button link to edit the user's settings
                2.2) refresh button
                2.2) close butto
        ======================================================
        -->
        <div class="panel-heading">
            <div class="panel-title row">
                <div class="col-md-12">
                    <!-- 1) user's name -->
                    <a href="<?php echo $wwwroot ?>/user/profile.php?id={{user.id.v}}" target="_blank" style="color: #000000">
                        {{user.firstname.v}} {{user.lastname.v}}
                    </a>
                    &nbsp;&nbsp;&nbsp;
                    <a href="<?php echo $wwwroot ?>/course/loginas.php?id=1&user={{user.id.v}}&sesskey={{user.sessionkey.v}}" target="_blank">
                        <button type="button" class="btn btn-default btn-sm">
                            <span><img src="<?php echo $wwwroot ?>/pix/t/right.png"></span> {{vocabulary.loginas}}
                        </button>
                    </a>

                    <!-- 2) button group -->
                    <div class="btn-group btn-group-xs pull-right" role="group">

                        <!-- 2.0) "search user" button -->
                        <a href="https://www.google.com/search?q={{user.firstname.v}}+{{user.lastname.v}}+<?php
                        $exploded = explode('.', $_SERVER['SERVER_NAME']);
                        echo $exploded[count($exploded) - 2];
                        ?>" target="_blank" title="{{vocabulary.search}}">
                            <button type="button" class="btn btn-default" aria-label="Search for user on Google">
                                <span><img src="<?php echo $wwwroot ?>/pix/t/preview.png"></span>
                            </button>
                        </a>

                        <!-- 2.1) "edit user" button -->
                        <a href="<?php echo $wwwroot ?>/user/editadvanced.php?id={{user.id.v}}" target="_blank" title="{{vocabulary.edit}}">
                            <button type="button" class="btn btn-default" aria-label="Edit User">
                                <span><img src="<?php echo $wwwroot ?>/pix/t/editstring.png"></span>
                            </button>
                        </a>

                        <!-- 2.2) refresh button -->
                        <!--
                        <a title="{{vocabulary.refresh}}">
                            <button type="button" class="btn btn-default" aria-label="Refresh" ng-click="loadDataUserInfo()">
                                <span><img src="/pix/i/reload.png"></span>
                            </button>
                        </a>
                        -->

                        <!-- 2.3) close button -->
                        <a title="{{vocabulary.hidesection}}">
                            <button type="button" class="btn btn-default" aria-label="Close" ng-click="userid = false;">
                                <span><img src="<?php echo $wwwroot ?>/pix/t/switch_minus.png"></span>
                            </button>
                        </a>
                    </div> <!-- btn-group -->
                </div> <!-- col-md-12 -->
            </div>  <!-- panel-title row -->
        </div>  <!-- panel-heading -->

        <!--
        =================================================================================
        The panel body consists of 3 nested panels:
            1) directly enrol user in the course selected on the left-hand side (only visible when a course is selected)
            2) detailed information about the user
            3) an interactive list of all courses the user is enrolled in (with course search and filter-by-parent-/grandparentcategory and/or -role
        =================================================================================
        -->
        <div class="panel-body">
            <loader ng-show="loadingUser"></loader>

            <!-- 1) directly enrol user in selected course (only visible when a course is selected) -->
            <div class="panel panel-default" ng-show="activeUsers == 1 && courseid">
                <div class="row panel-body">
                    <div class="col-md-4">
                        <button ng-click="addUserToCourse(user.id.v, course.data.id.v, selectedRole);" type="button" class="btn btn-default" aria-label="">
                            <span><img src="<?php echo $wwwroot ?>/pix/t/left.png"></span> {{vocabulary.enrol}}
                        </button>
                    </div>
                    <div class="col-md-8">
                        <label>{{vocabulary.role}} ({{vocabulary.default}}: {{vocabulary.student}}):</label>
                        <select ng-model="selectedRole">
                            <option ng-repeat="role in course.data.assignableRoles" value="{{role.id}}">{{role.name}}</option>
                        </select>
                    </div>
                </div> <!-- row panel-body -->
            </div> <!-- panel panel-default -->

            <!-- 2) detailed user information -->
            <div class="panel panel-default">
                <div class="panel-heading panel-title">
                    <div class="row">
                        <div class="col-md-9">
                            {{vocabulary.personal}}
                        </div>

                        <!-- refresh button -->
                        <div class="col-md-3">
                            <button type="button" class="btn btn-default pull-right" aria-label="Refresh" ng-click="loadDataUserInfo()" title="{{vocabulary.refresh}}">
                                <span><img src="<?php echo $wwwroot ?>/pix/i/reload.png"></span>
                            </button>
                        </div>
                    </div> <!-- row -->
                </div> <!-- panel-heading panel-title -->

                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-4">{{user.id.string}}</div>
                        <div class="col-md-8">{{user.id.v}}</div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">{{user.username.string}}</div>
                        <div class="col-md-8">{{user.username.v}}</div>
                    </div>

                    <p></p>

                    <div class="row">
                        <div class="col-md-4">{{user.firstname.string}}</div>
                        <div class="col-md-8">{{user.firstname.v}}</div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">{{user.lastname.string}}</div>
                        <div class="col-md-8">{{user.lastname.v}}</div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">{{user.email.string}}</div>
                        <div class="col-md-8"><a href="mailto:{{user.email.v}}">{{user.email.v}}</a></div>
                    </div>

                    <p></p>

                    <div class="row">
                        <div class="col-md-4">{{user.firstaccess.string}}</div>
                        <div class="col-md-8">{{user.firstaccess.v}}</div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">{{user.lastlogin.string}}</div>
                        <div class="col-md-8">{{user.lastlogin.v}}</div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">{{user.lastaccess.string}}</div>
                        <div class="col-md-8">{{user.lastaccess.v}}</div>
                    </div>

                    <p></p>

                    <div class="row">
                        <div class="col-md-4">{{user.auth.string}}</div>
                        <div class="col-md-8">{{user.auth.v}}</div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">{{user.lang.string}}</div>
                        <div class="col-md-8">{{user.lang.v}}</div>
                    </div>
                </div> <!-- panel-body -->
            </div> <!-- panel panel-default -->

            <p></p>

            <!-- 3) interactive list of courses the user is enrolled in -->
            <div class="dashboardCoursesOfUser">
                <div class="panel panel-default">
                    <div class="panel-heading panel-title">
                        <div class="row">
                            <div class="col-md-9">
                                {{vocabulary.searchcourses}}
                            </div>

                            <!-- refresh button -->
                            <div class="col-md-3">
                                <button type="button" class="btn btn-default pull-right" aria-label="Refresh" ng-click="loadDataUserInfo()" title="{{vocabulary.refresh}}">
                                    <span><img src="<?php echo $wwwroot ?>/pix/i/reload.png"></span>
                                </button>
                            </div>
                        </div> <!-- row -->
                    </div> <!-- panel-heading panel-title -->

                    <div class="panel-body">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <div ng-attr-id="{{'coursesOfUser_name_filter_div_' + activeUsers}}"></div>
                                <div class="row">
                                    <div class="col-md-4" ng-attr-id="{{'coursesOfUser_grandparentcategory_filter_div_' + activeUsers}}"></div>
                                    <div class="col-md-4" ng-attr-id="{{'coursesOfUser_parentcategory_filter_div_' + activeUsers}}"></div>
                                    <div class="col-md-4" ng-attr-id="{{'coursesOfUser_role_filter_div_' + activeUsers}}"></div>
                                </div>
                            </div>

                            <div class="panel-body">
                                <div ng-attr-id="{{'coursesOfUser_table_div_' + activeUsers}}"></div>
                            </div>
                        </div>
                    </div> <!-- panel-body -->
                </div> <!-- panel panel-default -->
            </div>
        </div>  <!-- panel-body -->
    </div>  <!-- panel-primary -->
</div>