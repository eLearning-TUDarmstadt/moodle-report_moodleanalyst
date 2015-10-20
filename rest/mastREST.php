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
//ini_set('display_errors', 'On');
//error_reporting(E_ALL | E_STRICT);
require 'Slim/Slim.php';
require_once '../../../config.php';

GLOBAL $CFG;
require_once $CFG->dirroot . '/course/lib.php';
require_once $CFG->dirroot . '/report/moodleanalyst/rest/lib.php';

/*
$origin = $_SERVER['Origin'];

header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
header('Access-Control-Allow-Methods: POST,GET,DELETE,PUT,OPTIONS');
header('Access-Control-Allow-Credentials: true');
header('Content-type: application/json');
*/
// GZIP Compression for output
if (!ob_start("ob_gzhandler")) {
    ob_start();
}

require_login();
require_capability('report/moodleanalyst:view', context_system::instance());

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim ();
$app->contentType("application/json");

$app->map('/isUserLoggedIn', 'moodleanalyst_isUserLoggedIn')->via('GET');
$app->map('/allCourses', 'moodleanalyst_allCourses')->via('GET');
$app->map('/allUsers', 'moodleanalyst_allUsers')->via('GET');
//$app->map('/allUsersWithLastAccess', 'moodleanalyst_allUsersWithLastAccess')->via('GET');
$app->map('/user/:id', 'moodleanalyst_user')->via('GET');
$app->map('/course/:id', 'moodleanalyst_course')->via('GET');
$app->map('/courses/getEmpty', 'moodleanalyst_emptyCourses')->via('GET');
$app->map('/courses/withNumberOfActivities', 'moodleanalyst_courseswithnoofactivities')->via('GET');
$app->map('/course/getPersons/:id', 'moodleanalyst_getPersonsInCourse')->via('GET');
$app->map('/course/getActivities/:id', 'moodleanalyst_getActivitiesInCourse')->via('GET');
$app->map('/course/new/options', 'moodleanalyst_newCourseOptions')->via('GET');
$app->map('/course/new', 'moodleanalyst_newCourse')->via('POST');
//$app->map('/course/getEnrolmentMethods/:id', 'getCourseEnrolmentMethods')->via('GET');
$app->map('/course/:id/setVisibility/:visibility', 'moodleanalyst_setCourseVisibility')->via('GET');
$app->map('/vocabulary', 'moodleanalyst_getVocabulary')->via('GET');
$app->map('/addUser/:userid/ToCourse/:courseid/withRole/:roleid', 'moodleanalyst_enrolUserToCourse')->via('GET');
$app->map('/files', 'moodleanalyst_getFiles')->via('GET');
$app->map('/url', 'moodleanalyst_getAllURLS')->via('GET');
$app->map('/user', 'moodleanalyst_allUsers_light')->via('GET');

$app->run();

/**
 * if this function is callable, the user is logged in 
 * and has the capability 'report/moodleanalyst:view'
 */
function moodleanalyst_isUserLoggedIn() {
    echo json_encode(true);
}

/**
 * Sets the visibility of a course
 * 
 * @param int $courseid
 * @param bool $visibility
 */
function moodleanalyst_setCourseVisibility($courseid, $visibility) {
    $wasSuccessful = course_change_visibility($courseid, $visibility);
    echo json_encode($wasSuccessful);
}

/**
 * Creates a new course
 * 
 * REQUEST-Params:
 * shortname Course shortname
 * fullname Course fullname
 * category Course parent category
 * password password for self enrolment
 * visible Course visibility 
 */
function moodleanalyst_newCourse() {
    global $DB, $CFG;

    $app = \Slim\Slim::getInstance();

    $data = new stdClass();
    $content = json_decode($app->request->getBody());
    if (!isset($content->shortname)) {
        moodleanalyst_errorAndDie('shortname needs to be set!');
    } else {
        $data->shortname = $content->shortname;
        if ($DB->record_exists('course', array('shortname' => $data->shortname))) {
            moodleanalyst_errorAndDie('shortnametaken');
        }
    }

    if (!isset($content->fullname)) {
        moodleanalyst_errorAndDie('fullname needs to be set!');
    } else {
        $data->fullname = $content->fullname;
    }

    if (!isset($content->category)) {
        moodleanalyst_errorAndDie('category needs to be set!');
    } else {
        $data->category = $content->category;
    }

    if (!isset($content->password)) {
        moodleanalyst_errorAndDie('password needs to be set!');
    }

    if (!isset($content->visible)) {
        //nothing here
    } else {
        $data->visible = $content->visible;
    }

    // Sets a course start date
    require_once $CFG->dirroot . '/lib/coursecatlib.php';
    $category = coursecat::get($data->category);
    $categoryPath = $category->path;
    $parentCategories = explode("/", $categoryPath);

    $possibleStartDate = 0;
    foreach ($parentCategories as $categoryID) {
        $category = coursecat::get($categoryID);
        $name = $category->name;

        $possibleStartDate = moodleanalyst_semesterToCourseStartDate($name);
        if ($possibleStartDate != 0) {
            $data->startdate = $possibleStartDate;
            break;
        }
    }

    $course = create_course($data);

    // Changing password
    $instances = enrol_get_instances($course->id, false);
    $enrolinstance = new stdClass();
    foreach ($instances as $instanceid => $instance) {
        if ($instance->enrol == 'self') {
            $enrolinstance->id = $instanceid;
            break;
        }
    }

    // It seems as a self enrolment is not created by default => create it!
    if (!isset($enrolinstance->id)) {
        global $CFG;
        require_once $CFG->dirroot . '/enrol/self/lib.php';
        $self = new enrol_self_plugin();
        $enrolinstance->id = $self->add_default_instance($course);

        // !!! 0 means enrolment method is active
        $enrolinstance->status = 0;
    }
    $enrolinstance->password = $content->password;
    $DB->update_record('enrol', $enrolinstance);

    echo json_encode(array('course' => $course->id, 'selfenrolinstance' => $enrolinstance->id));
    //echo json_encode(array('course' => $course->id));
}

