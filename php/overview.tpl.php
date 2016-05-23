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

This file joins together
    - coursesearch
    - courseinfo
    - usersearch and
    - userinfo
on one page.

It will also show an error message if the user is not logged into moodle with the proper rights (at least course creator).
-->

<modal title="Error" visible="showModal">
    <form role="form">
        <div class="alert alert-danger" role="alert"><strong>Login Error</strong></div>
        <button type="submit" class="btn btn-default" onclick="location.reload();">Reload</button>
    </form>
</modal>

<div class="row main">
    <div class="col-md-6" id="course">
        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="panel-title">
                    <h3>{{vocabulary.course}}</h3>
                </div>
            </div>
            <div class="panel-body">
                <courseinfo ng-show="courseid"></courseinfo>
                <coursesearch></coursesearch>
            </div>
        </div>
    </div>
    <div class="col-md-6" id="user">
        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="panel-title">
                    <h3>{{vocabulary.user}}</h3>
                </div>
            </div>
            <div class="panel-body">
                <userinfo ng-show="userid" activeUsers="1"></userinfo>
                <usersearch></usersearch>
            </div>
        </div>
    </div>
</div>