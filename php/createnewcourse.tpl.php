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

This page contains a form to create a new course.
-->

<div class="panel panel-default">
    <div class="panel-heading">
        <h3>{{vocabulary.newcourse}}</h3>
    </div>
    
    <div class="panel-body">
        <form name="form" novalidate>
            <div class="row">
                <div class="col-md-2">
                    {{vocabulary.shortname}}
                </div>
                <div class="col-md-10">
                    <input type="text" placeholder="{{vocabulary.shortname}}" ng-model="shortname" name="shortname" required="" />
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    {{vocabulary.fullname}}
                </div>
                <div class="col-md-10">
                    <input type="text" placeholder="{{vocabulary.fullname}}" ng-model="fullname" name="fullname" required="" />
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    {{vocabulary.category}}
                </div>
                <div class="col-md-10">
                    <select ng-model="category">
                        <option ng-repeat="category in allCategories | toArray | orderBy:'name'" value="{{category.id}}">{{category.name}}</option>
                    </select>
                </div>
            </div>
    
            <p></p>
    
            <div class="row">
                <div class="col-md-12">
                    <input type="radio" ng-model="password" value="nopassword" /> {{vocabulary.nopassword}}
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <input type="radio" ng-model="password" value="userpassword" />
                    <input type="text" placeholder="{{vocabulary.password}}" ng-model="userpassword" name="password" required="" />
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <input type="radio" ng-model="password" value="randompassword" /> Random {{vocabulary.password}}
                </div>
            </div>
    
            <p></p>
    
            <button type="button" class="btn btn-default" aria-label="newcourse.vocabulary.createnewcourse" ng-click="createNewCourse()">
                <span><img src="<?php echo $wwwroot ?>/pix/t/add.png"></span> {{vocabulary.createnewcourse}}
            </button>
            
            <button type="button" class="btn btn-default" aria-label="newcourse.vocabulary.reset" ng-click="reset()">
                <span><img src="<?php echo $wwwroot ?>/pix/i/return.png"></span> {{vocabulary.reset}}
            </button>
        </form>
    </div> <!-- panel-body -->
</div> <!-- panel panel-default -->