/**
 * Provides the newCourse-Form with categories and their ids
 */
function moodleanalyst_newCourseOptions() {
    global $DB;
    // collecting all categories, with path and id
    // example: /grandparentcategoryname/parentcategoryname/categoryname
    $categories = $DB->get_records('course_categories', array(), 'sortorder ASC', 'id, name, path');
    foreach ($categories as $id => $category) {
        $path = explode('/', $category->path);
        $string = '';
        foreach ($path as $key => $value) {
            if ($value) {
                $string .= ' // ' . $categories[$value]->name;
            }
        }
        $result['categories'][$id]['id'] = $id;
        $result['categories'][$id]['name'] = $string;
    }
    // end categories
    //printArray($result);
    echo json_encode($result);
}

/**
 * Collects translations and makes it accessible for JavaScript-Operations
 */
function moodleanalyst_getVocabulary() {
    $result = array();
    $result['course'] = get_string('course');
    $result['user'] = get_string('user');
    $result['users'] = get_string('users');
    $result['category'] = get_string('category');
    $result['role'] = get_string('role');
    $result['parentcategory'] = get_string('parentcategory', 'report_moodleanalyst');
    $result['grandparentcategory'] = get_string('grandparentcategory', 'report_moodleanalyst');
    $result['searchcourses'] = get_string('searchcourses');
    $result['enrol'] = get_string('enrol', 'enrol');
    $result['default'] = get_string('default');
    $result['student'] = get_string('defaultcoursestudent');
    $result['participants'] = get_string('participants');
    $result['enrolledusers'] = get_string('enrolledusers', 'enrol');
    $result['enrolmentmethods'] = get_string('enrolmentinstances', 'enrol');
    $result['list'] = get_string('list');
    $result['activity'] = get_string('activity');
    $result['activities'] = get_string('activities');
    $result['name'] = get_string('name');
    $result['section'] = get_string('section');
    $result['password'] = get_string('password');
    $result['sitehome'] = get_string('sitehome');
    $result['newcourse'] = get_string('newcourse');
    $result['statistics'] = get_string('statistics');
    $result['show'] = get_string('show');
    $result['view'] = get_string('viewmore');
    $result['hide'] = get_string('hide');
    $result['editsettings'] = get_string('editsettings');
    $result['newusers'] = get_string('newusers');
    $result['personal'] = get_string('personal');
    $result['courseinfo'] = get_string('courseinfo');
    $result['backtotop'] = get_string('backto', 'moodle', get_string('top'));
    $result['edit'] = get_string('edit');
    $result['delete'] = get_string('delete');
    $result['visible'] = get_string('visible');
    $result['downloadfile'] = get_string('downloadfile');
    $result['reset'] = get_string('reset');
    $result['refresh'] = get_string('refresh');
    $result['inactive'] = get_string('inactive');
    $result['fullname'] = get_string('fullnamecourse');
    $result['shortname'] = get_string('shortnamecourse');
    $result['newcourse'] = get_string('newcourse');
    $result['createnewcourse'] = get_string('createnewcourse');
    $result['category'] = get_string('category');
    $result['shortnametaken'] = get_string('shortnametaken');
    $result['selfenrolment'] = get_string('pluginname', 'enrol_self');
    $result['password'] = get_string('password', 'enrol_self');
    $result['nopassword'] = get_string('nopassword', 'enrol_self');
    $result['fullnameuser'] = get_string('fullnameuser');
    $result['allfiles'] = get_string('allfiles');
    $result['files'] = get_string('files');
    $result['home'] = get_string('home');
    $result['search'] = get_string('search');
    $result['hidesection'] = get_string('hidesection', 'moodle', '');
    $result['choosedots'] = get_string('choosedots');
    $result['addnewcourse'] = get_string('addnewcourse');
    $result['coursehidden'] = get_string('coursehidden');
    $result['activitymodules'] = get_string('activitymodules');
    $result['total'] = get_string('total');
    $result['loginas'] = get_string('loginas');
    $result['url']['modulenameplural'] = get_string('modulenameplural', 'mod_url');

    //echo "<pre>" . print_r($result, true) . "</pre>";
    echo json_encode($result);
}

/**
 * Returns the enrolment methods of the given course
 * 
 * @param int $courseid 
 * @return array with enrolment methods
 */
