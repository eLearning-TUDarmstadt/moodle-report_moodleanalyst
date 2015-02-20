<?php

//ini_set('display_errors', 'On');
//error_reporting(E_ALL | E_STRICT);

require 'Slim/Slim.php';
require_once '../../../config.php';
require_once '../../../course/lib.php';

// GZIP Compression for output
if (!ob_start("ob_gzhandler"))
    ob_start();

//require_login();
require_capability('report/moodleanalyst:view', context_system::instance());

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim ();

$app->map('/isUserLoggedIn', 'isUserLoggedIn')->via('GET');
$app->map('/allCourses', 'allCourses')->via('GET');
$app->map('/allUsers', 'allUsers')->via('GET');
$app->map('/user/:id', 'user')->via('GET');
$app->map('/course/:id', 'course')->via('GET');
$app->map('/course/getPersons/:id', 'getPersonsInCourse')->via('GET');
$app->map('/course/getActivities/:id', 'getActivitiesInCourse')->via('GET');
$app->map('/course/new/options', 'newCourseOptions')->via('GET');
$app->map('/course/new', 'newCourse')->via('POST');
//$app->map('/course/getEnrolmentMethods/:id', 'getCourseEnrolmentMethods')->via('GET');
$app->map('/course/:id/setVisibility/:visibility', 'setCourseVisibility')->via('GET');
$app->map('/vocabulary', 'getVocabulary')->via('GET');
$app->map('/addUser/:userid/ToCourse/:courseid/withRole/:roleid', 'enrolUserToCourse')->via('GET');

$app->run();

function isUserLoggedIn() {
    echo json_encode(true);
}

function setCourseVisibility($courseid, $visibility) {
    echo json_encode(course_change_visibility($courseid, $visibility));
}

