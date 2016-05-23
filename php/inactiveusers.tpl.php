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

This page lists all users with the option of filtering by inactivity.
Detailed information is shown on the right hand side of the screen after clicking on a user.
-->

<div class="panel panel-default">
    <div class="panel-heading">
        <h3>{{vocabulary.users}} ({{vocabulary.inactive}})</h3>
        <h4>{{vocabulary.total}}: {{numberOfRowsShownInactiveUsers}}</h4>
    </div>
    
    <div class="panel-body">
        <div class="row">
            <div class="col-md-6">
                <div id="dashboardInactiveUsers_div">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-md-10">
                                    <div id="inactiveUsers_dateOfLastAccessFilter_div"></div>
                                    <div id="inactiveUsers_timeSinceLastAccessFilter_div"></div>
                                </div>
                                
                                <div class="col-md-2">
                                    <!-- refresh button -->
                                    <button type="button" class="btn btn-default pull-right" aria-label="Refresh" ng-click="loadDataCourseSearch()" title="{{vocabulary.refresh}}">
                                        <span><img src="<?php echo $wwwroot ?>/pix/i/reload.png"></span>
                                    </button>
                                </div>
                            </div> <!-- row -->
                        </div> <!-- panel-heading -->
                        
                        <div class="panel-body" id="inactiveUsers_user_table_div">
                            <loader ng-hide="gotAllUsers"></loader>
                        </div>
                    </div> <!-- panel panel-info -->
                </div>
            </div> <!-- col-md-6 -->
            
            <div class="col-md-6">
                <userinfo ng-show="userid" activeUsers="0"></userinfo>
            </div>
        </div> <!-- row -->
    </div> <!-- panel-body -->
</div> <!-- panel panel-default -->