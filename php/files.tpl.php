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

This page lists all users with the option of filtering by inactivity.
Detailed information is shown on the right hand side of the screen after clicking on a user.
-->

<div class="panel panel-default" ng-controller="FilesController" id="dashboardFiles">
    <div class="panel-heading">
        <h3>{{vocabulary.allfiles}}</h3>
    </div>
    
    <div class="panel-body">
        <div class="panel panel-info">
            <div class="panel-heading">
                <div id="files_name_filter_div"></div>
                <div id="files_filename_filter_div"></div>
                <div id="files_filesize_filter_div"></div>
                <div id="files_mimetype_filter_div"></div>
            </div> <!-- panel-heading -->
            <div class="panel-body" id="files_table_div">
                <loader ng-hide="gotAllFiles"></loader>
            </div>
        </div> <!-- panel panel-info -->
    </div> <!-- panel-body -->
</div> <!-- panel panel-default -->