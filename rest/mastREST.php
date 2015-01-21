<?php

//ini_set('display_errors', 'On');
//error_reporting(E_ALL | E_STRICT);

require 'Slim/Slim.php';
require_once '../../../config.php';
require_once '../../../course/lib.php';

require_capability('report/moodleanalyst:view', context_system::instance());

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim ();

$app->map('/allCourses', 'allCourses')->via('GET');
$app->map('/course/:id', 'course')->via('GET');
$app->map('/course/getPersons/:id', 'getPersonsInCourse')->via('GET');
$app->map('/course/getActivities/:id', 'getActivitiesInCourse')->via('GET');

$app->run();

function getActivitiesInCourse($courseid) {
    global $OUTPUT;
    $table = array();
    $table['cols'] = array();
    $table['cols'][] = array('label' => get_string('section'), 'type' => 'number');
    $table['cols'][] = array('label' => get_string('sectionname'), 'type' => 'string');
    $table['cols'][] = array('label' => get_string('activity'), 'type' => 'string');
    $table['cols'][] = array('label' => get_string('name'), 'type' => 'string');
    
    $table['rows'] = array();

    $activities = get_array_of_activities($courseid);
    echo "<pre>" . print_r($activities, true) . "</pre>";

    foreach ($activities as $modid => $activity) {
        $section = $activity->section;
        $sectionname = get_section_name($courseid, $activity->section);
        $icon = '';//"<img src='" . $OUTPUT->pix_url('icon', 'mod_'.$activity->mod) . "'>";
        $activity = $icon . get_string('pluginname', $activity->mod);
        //ERROR IN NEXT LINE
        $activityname = $activity->name;
        $table['rows'][] = ['c' => array(array('v' => $section),array('v' => $sectionname), array('v' => $aktivitaet), array('v' => $activityname))];
    }
    //echo "<pre>" . print_r($table, true) . "</pre>";
    return $table;
}

function getPersonsInCourse($courseid) {
    // Preparing the return table
    $result = array();
    $result['cols'] = array();
    $result['cols'][] = array('label' => 'ID', 'type' => 'number');
    $result['cols'][] = array('label' => get_string('firstname'), 'type' => 'string');
    $result['cols'][] = array('label' => get_string('lastname'), 'type' => 'string');
    $result['cols'][] = array('label' => get_string('email'), 'type' => 'string');
    $result['cols'][] = array('label' => get_string('role'), 'type' => 'string');
    $result['rows'] = array();
    
    $context = context_course::instance($courseid);
    $usedRoles = get_roles_used_in_context($context);
    //printArray($usedRoles);
    foreach ($usedRoles as $roleid => $role) {
        $roleUsers = get_role_users($roleid, $context, false, 'u.id, u.firstname, u.lastname, u.email', null, false);
        if (!empty($roleUsers)) {
            foreach ($roleUsers as $userid => $user) {
                $result['rows'][] = ['c' => array(['v' => $userid], array('v' => $user->firstname), array('v' => $user->lastname), array('v' => $user->email), array('v' => $role->name))];
            }
        }
    }
    
    echo json_encode($result);
    //printArray($result);
}

function course($courseid) {
    $course = get_course($courseid);

    $data = array();
    $data['fullname']['string'] = get_string('fullnamecourse');
    $data['fullname']['v'] = $course->fullname;
    $data['shortname']['string'] = get_string('shortnamecourse');
    $data['shortname']['v'] = $course->shortname;
    $data['visible']['string'] = get_string('visible');
    $data['visible']['v'] = $course->visible;
    $data['idnumber']['string'] = get_string('idnumber');
    $data['idnumber']['v'] = $course->idnumber;
    $result = array();
    $result['data'] = $data;
    //printArray($result);
    echo json_encode($result);
}

function allCourses() {
    global $DB;
    $courses = get_courses("all", "c.sortorder DESC", 'c.id, c.fullname, c.shortname, c.category as parentcategory, c.visible');
    $categories = $DB->get_records('course_categories', null, null, 'id, name, parent, visible');

    // Preparing the return table
    $result = array();
    $result['cols'] = array();
    $result['cols'][] = array('label' => 'ID', 'type' => 'number');
    $result['cols'][] = array('label' => get_string('grandparentcategory', 'report_moodleanalyst'), 'type' => 'string');
    $result['cols'][] = array('label' => get_string('parentcategory', 'report_moodleanalyst'), 'type' => 'string');
    $result['cols'][] = array('label' => get_string('course', 'report_moodleanalyst'), 'type' => 'string');
    $result['rows'] = array();

    foreach ($courses as $courseid => $data) {
        if ($data->parentcategory != 0) {
            $data->parentcategoryname = $categories[$data->parentcategory]->name;
            $data->grandparentcategory = $categories[$data->parentcategory]->parent;
            if ($data->grandparentcategory != 0) {
                $data->grandparentcategoryname = $categories[$data->grandparentcategory]->name;
            } else {
                $data->grandparentcategoryname = "";
            }
        } else {
            $data->parentcategoryname = "";
            $data->grandparentcategory = "";
            $data->grandparentcategoryname = "";
        }
        $coursename = $data->fullname;
        // Filling the return table
        $result['rows'][] = ['c' => array(['v' => $data->id], array('v' => $data->grandparentcategoryname), array('v' => $data->parentcategoryname), array('v' => $coursename))];
    }
    echo json_encode($result);
    //printArray($result);
}

function printArray($array) {
    echo "<pre>" . print_r($array, true) . "</pre>";
}
?>