function moodleanalyst_getCourseEnrolmentMethods($courseid) {
    global $DB, $PAGE;
    $PAGE->set_context(context_system::instance());
    $instances = enrol_get_instances($courseid, false);
    $result = array();

    foreach ($instances as $instanceid => $instance) {
        $enrol = enrol_get_plugin($instance->enrol);

        $array['id'] = $instance->id;
        $array['enrol'] = $instance->enrol;
        if (!enrol_is_enabled($instance->enrol) || $instance->status == ENROL_INSTANCE_DISABLED) {
            $array['visible'] = 0;
        } else {
            $array['visible'] = 1;
        }
        $array['sortorder'] = $instance->name;
        $array['password'] = $instance->password;
        $array['name'] = $enrol->get_instance_name($instance);
        $array['number'] = $DB->count_records('user_enrolments', array('enrolid' => $instance->id));
        $result[$instanceid] = $array;
    }
    //echo json_encode($result);
    return $result;
}

/**
 * Returns a JSON with all activities in the given course
 * 
 * Formated in Google Charts Style
 * 
 * @param int $courseid
 */
function moodleanalyst_getActivitiesInCourse($courseid) {
    global $OUTPUT;
    $table = array();
    $table['cols'] = array();
    $table['cols'][] = array('label' => 'id', 'type' => 'number');
    //$table['cols'][] = array('label' => get_string('section'), 'type' => 'number');
    $table['cols'][] = array('label' => get_string('sectionname'), 'type' => 'string');
    $table['cols'][] = array('label' => get_string('activity'), 'type' => 'string');
    $table['cols'][] = array('label' => get_string('name'), 'type' => 'string');
    $table['cols'][] = array('label' => 'mod', 'type' => 'string');
    $table['cols'][] = array('label' => 'cm', 'type' => 'string');
    $table['cols'][] = array('label' => get_string('visible'), 'type' => 'boolean');


    $table['rows'] = array();

    $activities = get_array_of_activities($courseid);
    //echo "<pre>" . print_r($activities, true) . "</pre>";

    foreach ($activities as $modid => $activity) {
        if ($activity->visible) {
            $class = "";
        } else {
            $class = "dimmed";
        }
        $spanBegin = ''; //"<span class='" . $class . "'>";
        $spanEnd = ''; //"</span>";
        $activityname = $spanBegin . $activity->name . $spanEnd;
        $sectionname = $spanBegin . get_section_name($courseid, $activity->section) . $spanEnd;
        $icon = ''; //"<img src='" . $OUTPUT->pix_url('icon', 'mod_'.$activity->mod) . "'>";
        $activityType = $spanBegin . $icon . get_string('pluginname', $activity->mod) . $spanEnd;
        //$table['rows'][] = ['c' => array('v' => $sectionname), array('v' => $activityType), array('v' => $activityname)];
        $visibility = ($activity->visible) ? true : false;
        $table['rows'][] = ['c' => array(
                ['v' => $activity->id],
                ['v' => $sectionname],
                ['v' => $activityType],
                ['v' => $activityname],
                ['v' => $activity->mod],
                ['v' => $activity->cm],
                ['v' => $visibility]
        )];
    }
    //printArray($table);
    echo json_encode($table);
}

/**
 * Returns the persons in course as JSON in Google Chart format
 * 
 * @param int $courseid
 */
function moodleanalyst_getPersonsInCourse($courseid) {
    // Preparing the return table
    $result = array();
    $result['cols'] = array();
    $result['cols'][] = array('label' => 'ID', 'type' => 'number');
    $result['cols'][] = array('label' => get_string('username'), 'type' => 'string');
    $result['cols'][] = array('label' => get_string('firstname'), 'type' => 'string');
    $result['cols'][] = array('label' => get_string('lastname'), 'type' => 'string');
    //$result['cols'][] = array('label' => get_string('email'), 'type' => 'string');
    $result['cols'][] = array('label' => get_string('role'), 'type' => 'string');
    $result['cols'][] = array('label' => get_string('fullname'), 'type' => 'string');
    $result['rows'] = array();

    $context = context_course::instance($courseid);
    $usedRoles = get_roles_used_in_context($context);
    //printArray($usedRoles);
    foreach ($usedRoles as $roleid => $role) {
        $roleUsers = get_role_users($roleid, $context, false, 'u.id, u.username, u.firstname, u.lastname, u.email', null, false);
        if (!empty($roleUsers)) {
            foreach ($roleUsers as $userid => $user) {
                // removed array('v' => $user->email),
                $result['rows'][] = ['c' => array(['v' => $userid], array('v' => $user->username), array('v' => $user->firstname), array('v' => $user->lastname), array('v' => $role->name), array('v' => $user->firstname . ' ' . $user->lastname))];
            }
        }
    }

    echo json_encode($result);
    //printArray($result);
}

/**
 * Enrols an user to a course with a certain role
 * 
 * @param int $userid
 * @param int $courseid
 * @param int $roleid if invalid, the student role is chosen
 */
function moodleanalyst_enrolUserToCourse($userid, $courseid, $roleid) {
    global $DB, $CFG;
    require_once '../../../enrol/manual/externallib.php';

    if (!isset($roleid) || !is_numeric($roleid) || $roleid < 0) {
        echo "Invalid role: $role. Add as student.";
        $roleid = $DB->get_record('role', array('shortname' => 'student'), $fields = 'id', IGNORE_MISSING);
        $roleid = $roleid->id;
    }

    $enrolment = array('courseid' => $courseid, 'userid' => $userid, 'roleid' => $roleid);
    $enrolments[] = $enrolment;
    enrol_manual_external::enrol_users($enrolments);
}