function newCourse() {
    global $DB;

    $app = \Slim\Slim::getInstance();

    $data = new stdClass();
    $content = json_decode($app->request->getBody());
    if (!isset($content->shortname)) {
        errorAndDie('shortname needs to be set!');
    } else {
        $data->shortname = $content->shortname;
        if ($DB->record_exists('course', array('shortname' => $data->shortname))) {
            errorAndDie('shortnametaken');
        }
    }

    if (!isset($content->fullname)) {
        errorAndDie('fullname needs to be set!');
    } else {
        $data->fullname = $content->fullname;
    }

    if (!isset($content->category)) {
        errorAndDie('category needs to be set!');
    } else {
        $data->category = $content->category;
    }

    if (!isset($content->password)) {
        errorAndDie('password needs to be set!');
    }

    if (!isset($content->visible)) {
        //nothing here
    } else {
        $data->visible = $content->visible;
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
    $enrolinstance->password = $content->password;
    $DB->update_record('enrol', $enrolinstance);

    echo json_encode(array('course' => $course->id, 'selfenrolinstance' => $enrolinstance->id));
}

function newCourseOptions() {
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
    $vocab = array();
    $vocab['fullname'] = get_string('fullnamecourse');
    $vocab['shortname'] = get_string('shortnamecourse');
    $vocab['newcourse'] = get_string('newcourse');
    $vocab['createnewcourse'] = get_string('createnewcourse');
    $vocab['category'] = get_string('category');
    $vocab['shortnametaken'] = get_string('shortnametaken');
    $vocab['selfenrolment'] = get_string('pluginname', 'enrol_self');
    $vocab['password'] = get_string('password', 'enrol_self');
    $vocab['nopassword'] = get_string('nopassword', 'enrol_self');


    $result['vocabulary'] = $vocab;


    //printArray($result);
    echo json_encode($result);
}

function getVocabulary() {
    $result = array();
    $result['course'] = get_string('course');
    $result['user'] = get_string('user');
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
    $result['inactive'] = get_string('inactive');
    $result['newusers'] = get_string('newusers');
    $result['personal'] = get_string('personal');
    $result['info'] = get_string('info');
    
    //echo "<pre>" . print_r($result, true) . "</pre>";
    echo json_encode($result);
}

function getCourseEnrolmentMethods($courseid) {
    global $DB, $PAGE;
    $PAGE->set_context(context_system::instance());
    $instances = enrol_get_instances($courseid, false);
    $result = array();

    foreach ($instances as $instanceid => $instance) {
        $enrol = enrol_get_plugin($instance->enrol);

        $array['id'] = $instance->id;
        $array['enrol'] = $instance->enrol;
        if (!enrol_is_enabled($instance->enrol) or $instance->status != ENROL_INSTANCE_ENABLED) {
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

function getActivitiesInCourse($courseid) {
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
    $table['cols'][] = array('label' => 'visibility', 'type' => 'number');


    $table['rows'] = array();

    $activities = get_array_of_activities($courseid);
    //echo "<pre>" . print_r($activities, true) . "</pre>";

    foreach ($activities as $modid => $activity) {
        
        if($activity->visible) {
            $class="";
        } else {
            $class="dimmed";
        }
        $spanBegin = ''; //"<span class='" . $class . "'>";
        $spanEnd = ''; //"</span>";
        $activityname = $spanBegin . $activity->name . $spanEnd;
        $sectionname = $spanBegin . get_section_name($courseid, $activity->section) . $spanEnd;
        $icon = ''; //"<img src='" . $OUTPUT->pix_url('icon', 'mod_'.$activity->mod) . "'>";
        $activityType = $spanBegin . $icon . get_string('pluginname', $activity->mod) . $spanEnd;
        //$table['rows'][] = ['c' => array('v' => $sectionname), array('v' => $activityType), array('v' => $activityname)];
        $table['rows'][] = ['c' => array(
            ['v' => $activity->id],
            ['v' => $sectionname],
            ['v' => $activityType],
            ['v' => $activityname],
            ['v' => $activity->mod],
            ['v' => $activity->cm],
            ['v' => $activity->visible]
        )];
    }
    //printArray($table);
    echo json_encode($table);
}

function getPersonsInCourse($courseid) {
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

function enrolUserToCourse($userid, $courseid, $roleid) {
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

function user($userid) {
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
            $courses_enrolled['rows'][] = [
                'c' => array(
                    ['v' => $course->id],
                    array('v' => $course->parentcategoryname),
                    array('v' => $course->categoryname),
                    array('v' => $course->fullname),
                    array('v' => role_get_name($role))
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

    $ret['courses'] = $courses_enrolled;
    //printArray($ret);
    echo json_encode($ret);
}

function course($courseid) {
    //global $PAGE;
    //$PAGE->set_context();
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
    $data['category']['v'] = $course->category;

    $context = context_course::instance($courseid);
    $data['roles']['string'] = get_string('roles');
    $data['roles']['v'] = role_get_names($context);
    $data['personsInCourse'] = count_enrolled_users($context);
    $data['enrolmentmethods'] = getCourseEnrolmentMethods($courseid);

    $usedRoles = get_roles_used_in_context($context);
    //printArray($usedRoles);
    foreach ($usedRoles as $roleid => $role) {
        $count = count_role_users($roleid, $context);
        if ($count != 0) {
            $data['rolesInCourse'][$roleid]['id'] = $roleid;
            $data['rolesInCourse'][$roleid]['name'] = $role->name;
            $data['rolesInCourse'][$roleid]['number'] = $count;
            $data['rolesInCourse'][$roleid]['sortorder'] = $role->sortorder;
        }
    }
    $result = array();
    $result['data'] = $data;
    //printArray($result);
    echo json_encode($result);
}

function allUsers() {
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
    $result['cols'][] = array('label' => get_string('fullname'), 'type' => 'string');
    $result['rows'] = array();

    foreach ($users as $userid => $user) {
        $result['rows'][] = [
            'c' => array(
                ['v' => $user->id],
                array('v' => $user->username),
                array('v' => $user->firstname),
                array('v' => $user->lastname),
                array('v' => $user->email),
                array('v' => $user->firstname . ' ' . $user->lastname)
            )
        ];
    }
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

function errorAndDie($msg) {
    echo json_encode(array('error' => $msg));
    die();
}
?>

