<?php
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
 * @package    report_moodleanalyst
 * @copyright  2015, Nils Muzzulini
 * @copyright  2015, Steffen Pegenau
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$wwwroot = $CFG->wwwroot;

defined('MOODLE_INTERNAL') || die;

$context = context_system::instance();
require_capability('report/moodleanalyst:view', $context);

$googleLibs = 'https://www.google.com/jsapi?autoload={%22modules%22:[{%22name%22:%22visualization%22,%22version%22:%221%22,%22packages%22:[%22table%22,%22corechart%22,%22controls%22]}]}';
/*
 * Old referrer to angular.html 
  echo '<script type="text/javascript">
  window.location = "/report/moodleanalyst/html/angular.html";
  </script>';
 */

echo '<html>
    <head>
        <title>Moodle AnalyST</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        
        <!-- Favicon -->
        <link rel="shortcut icon" href="' . $wwwroot . '/report/moodleanalyst/pix/favicon.ico" type="image/x-icon" />
        
        <!-- Bootstrap core CSS-->
        <link rel="stylesheet" href="' . $wwwroot . '/report/moodleanalyst/css/bootstrap.min.css" />
        
        <!-- Bootstrap core CSS-->
        <link rel="stylesheet" href="' . $wwwroot . '/report/moodleanalyst/css/ownModifications.css" />
        
        <!-- jQuery core CSS -->
        <link rel="stylesheet" href="' . $wwwroot . '/report/moodleanalyst/css/jquery-ui.min.css" />
    </head>
    
    <body ng-app="overview" style="padding-top:40px">
        <div class="wrapper">
                <div style="padding-top:0px"  class="navbar navbar-inverse navbar-fixed-top">
                    <div class="container-fluid">
                        <div class="navbar-header navbar-right">
                            <button type="button" data-toggle="collapse" data-target="#navbar_sc" class="navbar-toggle collapsed">
                                <span class="sr-only">Toggle nav</span>
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                            </button>
                            <a href="' . $wwwroot . '/report/moodleanalyst/index.php" class="navbar-brand">
                                <span><img alt="Brand" src="' . $wwwroot . '/report/moodleanalyst/pix/favicon.ico">&nbsp;Moodle Analyst Support Tool</span>
                            </a>
                        </div>
                        <div id="navbar_sc" class="collapse navbar-collapse" role="tabpanel">
                            <ul class="nav navbar-nav" role="tablist" id="myTabList">
                                <li>
                                    <a id="tablink_backtomoodle" href="'  .$wwwroot.  '" target="_blank">
                                        <span><img src="' . $wwwroot . '/pix/i/moodle_host.png"></span> {{vocabulary.sitehome}}
                                    </a>
                                </li>
                                <li role="presentation" class="active">
                                    <a id="tablink_home" href="#tabs-1" role="tab" data-toggle="tab">
                                        <span><img src="' . $wwwroot . '/pix/i/publish.png"></span> {{vocabulary.home}}
                                    </a>
                                </li>
                                <li role="presentation">
                                    <a id="tablink_createnewcourse" href="#tabs-2" role="tab" data-toggle="tab">
                                        <span><img src="' . $wwwroot . '/pix/t/add.png"></span> {{vocabulary.addnewcourse}}
                                    </a>
                                </li>
                                
                                <li class="dropdown">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button">
                                        <span><img src="' . $wwwroot . '/pix/i/scales.png"></span> {{vocabulary.statistics}}<b class="caret"></b>
                                    </a>
                                    <ul class="dropdown-menu" role="menu">
                                        
                                        <!-- NOT YET IMPLEMENTED
                                        <li role="presentation"><a id="tablink_newusers" href="#tabs-3" role="tab" data-toggle="tab">{{vocabulary.newusers}}</a></li>
                                        -->
                                        
                                        <li role="presentation"><a id="tablink_inactiveusers" href="#tabs-4" role="tab" data-toggle="tab">{{vocabulary.users}} ({{vocabulary.inactive}})</a></li>
                                        
                                        <!-- CURRENTLY NOT USED DUE TO LONG LOADING TIME FOR LARGE MOODLE INSTANCES
                                        <li role="presentation"><a id="tablink_emptycourses" href="#tabs-5" role="tab" data-toggle="tab">Empty courses</a></li>
                                        -->

                                        <li role="presentation"><a id="tablink_files" href="#tabs-6" role="tab" data-toggle="tab" ng-controller="FilesController" ng-click="getAllFiles();">{{vocabulary.allfiles}}</a></li>
                                        <li role="presentation"><a id="tablink_courseswithactivities" href="#tabs-7" role="tab" data-toggle="tab" ng-controller="CoursesWithActivitiesController" ng-click="getData();">{{vocabulary.activitymodules}}</a></li>
                                        <li role="presentation"><a id="tablink_urls" href="#tabs-urls" role="tab" data-toggle="tab" ng-controller="URLsController" ng-click="getAllURLs();">{{vocabulary.url.modulenameplural}}</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div> <!-- container-fluid -->
                </div> <!-- navbar -->
                
                <div class="tab-content well container-fluid" id="tabs">
                    <div role="tabpanel" class="tab-pane fade active in" id="tabs-1">
                        <overview></overview>
                    </div>
                    <div role="tabpanel" class="tab-pane fade" id="tabs-2">
                        <newcourseform></newcourseform>
                    </div>
                    
                    <!-- NOT YET IMPLEMENTED
                    <div role="tabpanel" class="tab-pane fade" id="tabs-3">
                        <div ng-include="\'' . $wwwroot . '/report/moodleanalyst/php/newusers.tpl.php\'"></div>
                    </div>
                    -->
                    
                    <div role="tabpanel" class="tab-pane fade" id="tabs-4">
                        <div ng-include="\'' . $wwwroot . '/report/moodleanalyst/php/inactiveusers.tpl.php\'"></div>
                    </div>
                    
                    <!-- CURRENTLY NOT USED DUE TO LONG LOADING TIME FOR LARGE MOODLE INSTANCES
                    <div role="tabpanel" class="tab-pane fade" id="tabs-5">
                        <div ng-include="\'' . $wwwroot . '/report/moodleanalyst/php/emptycourses.tpl.php\'"></div>
                    </div>
                    -->

                    <div role="tabpanel" class="tab-pane fade" id="tabs-6">
                        <div ng-include="\'' . $wwwroot . '/report/moodleanalyst/php/files.tpl.php\'"></div>
                    </div>
                    <div role="tabpanel" class="tab-pane fade" id="tabs-7">
                        <div ng-include="\'' . $wwwroot . '/report/moodleanalyst/php/courseswithactivities.tpl.php\'"></div>
                    </div>
                    <div role="tabpanel" class="tab-pane fade" id="tabs-urls">
                        <div ng-include="\'' . $wwwroot . '/report/moodleanalyst/php/urls.tpl.php\'"></div>
                    </div>
                </div> <!-- tab-content -->
                
                <footer class="navbar-inverse navbar-fixed-bottom">
                    <div class="row" style="color:#A4A4A4;">
                        <div class="col-md-4">
                            &copy; All Rights reserved.
                        </div>
                        
                        <div class="col-md-4">
                            <center>
                                <a href="https://github.com/SteffenPegenau/moodle-analyst/issues" target="_blank" style="color:#A4A4A4;">
                                    <img src="' . $wwwroot . '/pix/docs.png"> Submit Issue
                                </a>
                            </center>
                        </div>
                        
                        <div class="col-md-4">
                            <a href="" target="_self" style="color:#A4A4A4;" class="pull-right" id="backtotop">
                                {{vocabulary.backtotop}}
                            </a>
                        </div>
                    </div>
                </footer>
        </div> <!-- wrapper -->
        
        
        <!-- JavaScript -->
        <!-- ================================================== -->
        <!-- Placed at the end of the document so the pages load faster -->

        <!-- jQuery core JS -->
        <script src="' . $wwwroot . '/report/moodleanalyst/js/jquery-1.11.2.js"></script>
        
        <!-- Google Visualization API -->
        <script type="text/javascript" src="'.$googleLibs.'"></script>
        <!-- Include AngularJS -->
        <script src="'.$wwwroot.'/report/moodleanalyst/js/angularlib.js"></script>
        
        
        <!-- Include custom JS -->
        <script id="angularJSloader" src="' . $wwwroot . '/report/moodleanalyst/js/angular.js" wwwroot="' . $wwwroot . '"></script>
        <script id="angularJSloader" src="' . $wwwroot . '/report/moodleanalyst/js/DataTableToCSV.js" wwwroot="' . $wwwroot . '"></script>
        
        <!-- Bootstrap core JS -->
        <script src="' . $wwwroot . '/report/moodleanalyst/js/bootstrap.min.js"></script>
        
        <!-- Custom JS -->
        <script type="text/javascript">
            $(document).ready(function() {
                /*
                $(".navbar-nav a").click(function (e) {
                    e.preventDefault();
                });
                */
                
                $(".navbar-brand").click(function (e) {
                    //e.preventDefault();
                    $("#myTabList a[href=\"#tabs-1\"]").tab("show");
                });
                
                $("#backtotop").click(function (e) {
                    //e.preventDefault();
                    $("html, body").animate({scrollTop: 0}, 800);
                });
            });
        </script>
        
        
    </body>
</html>';