/**
 * Returns information about an user as JSON in Google charts format
 * 
 * @param int $userid
 */
function moodleanalyst_user($userid) {
    require_once '../../../user/lib.php';
    require_once '../../../lib/coursecatlib.php';
    $user = user_get_users_by_id(array($userid));
    $user = $user[$userid];

    $courses = enrol_get_all_users_courses($userid);

    $courses_enrolled = array();
    $courses_enrolled['cols'] = array();
    $courses_enrolled['cols'][] = array('label' => 'ID', 'type' => 'number');
    $courses_enrolled['cols'][] = array('label' => get_string('grandparentcategory', 'report_moodleanalyst'), 'type' => 'string');
    $courses_enrolled['cols'][] = array('label' => get_string('parentcategory', 'report_moodleanalyst'), 'type' => 'string');
    $courses_enrolled['cols'][] = array('label' => get_string('course', 'report_moodleanalyst'), 'type' => 'string');
    $courses_enrolled['cols'][] = array('label' => get_string('roles'), 'type' => 'string');
    $courses_enrolled['cols'][] = array('label' => get_string('visible'), 'type' => 'boolean');
    $courses_enrolled['rows'] = array();

    foreach ($courses as $courseid => $course) {
        $categoryid = $course->category;
        $category = coursecat::get($categoryid);
        $course->categoryname = $category->get_formatted_name();
        $parents = $category->get_parents();
        if (!empty($parents)) {
            $parent = $parents[0];
            $course->parentcategory = $parent;
            $parentcategory = coursecat::get($parent);
            $course->parentcategoryname = $parentcategory->get_formatted_name();
        } else {
            $course->parentcategory = null;
            $course->parentcategoryname = null;
        }

        // Get roles
        $context = context_course::instance($courseid);
        $course->roles = get_user_roles($context, $userid);

        foreach ($course->roles as $roleid => $role) {
            $visible = ($course->visible) ? true : false;
            $courses_enrolled['rows'][] = [
                'c' => array(
                    ['v' => $course->id],
                    array('v' => $course->parentcategoryname),
                    array('v' => $course->categoryname),
                    array('v' => $course->fullname),
                    array('v' => role_get_name($role)),
                    array('v' => $visible)
                )
            ];
        }
    }
    /*
     * Possible fields:    
     * 
     * [id] => 20831
      [auth] => cas
      [confirmed] => 1
      [policyagreed] => 1
      [deleted] => 0
      [suspended] => 0
      [mnethostid] => 1
      [username] =>
      [password] => not cached
      [idnumber] =>
      [firstname] =>
      [lastname] =>
      [email] =>
      [emailstop] => 0
      [icq] =>
      [skype] =>
      [yahoo] =>
      [aim] =>
      [msn] =>
      [phone1] =>
      [phone2] =>
      [institution] =>
      [department] =>
      [address] =>
      [city] =>
      [country] =>
      [lang] => en
      [theme] =>
      [timezone] => 99
      [firstaccess] => 1377083840
      [lastaccess] => 1418644723
      [lastlogin] => 1418634762
      [currentlogin] => 1418644723
      [lastip] =>
      [secret] =>
      [picture] => 0
      [url] =>
      [description] =>
      [descriptionformat] => 1
      [mailformat] => 1
      [maildigest] => 0
      [maildisplay] => 2
      [autosubscribe] => 1
      [trackforums] => 0
      [timecreated] => 1377083840
      [timemodified] => 1392029898
      [trustbitmask] => 0
      [imagealt] =>
      [lastnamephonetic] =>
      [firstnamephonetic] =>
      [middlename] =>
      [alternatename] =>
      [calendartype] => gregorian
     */

    $ret = array();
    $ret['id']['string'] = "ID";
    $ret['id']['v'] = $user->id;
    $ret['auth']['string'] = get_string('authentication');
    $ret['auth']['v'] = $user->auth;
    $ret['username']['string'] = get_string('username');
    $ret['username']['v'] = $user->username;
    $ret['idnumber']['string'] = get_string('idnumber');
    $ret['idnumber']['v'] = $user->idnumber;
    $ret['firstname']['string'] = get_string('firstname');
    $ret['firstname']['v'] = $user->firstname;
    $ret['lastname']['string'] = get_string('lastname');
    $ret['lastname']['v'] = $user->lastname;
    $ret['email']['string'] = get_string('email');
    $ret['email']['v'] = $user->email;
    $ret['firstaccess']['string'] = get_string('firstaccess');
    $ret['firstaccess']['v'] = userdate($user->firstaccess);
    $ret['lastaccess']['string'] = get_string('lastaccess');
    $ret['lastaccess']['v'] = userdate($user->lastaccess);
    $ret['lastlogin']['string'] = get_string('lastlogin');
    $ret['lastlogin']['v'] = userdate($user->lastlogin);
    $ret['lastip']['string'] = get_string('lastip');
    $ret['lastip']['v'] = $user->lastip;
    $ret['lang']['string'] = get_string('language');
    $ret['lang']['v'] = $user->lang;

    // Session key for login as...
    global $USER;
    $ret['sessionkey']['string'] = 'sessionkey';
    $ret['sessionkey']['v'] = $USER->sesskey;
    
    $ret['courses'] = $courses_enrolled;
    
    
    //printArray($ret);
    echo json_encode($ret);
}

