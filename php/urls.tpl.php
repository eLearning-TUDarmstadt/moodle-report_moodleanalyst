<?php
require(dirname(__FILE__) . '/../../../config.php');
require_capability('report/moodleanalyst:view', \context::instance_by_id(10));
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

This page lists all urls with the option of filtering.
Detailed information is shown on the right hand side of the screen after clicking on a url.
-->

<div class="panel panel-default" ng-controller="URLsController" id="dashboardURLs">

    <div class="panel-heading">
        <h3>{{vocabulary.url.modulenameplural}}</h3>
    </div>

    <div class="panel-body">
        <div class="col-md-6">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <div id="urls_grandparentcategory_filter_div"></div>
                    <div id="urls_parentcategory_filter_div"></div>
                    <div id="urls_course_shortname_filter_div"></div>
                    <div id="urls_name_filter_div"></div>
                    <div id="urls_url_filter_div"></div>
                </div> <!-- panel-heading -->
                <div class="panel-body" id="urls_table_div">
                    <loader ng-hide="gotAllURLs"></loader>
                </div>
            </div> <!-- panel panel-info -->
        </div> <!-- panel-body -->
        <div class="col-md-6">
            <courseinfo></courseinfo>
        </div>
    </div> <!-- panel panel-default -->

</div>
