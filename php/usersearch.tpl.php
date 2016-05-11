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

This page lists all users with an option to search by string.
Selecting a user will load the userinfo page.
-->

<div id="dashboardUserSearch">
    <div class="panel panel-info">
        <div class="panel-heading">
            <div class="row">
                <div class="col-md-10" id="user_name_filter_div"></div>
                <div class="col-md-2">
                    <!-- refresh button -->
                    <button type="button" class="btn btn-default pull-right" aria-label="Refresh" ng-click="loadDataUserSearch()" title="{{vocabulary.refresh}}">
                        <span><img src="<?php echo $wwwroot ?>/pix/i/reload.png"></span>
                    </button>
                </div>
            </div> <!-- row -->
        </div> <!-- panel-heading -->
        
        <div class="panel-body" id="user_table_div">
            <loader ng-hide="gotAllUsers"></loader>
        </div>
    </div> <!-- panel panel-info -->
</div>