/**
 * Returns a JSON-List of empty courses
 * 
 * FUNCTION TOO SLOW => currently deactivated
 */
function moodleanalyst_emptyCourses() {
    global $DB;
    //$courses = get_courses("all", "c.sortorder DESC", 'c.*, c.category as parentcategory');
    $courses = get_courses("all", "c.sortorder DESC", 'c.id, c.fullname, c.shortname, c.category as parentcategory, c.visible');
    $categories = $DB->get_records('course_categories', null, null, 'id, name, parent, visible');
    // Preparing the return table
    $result = array();
    $result['cols'] = array();
    $result['cols'][] = array('label' => 'ID', 'type' => 'number');
    $result['cols'][] = array('label' => get_string('grandparentcategory', 'report_moodleanalyst'), 'type' => 'string');
    $result['cols'][] = array('label' => get_string('parentcategory', 'report_moodleanalyst'), 'type' => 'string');
    $result['cols'][] = array('label' => get_string('course', 'report_moodleanalyst'), 'type' => 'string');
    $result['cols'][] = array('label' => get_string('visible'), 'type' => 'boolean');

    $result['rows'] = array();

    foreach ($courses as $courseid => $data) {
        $activities = get_array_of_activities($courseid);
        if (count($activities) <= 0) {
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
            $data->visible = ($data->visible) ? true : false;
            $result['rows'][] = ['c' => array(['v' => $data->id], array('v' => $data->grandparentcategoryname), array('v' => $data->parentcategoryname), array('v' => $coursename), array('v' => $data->visible))];
        }
    }
    echo json_encode($result);
    //printArray($result);
}

/**
 * Returns information about a course as JSON in Google charts format
 * 
 * @param int $courseid
 */
function moodleanalyst_course($courseid) {
    global $CFG, $PAGE;
    require_once $CFG->dirroot . '/lib/coursecatlib.php';
    require_login();
    $course = get_course($courseid);

    $data = array();
    $data['id']['string'] = get_string('course');
    $data['id']['v'] = $courseid;
    $data['fullname']['string'] = get_string('fullnamecourse');
    $data['fullname']['v'] = $course->fullname;
    $data['shortname']['string'] = get_string('shortnamecourse');
    $data['shortname']['v'] = $course->shortname;
    $data['visible']['string'] = get_string('visible');
    $data['visible']['v'] = $course->visible;
    $data['idnumber']['string'] = get_string('idnumber');
    $data['idnumber']['v'] = $course->idnumber;

    $data['category']['string'] = get_string('coursecategory');
    $category = coursecat::get($course->category);
    $parents = $category->get_parents();

    $breadcrumb = "// " . coursecat::get($course->category)->name . " ";
    foreach ($parents as $key => $id) {
        $breadcrumb = "// " . coursecat::get($id)->name . " " . $breadcrumb;
    }
    $data['category']['v'] = $breadcrumb;

    $context = context_course::instance($courseid);
    $data['roles']['string'] = get_string('roles');
    $data['roles']['v'] = role_get_names($context);
    $data['personsInCourse'] = count_enrolled_users($context);
    $data['enrolmentmethods'] = moodleanalyst_getCourseEnrolmentMethods($courseid);


    // Gets roles used in course

    $usedRoles = get_roles_used_in_context($context);
    //printArray($usedRoles);
    foreach ($usedRoles as $roleid => $role) {
        $count = count_role_users($roleid, $context);
        if ($count != 0) {
            $data['rolesInCourse'][$roleid]['id'] = $role->id;
            $data['rolesInCourse'][$roleid]['name'] = $role->name;
            $data['rolesInCourse'][$roleid]['number'] = $count;
            $data['rolesInCourse'][$roleid]['sortorder'] = $role->sortorder;
        }
    }

    // Gets assignable roles in course
    require_once $CFG->dirroot . '/enrol/locallib.php';
    $manager = new course_enrolment_manager($PAGE, $course);
    $usedRoles = $manager->get_assignable_roles();

    //$usedRoles = get_roles_used_in_context($context);
    //printArray($usedRoles);
    foreach ($usedRoles as $roleid => $rolename) {
        $data['assignableRoles'][$roleid]['id'] = $roleid;
        $data['assignableRoles'][$roleid]['name'] = $rolename;
    }
    $result = array();
    $result['data'] = $data;
    //printArray($result);
    echo json_encode($result);
}

/**
 * Returns a list of all users as JSON in Google Charts format
 * 
 * Load only data that is really necessary 
 */
