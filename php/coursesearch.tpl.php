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

This page lists all courses along with a search box and the option to filter by parent and/or grandparent category.
Selecting a course will load the courseinfo page.
-->

<div id="dashboardCourseSearch">
    <div class="panel panel-info">
        <div class="panel-heading">
            <div class="row">
                <div class="col-md-10" id="courses_name_filter_div"></div>
                <div class="col-md-2">
                        <!-- Export Table as CSV button -->
                        <button type="button" class="btn btn-default" aria-label="Export CSV" ng-click="downloadDataTableAsCSV()" title="Export Table as CSV">
                            <span><img src="<?php echo $wwwroot ?>/pix/i/export.png"></span>
                        </button>
                        
                        <!-- refresh button -->
                        <button type="button" class="btn btn-default" aria-label="Refresh" ng-click="loadDataCourseSearch()" title="{{vocabulary.refresh}}">
                            <span><img src="<?php echo $wwwroot ?>/pix/i/reload.png"></span>
                        </button>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12" id="courses_id_filter_div"></div>
            </div>
            <div class="row">
                <div class="col-md-3" id="courses_grandparentcategory_filter_div"></div>
                <div class="col-md-3" id="courses_parentcategory_filter_div"></div>
            </div> <!-- row -->
        </div> <!-- panel-heading -->

        <div class="panel-body" id="courses_table_div">
            <loader ng-hide="gotAllCourses"></loader>
        </div>
    </div> <!-- panel panel-info -->
</div>