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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Version details.
 *
 * @package report_moodleanalyst
 * @copyright 2016, Steffen Pegenau
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *         
 */
 
 echo "Hello";
 
 
require_once '../../config.php';

global $CFG, $DB;
require_once $CFG->libdir . '/adminlib.php';

//defined ( 'MOODLE_INTERNAL' ) || die ();

$context = context_system::instance ();
require_capability ( 'report/moodleanalyst:view', $context );

$DATE_FORMAT = "d.m.Y\tH:i:s";

$sql = "SELECT
	ue.id,
	(SELECT name FROM {course_categories} WHERE id = ccat.parent) as semester,
	ccat.name as fb,
	e.courseid,
	c.shortname,
	ue.userid,
	u.firstname,
	u.lastname,
	ue.modifierid,
	(SELECT firstname FROM {user} WHERE id=ue.modifierid) as modifier_firstname,
	(SELECT lastname FROM {user} WHERE id=ue.modifierid) as modifier_lastname,
	ue.timecreated,
	ue.timemodified
FROM
	{user_enrolments} ue,
	{user} u,
	{enrol} e,
	{course} c,
	{course_categories} ccat
WHERE 
	u.id = ue.userid AND
	ue.enrolid = e.id AND
	e.enrol = 'manual' AND
	e.courseid = c.id AND
	c.category = ccat.id
ORDER BY
	ue.timecreated DESC";

$DB->set_debug(true);
$results = $DB->get_records_sql ( $sql, array (), 0, 1000);

$entries = [];
foreach ($results as $id => $e) {
	$values = array();
	$values[] = intval($e->id);
	$values[] = "'" . addslashes($e->semester) . "'";
	$values[] = "'" . addslashes($e->fb) . "'";
	$values[] = intval($e->courseid);
	$values[] = "'" . addslashes($e->shortname) . "'";
	$values[] = intval($e->userid);
	$values[] = "'" . addslashes($e->firstname) . "'";
	$values[] = "'" . addslashes($e->lastname) . "'";
	$values[] = intval($e->modifierid);
	$values[] = "'" . addslashes($e->modifier_firstname) . "'";
	$values[] = "'" . addslashes($e->modifier_lastname) . "'";
	$values[] = "'" . date($DATE_FORMAT, $e->timecreated) . "'";
	$entries[] = "[" . join(',', $values) . "]\n";
}

$table_rows = join(',', $entries);

echo '
		<html>
			<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
			<script type="text/javascript">
		      google.charts.load("current", {"packages":["table", "controls", "corechart"]});
		      google.charts.setOnLoadCallback(drawDashboard);
		
		      function drawDashboard() {
		        var data = new google.visualization.DataTable();
		        data.addColumn("number", "enrolmentid");
		        data.addColumn("string", "semester");
		        data.addColumn("string", "fb");
		        data.addColumn("number", "courseid");
		        data.addColumn("string", "shortname");
		        data.addColumn("number", "userid");
		        data.addColumn("string", "firstname");
		        data.addColumn("string", "semester");
		        data.addColumn("number", "modifierid");
		        data.addColumn("string", "m_firstname");
		        data.addColumn("string", "m_lastname");
		        data.addColumn("string", "enrolment created");
		        data.addRows(['.$table_rows.' ]);
		        		
		        // Create a dashboard.
		        var dashboard = new google.visualization.Dashboard(
		            document.getElementById("dashboard"));
		        		
		        // Create a range slider, passing some options
		        var modifier_filter = new google.visualization.ControlWrapper({
		          "controlType": "CategoryFilter",
		          "containerId": "modifier_filter",
		          "options": {
		            "filterColumnLabel": "modifierid"
		          }
		        });
		
		        var table = new google.visualization.ChartWrapper({
			        chartType: "Table",
			        containerId: "table_div",
			        options: {
			            showRowNumber: false,
			            width: "100%",
			            //page: "enable",
			            //pageSize: 25,
			            allowHtml: true
			                    //sortColumn: 0,
			                    //sortAscending: false
			        },
			        view: {
			            // 0: instance
			            // 1: section name
			            // 2: localised activity type
			            // 3: activity name
			            // 4: mod - moodle internal mod name, for example forum, chat, assign, choice
			            // 5: course module id (cm)
			            // 6: visible (1 || 0)
			            columns: [0,1, 2, 3,4,5,6,7,8,9,10,11]
			        }
			    });
		        //var table = new google.visualization.Table(document.getElementById("table_div"));
		        dashboard.bind(modifier_filter, table);	
		       	//dashboard.bind([modifier_filter], table);
		
		        dashboard.draw(data);
		      }
		    </script>
		<head>
		</head>
		<body>
		    <h1>Die letzten 1000 Kurseintragungen:</h1>
		    <div id="dashboard">
				<div id="modifier_filter"></div>
		    	<div id="table_div"></div>
		    </div>
		</body>
		</html>';