function moodleanalyst_allUsers_light() {
    /*
     * @param bool $get If false then only a count of the records is returned
     * @param string $search A simple string to search for
     * @param bool $confirmed A switch to allow/disallow unconfirmed users
     * @param array $exceptions A list of IDs to ignore, eg 2,4,5,8,9,10
     * @param string $sort A SQL snippet for the sorting criteria to use
     * @param string $firstinitial Users whose first name starts with $firstinitial
     * @param string $lastinitial Users whose last name starts with $lastinitial
     * @param string $page The page or records to return
     * @param string $recordsperpage The number of records to return per page
     * @param string $fields A comma separated list of fields to be returned from the chosen table.
     * @return array|int|bool  {@link $USER} records unless get is false in which case the integer count of the records found is returned.
     *                        False is returned if an error is encountered.
     */
    $get = true;
    $search = '';
    $confirmed = false;
    $exceptions = null;
    $sort = 'lastname ASC';
    $firstinitial = '';
    $lastinitial = '';
    $page = '';
    $recordsperpage = '100000000';
    $fields = 'id, username, firstname, lastname, email';
    $users = get_users($get, $search, $confirmed, $exceptions, $sort, $firstinitial, $lastinitial, $page, $recordsperpage, $fields);
    // Preparing the return table
    $result = array();
    $result['cols'] = array();
    $result['cols'][] = array('label' => 'ID', 'type' => 'number');
    $result['cols'][] = array('label' => get_string('username'), 'type' => 'string');
    $result['cols'][] = array('label' => get_string('firstname'), 'type' => 'string');
    $result['cols'][] = array('label' => get_string('lastname'), 'type' => 'string');
    $result['cols'][] = array('label' => get_string('email'), 'type' => 'string');
    $result['cols'][] = array('label' => 'All', 'type' => 'string');

    $result['rows'] = array();

    foreach ($users as $userid => $user) {
        $result['rows'][] = [
            'c' => array(
                ['v' => $user->id],
                array('v' => $user->username),
                array('v' => $user->firstname),
                array('v' => $user->lastname),
                array('v' => $user->email),
                array('v' => $user->username . ' mdl '. $user->firstname . ' ' . $user->lastname . ' ' .$user->email)
            )
        ];
    }
    //printArray($result);
    echo json_encode($result);
}

/**
 * Returns a list of all users as JSON in Google Charts format
 */
function moodleanalyst_allUsers() {
    /*
     * @param bool $get If false then only a count of the records is returned
     * @param string $search A simple string to search for
     * @param bool $confirmed A switch to allow/disallow unconfirmed users
     * @param array $exceptions A list of IDs to ignore, eg 2,4,5,8,9,10
     * @param string $sort A SQL snippet for the sorting criteria to use
     * @param string $firstinitial Users whose first name starts with $firstinitial
     * @param string $lastinitial Users whose last name starts with $lastinitial
     * @param string $page The page or records to return
     * @param string $recordsperpage The number of records to return per page
     * @param string $fields A comma separated list of fields to be returned from the chosen table.
     * @return array|int|bool  {@link $USER} records unless get is false in which case the integer count of the records found is returned.
     *                        False is returned if an error is encountered.
     */
    $get = true;
    $search = '';
    $confirmed = false;
    $exceptions = null;
    $sort = 'lastname ASC';
    $firstinitial = '';
    $lastinitial = '';
    $page = '';
    $recordsperpage = '100000000';
    $fields = 'id, username, firstname, lastname, email, lastaccess';
    $users = get_users($get, $search, $confirmed, $exceptions, $sort, $firstinitial, $lastinitial, $page, $recordsperpage, $fields);
    // Preparing the return table
    $result = array();
    $result['cols'] = array();
    $result['cols'][] = array('label' => 'ID', 'type' => 'number');
    $result['cols'][] = array('label' => get_string('username'), 'type' => 'string');
    $result['cols'][] = array('label' => get_string('firstname'), 'type' => 'string');
    $result['cols'][] = array('label' => get_string('lastname'), 'type' => 'string');
    $result['cols'][] = array('label' => get_string('email'), 'type' => 'string');
    $result['cols'][] = array('label' => get_string('fullname'), 'type' => 'string');
    $result['cols'][] = array('label' => get_string('lastaccess'), 'type' => 'date');
    $result['cols'][] = array('label' => get_string('days'), 'type' => 'number');

    $result['rows'] = array();

    foreach ($users as $userid => $user) {
        $result['rows'][] = [
            'c' => array(
                ['v' => $user->id],
                array('v' => $user->username),
                array('v' => $user->firstname),
                array('v' => $user->lastname),
                array('v' => $user->email),
                array('v' => $user->firstname . ' ' . $user->lastname),
                array('v' => createDateForJavaScript($user->lastaccess)),
                // Days since last access 
                array('v' => round((time() - $user->lastaccess) / (60 * 60 * 24), 0))
            )
        ];
    }
    //printArray($result);
    echo json_encode($result);
}

/**
 * Returns a list of all courses as JSON in Google Charts format
 */
function moodleanalyst_allCourses() {
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
    $result['cols'][] = array('label' => get_string('visible'), 'type' => 'boolean');
    $result['cols'][] = array('label' => 'ID' . ' ' . get_string('course', 'report_moodleanalyst'), 'type' => 'string');

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
        $data->visible = ($data->visible) ? true : false;
        $result['rows'][] = ['c' => array(['v' => $data->id], array('v' => $data->grandparentcategoryname), array('v' => $data->parentcategoryname), array('v' => $coursename), array('v' => $data->visible), array('v' => ($data->id . ' ' . $coursename)))];
    }
    echo json_encode($result);
    //printArray($result);
}

/**
 * Helper function that displays a variable preformated for debugging
 * 
 * @param variable $var variable to be displayed
 */
function moodleanalyst_printArray($var) {
    echo "<pre>" . print_r($var, true) . "</pre>";
}

/**
 * Die with an error message
 * 
 * @param String $msg
 */
function moodleanalyst_errorAndDie($msg) {
    echo json_encode(array('error' => $msg));
    die();
}

/**
 * Converts a term into a course start date
 * 
 * WiSe 2010/11 => 01.10.2010 (as Unix-Timestamp)
 * SoSe 2011 => 01.04.2011 (as Unix-Timestamp)
 *  
 * @param String $semester for example "WiSe 2010/11" or "SoSe 2011"
 * @return int $timestamp UNIX-Timestamp, 0 if no reasonable param was given
 */
function moodleanalyst_semesterToCourseStartDate($semester = "") {
    $hour = 6;
    $minute = 0;
    $second = 0;
    $month = 0;
    $day = 1;
    $year = 0;
    if ($pos = strpos($semester, "WiSe 20") === 0) {
        // Wintersemester
        $month = 10;
        $year = (int) substr($semester, 5, 4);
    } elseif ($pos = strpos($semester, "SoSe 20") === 0) {
        // Sommersemester
        $month = 4;
        $year = (int) substr($semester, 5, 4);
    }

    if ($year != 0) {
        return mktime($hour, $minute, $second, $month, $day, $year);
    } else {
        return 0;
    }
}

/**
 * Gets all files on moodle site
 */
function moodleanalyst_getFiles() {
    global $CFG, $DB, $PAGE;

    // Getting all contexts in a single request
    $sql = "SELECT * FROM {context} WHERE contextlevel=" . CONTEXT_MODULE;
    $contexts = $DB->get_records_sql($sql);

    $cmid2contextid = array();
    foreach ($contexts as $id => $context) {
        $cmid2contextid[$context->instanceid] = $id;
    }

    // Getting all folders
    $sql = "SELECT id, course, name FROM {folder}";
    $folders = $DB->get_records_sql($sql);

    // Getting all resources
    $sql = "SELECT id, course, name FROM {resource}";
    $resources = $DB->get_records_sql($sql);

    //$sql = "SELECT id, contextid, component, filearea, filepath, filename, filesize, mimetype FROM {files} WHERE filesize != 0 AND component IN ('course', 'mod_resource', 'mod_folder')";
    $sql = "SELECT id, filename, filesize, mimetype, contextid FROM {files} WHERE filesize != 0";
    $files = $DB->get_records_sql($sql);

    $contextid2file = array();
    foreach ($files as $id => $data) {
        //moodleanalyst_printArray($data);
        $contextid2file[$data->contextid] = $data;
    }

    $sql = "SELECT id, course, instance FROM {course_modules} WHERE module IN (SELECT id FROM {modules} WHERE name IN ('resource'))";
    $mod_resources = $DB->get_records_sql($sql);


    $result = array();

    // Add all resources to result array
    foreach ($mod_resources as $modid => $cm) {
        $tmp = array();

        $tmp["course"] = $cm->course;
        $tmp["name"] = $resources[$cm->instance]->name;

        $contextid = $cmid2contextid[$modid];
        if (isset($contextid2file[$contextid])) {
            $file = $contextid2file[$contextid];
            $tmp["filename"] = $file->filename;
            $tmp["filesize"] = $file->filesize;
            $tmp["mimetype"] = $file->mimetype;

            // Add to result
            $result[] = $tmp;
        } else {
            unset($mod_resources[$modid]);
        }
    }

    $sql = "SELECT id, course, instance FROM {course_modules} WHERE module IN (SELECT id FROM {modules} WHERE name IN ('folder'))";
    $mod_folders = $DB->get_records_sql($sql);

    // Add folders to result array
    foreach ($mod_folders as $modid => $cm) {
        $contextid = $cmid2contextid[$modid];

        $filesInFolder = $DB->get_records_sql("SELECT id, filename, filesize, mimetype, contextid FROM {files} WHERE filesize != 0 AND contextid=" . $contextid);
        foreach ($filesInFolder as $fileid => $file) {
            $tmp = array();
            $tmp["course"] = $cm->course;
            $tmp["name"] = $file->filename;
            $tmp["filename"] = $file->filename;
            $tmp["filesize"] = $file->filesize;
            $tmp["mimetype"] = $file->mimetype;
            $result[] = $tmp;
        }
    }

    $table['cols'] = array();
    $table['cols'][] = array('label' => get_string('course'), 'type' => 'number');
    $table['cols'][] = array('label' => get_string('name'), 'type' => 'string');
    $table['cols'][] = array('label' => 'filename', 'type' => 'string');
    $table['cols'][] = array('label' => 'filesize (KB)', 'type' => 'number');
    $table['cols'][] = array('label' => 'mimetype', 'type' => 'string');

    $table['rows'] = array();

    foreach ($result as $key => $value) {
        $filesize = round($value["filesize"] / 1024, 2);
        $table['rows'][] = ['c' => array(
                array('v' => $value["course"]),
                array('v' => $value["name"]),
                array('v' => $value["filename"]),
                array('v' => $filesize),
                array('v' => $value["mimetype"]))];
    }

    echo json_encode($table);


    /*
      // Gets the moodle internal id for mods of type 'resource'
      $mods = $DB->get_records('modules', array('name' => 'resource'));
      $mod_resource = reset($mods);
      $id_mod_resource = $mod_resource->id;

      // Gets all cm's of resource
      $cms = $DB->get_records('course_modules', array('module' => $id_mod_resource));
      $resources = $DB->get_records('resource');

      $file_browser = new file_browser();
      $file_infos = $file_browser->get_file_info();

      $res = $resources;

      require_once $CFG->dirroot . '/mod/resource/locallib.php';
      foreach ($cms as $cmid => $cm) {
      $details = resource_get_optional_details($resources[$cm->instance], $cm);
      moodleanalyst_printArray($details);
      }

      /*
      foreach ($cms as $cmid => $cm) {
      //$context = context_module::instance($cm->id);
      $context = context_course::instance($cm->course);
      $file_info = $file_browser->get_file_info($context);

      $array = [
      $cm->course => $file_info->count_non_empty_children(),
      'mimetype' => $file_info->get_mimetype(),
      'filesize' => $file_info->get_filesize()
      ];
      moodleanalyst_printArray($array);
      //moodleanalyst_printArray($file_info->get_filesize());
      }

      $resources = $DB->get_records('resource');
     */

    //moodleanalyst_printArray($res);
}

/**
 * Returns a table of all courses with the number of activities in each course
 * as JSON for a google charts table
 */
function moodleanalyst_courseswithnoofactivities() {
    global $DB;
    $courses = get_courses("all", "c.sortorder DESC", 'c.id, c.fullname, c.shortname, c.category as parentcategory, c.visible');
    $categories = $DB->get_records('course_categories', null, null, 'id, name, parent, visible');

    $activities = $DB->get_records_sql("SELECT course, COUNT(course) as countedactivities FROM {course_modules} GROUP BY course");

    // Preparing the return table
    $result = array();
    $result['cols'] = array();
    $result['cols'][] = array('label' => 'ID', 'type' => 'number');
    $result['cols'][] = array('label' => get_string('grandparentcategory', 'report_moodleanalyst'), 'type' => 'string');
    $result['cols'][] = array('label' => get_string('parentcategory', 'report_moodleanalyst'), 'type' => 'string');
    $result['cols'][] = array('label' => get_string('course', 'report_moodleanalyst'), 'type' => 'string');
    $result['cols'][] = array('label' => get_string('activities'), 'type' => 'number');
    $result['cols'][] = array('label' => get_string('visible'), 'type' => 'boolean');

    $result['rows'] = array();

    $yes = get_string('yes');
    $no = get_String('no');

    foreach ($courses as $courseid => $data) {
        if (isset($activities[$courseid])) {
            $data->numberOfActivities = $activities[$courseid]->countedactivities;
        } else {
            $data->numberOfActivities = 0;
        }

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
        $data->visible = ($data->visible) ? true : false;
        $data->visibleReadable = ($data->visible) ? $yes : $no;

        $number = (int) $data->numberOfActivities;
        $result['rows'][] = ['c' => array(['v' => $data->id], array('v' => $data->grandparentcategoryname), array('v' => $data->parentcategoryname), array('v' => $coursename), array('v' => $number), array('v' => $data->visible, 'f' => $data->visibleReadable))];
    }
    echo json_encode($result);
}

/**
 * Returns a table of all urls
 * as JSON for a google charts table
 */
function moodleanalyst_getAllURLS() {
    global $DB;
    $urls = $DB->get_records_sql("SELECT
	{url}.id,
	{url}.course,
	{course}.shortname AS coursename,
        {course}.category AS parentcategoryid,
	{course_categories}.name AS parentcategory,
        {course_categories}.parent AS grandparentcategoryid,
	{url}.name,
        {url}.externalurl
FROM
	{url},
	{course},
	{course_categories} 
WHERE 
	{course}.id = {url}.course AND
	{course_categories}.id = {course}.category
    ");
    $categories = $DB->get_records('course_categories', null, null, 'id, name, parent');

    // Preparing the return table
    $result = array();
    $result['cols'] = array();
    $result['cols'][] = array('label' => 'ID', 'type' => 'number');
    $result['cols'][] = array('label' => get_string("course"), 'type' => 'number');
    $result['cols'][] = array('label' => get_string('grandparentcategory', 'report_moodleanalyst'), 'type' => 'string');
    $result['cols'][] = array('label' => get_string('parentcategory', 'report_moodleanalyst'), 'type' => 'string');
    $result['cols'][] = array('label' => get_string('shortnamecourse'), 'type' => 'string');
    $result['cols'][] = array('label' => get_string('name'), 'type' => 'string');
    $result['cols'][] = array('label' => get_string('externalurl', 'mod_url'), 'type' => 'string');

    $result['rows'] = array();

    foreach ($urls as $urlid => $url) {

        if ($url->grandparentcategoryid != 0) {
            $url->grandparentcategoryname = $categories[$url->grandparentcategoryid]->name;
        } else {
            $url->grandparentcategoryname = "";
        }

        //moodleanalyst_printArray($url);
        $result['rows'][] = ['c' => array(
                        ['v' => $url->id],
                        array('v' => $url->course),
                        array('v' => $url->grandparentcategoryname),
                        array('v' => $url->parentcategory),
                        array('v' => $url->coursename),
                        array('v' => $url->name),
                        array('v' => $url->externalurl)
        )];
    }
    echo json_encode($result);


    //moodleanalyst_printArray($result);
}
?>

