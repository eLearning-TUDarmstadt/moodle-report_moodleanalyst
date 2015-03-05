<?php

//ini_set('display_errors', 'On');
//error_reporting(E_ALL | E_STRICT);

require 'Slim/Slim.php';
require_once '../../../config.php';
require_once '../../../course/lib.php';

// compress output if possible
//ob_start('ob_gzhandler');
//require_once($CFG->libdir.'/adminlib.php');
//admin_externalpage_setup('reportmoodleanalyst', '', null, '', array('pagelayout'=>'report'));

$context = context_system::instance();
require_capability('report/moodleanalyst:view', $context);

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim ();

// $app->get('/course', 'course');
// $app->('/course(/:identifier)', 'course');
$app->map('/course/(:identifier)', 'course')->via('GET', 'POST');
$app->map('/course/id/:id', 'courseDetailed')->via('GET', 'POST');
$app->map('/Dateien', 'Dateien')->via('GET', 'POST');
$app->map('/Kommunikation', 'Kommunikation')->via('GET', 'POST');
$app->map('/Tests', 'Tests')->via('GET', 'POST');
$app->map('/Kooperation', 'Kooperation')->via('GET', 'POST');
$app->map('/Lehrorganisation', 'Lehrorganisation')->via('GET', 'POST');
$app->map('/Rueckmeldungen', 'Rueckmeldungen')->via('GET', 'POST');
$app->map('/user/(:identifier)', 'user')->via('GET', 'POST');
$app->map('/user/id/:id', 'userDetailed')->via('GET', 'POST');
$app->map('/Category(/:id)', 'Category')->via('GET', 'POST');
$app->map('/Schnittstelle(/:id)', 'Schnittstelle')->via('GET', 'POST');
$app->map('/Kategorie(/:id)', 'Kategorie')->via('GET', 'POST');
$app->map('/LeereKurse', 'LeereKurse')->via('GET');
$app->map('/inaktiveNutzer', 'inaktiveNutzer')->via('GET', 'POST');
$app->map('/NeuesteKurse', 'NeuesteKurse')->via('GET', 'POST');
$app->map('/OneActivityInCourse/', 'OneActivityInCourse')->via('GET', 'POST');
$app->map('/teachersOfEmptyCourses', 'teachersOfEmptyCourses')->via('GET', 'POST');
//$app->map('/test123/:id', 'test123')->via('GET', 'POST');
$app->run();

function course() {
    $app = \Slim\Slim::getInstance();
    $query = $app->request->get('query');
    global $DB;

    $sql = "SELECT
		{course}.id,
		{course}.fullname,
		{course}.shortname,
                {course}.visible,
		{course}.category as fbID,
		(SELECT name FROM {course_categories} WHERE id={course}.category) as fb,
		(SELECT parent FROM {course_categories} WHERE id={course}.category) as semesterID,
		(SELECT name FROM {course_categories} WHERE id=(SELECT parent FROM {course_categories} WHERE id={course}.category)) as semester
	FROM {course} ";
    if ($query) {
        $name = str_replace(' ', '%', $query);
        $sql .= " WHERE {course}.fullname LIKE '%" . $name . "%' OR {course}.shortname LIKE '%" . $name . "%'";
    }
    $result = $DB->get_records_sql($sql);
    $array = array(
        "Result" => "OK",
        "Records" => $result
    );
    // echo "<pre>".print_r($result, true)."</pre>";
    echo json_encode($array);
}

/* function test123($id) {
  $a = get_array_of_activities($id);
  $b = get_course($id);
  $c = new course_modinfo(get_course($id));
  $d = $c->get_used_module_names();
  $e = get_fast_modinfo($id);
  //echo json_encode($a);
  echo "<pre>".print_r($d, true)."</pre>";
  } */

function courseDetailed($id) {
    if ($id == "undefined") {
        echo "NOT A COURSE ID!";
        die;
    }
    global $DB;
    $result = $DB->get_records('course', array(
        'id' => $id
    ));
    $array = array();
    foreach ($result as $key => $value) {
        $array [] = $value;
    }
    // echo "<pre>".print_r($array, true)."</pre>";

    $categories = $DB->get_records('course_categories', array(), null, 'id,name,parent');
    if ($array [0]->category == 0) {
        $array [0]->fbID = false;
        $array [0]->semesterID = false;
    } else {
        $array [0]->fbID = $array [0]->category;
        $array [0]->fb = $categories [$array [0]->fbID]->name;
        $array [0]->semesterID = $categories [$array [0]->fbID]->parent;
    }

    if ($array [0]->semesterID == 0) {
        $array [0]->semester = false;
    } else {
        $array [0]->semester = $categories [$array [0]->semesterID]->name;
    }
    $array[0]->Lehrende = getPersonsInCourse("Lehrende", $id);
    $array[0]->Assistenz = getPersonsInCourse("Assistenz", $id);
    $array[0]->Tutoren = getPersonsInCourse("Tutoren", $id);
    $array[0]->Studierende = getPersonsInCourse("Studierende", $id);

    $array[0]->Activities = getTableOfActivitiesInCourse($id);

    $array[0]->Module = getModulesInCourse($id);

    $array[0]->Einschreibemethoden = getEinschreibemethoden($id);

    $array[0]->UserEnrolments = getUserEnrolments($id);

    //echo "<pre>" . print_r ($array, true) . "</pre>";
    $array = array("Result" => "OK", "Records" => $array);
    echo json_encode($array);
}

function getTableOfActivitiesInCourse($courseid) {
    global $OUTPUT;
    $table = array();
    $table['cols'] = array();
    $table['cols'][] = array('label' => 'Section', 'type' => 'number');
    $table['cols'][] = array('label' => 'Abschnitt', 'type' => 'string');
    $table['cols'][] = array('label' => 'Aktivität', 'type' => 'string');
    $table['cols'][] = array('label' => 'Name', 'type' => 'string');
    
    $table['rows'] = array();

    $activities = get_array_of_activities($courseid);
    //echo "<pre>" . print_r($activities, true) . "</pre>";

    foreach ($activities as $modid => $activity) {
        $section = $activity->section;
        $abschnitt = get_section_name($courseid, $activity->section);
        $icon = '';//"<img src='" . $OUTPUT->pix_url('icon', 'mod_'.$activity->mod) . "'>";
        $aktivitaet = $icon . get_string('pluginname', $activity->mod);
        $name = $activity->name;
        $table['rows'][] = ['c' => array(array('v' => $section),array('v' => $abschnitt), array('v' => $aktivitaet), array('v' => $name))];
    }
    //echo "<pre>" . print_r($table, true) . "</pre>";
    return $table;
}

/**
 * Gibt alle Personen zur�ck, die mit gegebener Rolle in einen Kurs eingetragen sind
 *
 * @param string $role
 *        	alle 'name'-Eintr�ge in {role}, bspw. Manager, Course creator, Lehrende, Tutor, Studierende, Gast, Assistenz
 * @param int $course
 *        	Moodle-Kurs-ID
 */
function getPersonsInCourse($role, $course) {
    global $DB;
    $sql = "SELECT  
				{role_assignments}.id,
				{role_assignments}.roleid,
				{role_assignments}.userid,
				
				{context}.instanceid as course,
				
				{role}.archetype,
				
				{user}.firstname,
				{user}.lastname
			FROM 
				{role_assignments}, {context}, {role}, {user}
			WHERE
				{role_assignments}.contextid = {context}.id AND
				{context}.instanceid = " . $course . " AND
				{role}.id = {role_assignments}.roleid AND
				{user}.id = {role_assignments}.userid AND
				{role}.name LIKE '" . $role . "'";
    return $DB->get_records_sql($sql);
}

function getModulesInCourse($course) {
    global $DB;
    $sql = "SELECT
				{course_modules}.module,
				{modules}.name,
				count({course_modules}.module) as anzahl
			FROM 
				{course_modules}, {modules}
			WHERE
				{course_modules}.course = $course AND
				{modules}.id = {course_modules}.module
			GROUP BY 
				{course_modules}.module, {modules}.name
			";
    return $DB->get_records_sql($sql);
}

function getEinschreibemethoden($course) {
    global $DB;
    $sql = "SELECT
				{enrol}.id,
                                {enrol}.enrol,
				{enrol}.status,
				{enrol}.password
			FROM {enrol}
			WHERE
				{enrol}.courseid = $course
			";
    return $DB->get_records_sql($sql);
}

function getUserEnrolments($course) {
    global $DB;
    $sql = "SELECT
				{enrol}.id,
				{enrol}.enrol,
				count({user_enrolments}.userid) as anzahl
			FROM
				{enrol}, {user_enrolments}
			WHERE
				{user_enrolments}.enrolid = {enrol}.id AND
				{enrol}.courseid = $course
			GROUP BY
				enrolid, {enrol}.id,{enrol}.enrol
			";
    return $DB->get_records_sql($sql);
}

function Dateien() {
    $app = \Slim\Slim::getInstance();
    $jtSorting = $app->request->get('jtSorting');
    $mods = array('resource', 'folder');
    echo GetTableOfCoursesWithAmountOfModules($mods, $jtSorting);
}

function Kommunikation() {
    $app = \Slim\Slim::getInstance();
    $jtSorting = $app->request->get('jtSorting');
    $mods = array('chat', 'forum');
    echo GetTableOfCoursesWithAmountOfModules($mods, $jtSorting);
}

function Tests() {
    $app = \Slim\Slim::getInstance();
    $jtSorting = $app->request->get('jtSorting');
    $mods = array('quiz', 'assign', 'hotpot', 'lesson', 'games');
    echo GetTableOfCoursesWithAmountOfModules($mods, $jtSorting);
}

function Kooperation() {
    $app = \Slim\Slim::getInstance();
    $jtSorting = $app->request->get('jtSorting');
    $mods = array('wiki', 'data', 'glossary', 'workshop');
    echo GetTableOfCoursesWithAmountOfModules($mods, $jtSorting);
}

function Lehrorganisation() {
    $app = \Slim\Slim::getInstance();
    $jtSorting = $app->request->get('jtSorting');
    $mods = array();
    $additionalRows = "(SELECT COUNT(id) FROM {groups} WHERE courseid = {course}.id) AS gruppen,";
    echo GetTableOfCoursesWithAmountOfModules($mods, $jtSorting, $additionalRows);
}

function Rueckmeldungen() {
    $app = \Slim\Slim::getInstance();
    $jtSorting = $app->request->get('jtSorting');
    $mods = array('choice', 'feedback', 'hotquestion');
    echo GetTableOfCoursesWithAmountOfModules($mods, $jtSorting);
}

function Category($id = 0) {
    GLOBAL $DB;
    $sql = "SELECT 
				c.id as Sem,
				c.name,
				(
					SELECT COUNT({course}.id) FROM {course} WHERE {course}.category = c.id
				) +
				(
					SELECT COUNT({course}.id) FROM {course} WHERE 
					{course}.category 
					IN
					(SELECT k.id FROM {course_categories} k WHERE k.parent = c.id)
				)	AS kursegesamt,
				
				(
					SELECT COUNT({course}.id) FROM {course} WHERE {course}.category = c.id AND {course}.idnumber != ''
				) +
				(
					SELECT COUNT({course}.id) FROM {course} WHERE 
					{course}.category 
					IN
					(SELECT k.id FROM {course_categories} k WHERE k.parent = c.id) AND {course}.idnumber != ''
				)	AS schnittstelle,
				
				(
					SELECT COUNT({course}.id) FROM {course} WHERE {course}.category = c.id AND {course}.idnumber = ''
				) +
				(
					SELECT COUNT({course}.id) FROM {course} WHERE 
					{course}.category 
					IN
					(SELECT k.id FROM {course}_categories k WHERE k.parent = c.id) AND {course}.idnumber = ''
				)	AS manuell
				
			FROM 
				{course_categories} c
			WHERE
				parent = " . $id . " AND
				id != 1
			ORDER BY
				c.timemodified ASC";
    $categories = $DB->get_records_sql($sql);
    $manuell = 0;
    $schnittstelle = 0;
    $gesamt = 0;
    foreach ($categories as $id => $category) {
        $manuell += $category->manuell;
        $schnittstelle += $category->schnittstelle;
        $gesamt += $category->kursegesamt;
    }
    $array['subcategories'] = $categories;
    $array['manuell'] = $manuell;
    $array['schnittstelle'] = $schnittstelle;
    $array['gesamt'] = $gesamt;
    //echo "<pre>".print_r($array, true)."</pre>";
    echo json_encode($array);
}

/**
 * Kibt Kursliste aus, die neben allgemeinen Kursinformationen ausgibt, wie oft die angegebenen Module vorhanden sind. Sortierung nach $sortString
 * 
 * @param array $mods Alle Module, f�r die die Anzahl ermittelt werden soll. Bsp.: array("chat", "forum")
 * @param string $sortString Sort-String 
 * @param string $additionalRows SQL-Abfrage als String
 * @return json $json Ausgabe-JSON
 */
function GetTableOfCoursesWithAmountOfModules($mods, $sortString = "", $additionalRows = "") {
    global $DB;

    $sql = "SELECT
			{course}.id as course,
			{course}.fullname,
			{course}.timecreated as timecreated,
			{course}.timemodified as timemodified,";
    foreach ($mods as $mod) {
        $sql .= "(SELECT COUNT(id) FROM {course_modules} WHERE {course_modules}.course = {course}.id AND module=(SELECT id FROM {modules} WHERE name LIKE '" . $mod . "')) AS " . $mod . ",";
    }
    $sql .= $additionalRows;
    $sql .= "
			(SELECT {course_categories}.name FROM {course_categories} WHERE {course_categories}.id={course}.category) AS fb,
			{course}.category as fbid,
			(SELECT {course_categories}.name FROM {course_categories} WHERE {course_categories}.id=
			(SELECT {course_categories}.parent FROM {course_categories} WHERE {course_categories}.id={course}.category)
			) as semester,
			(SELECT {course_categories}.parent FROM {course_categories} WHERE {course_categories}.id={course}.category) as semesterid,
			(SELECT COUNT(id) FROM {user_enrolments} WHERE enrolid IN (SELECT {enrol}.id FROM {enrol} WHERE {enrol}.courseid={course}.id)) as participants
			FROM {course}
				";
    if ($sortString) {
        $sql .= " ORDER BY " . $sortString;
    }
    //echo "<pre>".print_r($sql, true)."</pre>";
    $results = $DB->get_records_sql($sql);

    $array = array();

    foreach ($results as $key => $value) {
        if (!empty($mods)) {
            $sum = 0;
            //echo print_r($value, true);
            foreach ($mods as $mod) {
                //echo $mod.": ".(string)$value->$mod;
                $sum = $sum + $value->$mod;
            }
            if ($sum > 0) {
                $array[] = $value;
            }
        } else {
            $array[] = $value;
        }
    }
    $array = array(
        "Result" => "OK",
        "SQL" => $sql,
        "Records" => $array
    );
    return json_encode($array);
}

function user() {
    $app = \Slim\Slim::getInstance();
    $query = $app->request->get('query');
    global $DB;

    $sql = "SELECT
				{user}.id,
				{user}.username,
				{user}.firstname,
				{user}.lastname,
				{user}.email
			FROM {user}
			";

    if ($query) {
        $name = str_replace(' ', '%', $query);
        if (strpos($name, "%") !== false) {
            $sql .= " WHERE ({user}.firstname + {user}.lastname) LIKE '%" . $name . "%'";
        } else {
            $sql .= " WHERE ({user}.firstname + {user}.lastname) LIKE '%" . $name . "%' OR {user}.username LIKE '%" . $name . "%'";
        }
    }

    $result = $DB->get_records_sql($sql);
    $array = array(
        "Result" => "OK",
        "Records" => $result
    );
    // echo "<pre>".print_r($result, true)."</pre>";
    echo json_encode($array);
}

function userDetailed($id) {
    global $DB;
    $sql = "SELECT
				id,
				auth,
				username,
				firstname, 
				lastname,
				email,
				lang,
				lastaccess
			FROM {user} WHERE id=" . $id;

    $result = $DB->get_record_sql($sql);

    $sql = "SELECT
			mdl_role_assignments.id,
			mdl_role_assignments.roleid,
			mdl_context.instanceid as course,
			mdl_role.name,
			{course}.category as fbid,
			{course}.fullname,
			{course}.shortname,
                        {course}.visible,
			(SELECT {course_categories}.name FROM {course_categories} WHERE id={course}.category) as fb,
			(SELECT {course_categories}.parent FROM {course_categories} WHERE id={course}.category) as semesterid,
			(SELECT {course_categories}.name FROM {course_categories} WHERE id=(SELECT {course_categories}.parent FROM {course_categories} WHERE id={course}.category)) as semester
			FROM mdl_role_assignments, mdl_context, mdl_role, {course}
			WHERE
			userid=" . $id . " AND
			mdl_context.id = mdl_role_assignments.contextid AND
			mdl_context.contextlevel=50 AND 
			mdl_role_assignments.roleid = mdl_role.id AND
			{course}.id = mdl_context.instanceid";
    $result->roles = $DB->get_records_sql($sql);

    //echo "<pre>".print_r($result, true)."</pre>";

    $array = array("Result" => "OK", "Records" => $result);
    sendeJSON($array);
}

function sendeJSON($array) {
    sendeHeader("application/json");
    echo json_encode($array);
}

function sendeText($text, $convert = true) {
    //if ($convert && is_array($text))
    //	$text = print_r($text, true);
    sendeHeader();
    echo "<pre>" . print_r($text, true) . "</pre>";
}

function sendeHeader($type = "text/plain") {
    if (headers_sent())
        return true;
    header('Content-type: ' . $type);
    header('Cache-Control: private, must-revalidate, pre-check=0, post-check=0, max-age=0');
    header('Expires: ' . gmdate('D, d M Y H:i:s', 0) . ' GMT');
    header('Pragma: no-cache');
    header('Accept-Ranges: none');
}

/**
 * Gibt an, wie viele Kurse sich in Kategorie bzw. Subkategorien befinden
 *  
 */
function CountCoursesInCategory($parentCategory = 0) {
    GLOBAL $DB;
    $schnittstelle = 0;
    $manuell = 0;
    $gesamt = 0;
    $sql = "SELECT id, idnumber FROM mdl_course WHERE category=" . $parentCategory;
    $coursesInCategory = $DB->get_records_sql($sql);
    foreach ($coursesInCategory as $id => $zahlen) {
        if (!empty($zahlen->idnumber))
            $schnittstelle++;
        else
            $manuell++;
    }

    $subcategories = SubcategoriesInCategory($parentCategory);
    foreach ($subcategories as $category => $werte) {
        $zahlen = CountCoursesInCategory($category);
        $schnittstelle += $zahlen['schnittstelle'];
        $manuell += $zahlen['manuell'];
    }

    $gesamt = $schnittstelle + $manuell;
    return array("schnittstelle" => $schnittstelle, "manuell" => $manuell, "gesamt" => $gesamt);
}

function SubcategoriesInCategory($parentCategory = 0) {
    GLOBAL $DB;
    $sql = "SELECT id, name FROM {course_categories} WHERE parent=" . $parentCategory . " ORDER BY {course_categories}.timemodified ASC";
    return $DB->get_records_sql($sql);
}

function Schnittstelle($parentCategory = 0) {
    $subcategories = SubcategoriesInCategory($parentCategory);
    foreach ($subcategories as $id => $werte) {
        $zahlen = CountCoursesInCategory($id);
        $werte->schnittstelle = $zahlen['schnittstelle'];
        $werte->manuell = $zahlen['manuell'];
        $werte->gesamt = $zahlen['gesamt'];
    }
    //echo "<pre>".print_r($subcategories, true)."</pre>";
    sendeJSON($subcategories);
}

function Kategorie($category = 0) {
    if ($category == 0) {
        sendeJSON(array("parentName" => '', "parentID" => 0));
        return;
    }
    GLOBAL $DB;
    $sql = "SELECT parent FROM {course_categories} WHERE id = " . $category;
    $id = $DB->get_record_sql($sql);
    $parent = $id->parent;
    $sql = "SELECT name FROM {course_categories} WHERE id = " . $parent;
    $id = $DB->get_record_sql($sql);
    if (!$id) {
        $array = array("parentName" => '', "parentID" => $parent);
    } else {
        $array = array("parentName" => $id->name, "parentID" => $parent);
    }
    //echo "<pre>".print_r($array, true)."</pre>";
    sendeJSON($array);
}

function LeereKurse() {
    GLOBAL $DB;
    $sql = "SELECT
	mdl_course.id,
	mdl_course.category AS fbID,
	mdl_course.fullname,
	mdl_course.shortname,
	(SELECT mdl_course_categories.name FROM mdl_course_categories WHERE mdl_course_categories.id=mdl_course.category) AS fb,
	(SELECT mdl_course_categories.parent FROM mdl_course_categories WHERE mdl_course_categories.id=mdl_course.category) AS semesterID,
	(SELECT mdl_course_categories.name FROM mdl_course_categories WHERE mdl_course_categories.id=(SELECT mdl_course_categories.parent FROM mdl_course_categories WHERE mdl_course_categories.id=mdl_course.category)) AS semester
FROM
	mdl_course
WHERE
	(SELECT COUNT(mdl_course_modules.course) FROM mdl_course_modules WHERE mdl_course_modules.course=mdl_course.id GROUP BY mdl_course_modules.course) < 2";

    $result = $DB->get_records_sql($sql);
    $array = array("Result" => "OK", "Count" => count($result), "Records" => $result);
    sendeJSON($array);
}

function inaktiveNutzer($minTimeDiff = 31536000) {
    GLOBAL $DB;
    $sql = "SELECT id, username, firstname + ' ' + lastname AS fullname, " . time() . "-lastaccess as timediff FROM {user} WHERE auth LIKE 'cas' AND " . time() . "-lastaccess > " . $minTimeDiff . " ORDER BY timediff DESC";
    $result = $DB->get_records_sql($sql);
    $array = array("Result" => "OK", "Count" => count($result), "Records" => $result);
    sendeJSON($array);
}

function NeuesteKurse() {
    GLOBAL $DB;
    $sql = "SELECT TOP 50
				mdl_course.timecreated,
    			mdl_course.fullname,
				mdl_course.id,
				mdl_course.category AS fbID,
				(SELECT mdl_course_categories.name FROM mdl_course_categories WHERE mdl_course_categories.id=mdl_course.category) AS fb,
				(SELECT mdl_course_categories.parent FROM mdl_course_categories WHERE mdl_course_categories.id=mdl_course.category) AS semesterID,
				(SELECT mdl_course_categories.name FROM mdl_course_categories WHERE mdl_course_categories.id=(SELECT mdl_course_categories.parent FROM mdl_course_categories WHERE mdl_course_categories.id=mdl_course.category)) AS semester
			FROM
				mdl_course
			ORDER BY timecreated
			DESC";

    $result = $DB->get_records_sql($sql);
    $array = array("Result" => "OK", "Records" => $result);
    sendeJSON($array);
}

function OneActivityInCourse() {
    GLOBAL $DB, $USER;
    $sql = "SELECT
                mdl_course.id,
                mdl_course.category AS fbID,
                mdl_course.fullname,
                mdl_course.shortname,
                (SELECT mdl_course_categories.name FROM mdl_course_categories WHERE mdl_course_categories.id=mdl_course.category) AS fb,
                (SELECT mdl_course_categories.parent FROM mdl_course_categories WHERE mdl_course_categories.id=mdl_course.category) AS semesterID,
                (SELECT mdl_course_categories.name FROM mdl_course_categories WHERE mdl_course_categories.id=(SELECT mdl_course_categories.parent FROM mdl_course_categories WHERE mdl_course_categories.id=mdl_course.category)) AS semester
            FROM
                mdl_course
            WHERE
                (SELECT COUNT(mdl_course_modules.course) FROM mdl_course_modules WHERE mdl_course_modules.course=mdl_course.id GROUP BY mdl_course_modules.course) = 2";

    $result = $DB->get_records_sql($sql);

    foreach ($result as $key => $value) {
        $modinfo = new course_modinfo(get_course($key), $USER->id);
        $modsincourse = $modinfo->get_used_module_names();
        $oneactivity = '';

        foreach ($modsincourse as $english => $deutsch) {
            if ($deutsch != "Forum") {
                $oneactivity = $deutsch;
            } else if (strlen($oneactivity) < 1) {
                $oneactivity = $deutsch;
            }
        }
        $value->activity = $oneactivity;
    }
    $array = array("Result" => "OK", "Records" => $result);

    echo json_encode($array);
}

function teachersOfEmptyCourses() {
    GLOBAL $DB;
    $sql = "SELECT
	mdl_course.id,
	mdl_course.category AS fbID,
	mdl_course.fullname,
	mdl_course.shortname,
	(SELECT mdl_course_categories.name FROM mdl_course_categories WHERE mdl_course_categories.id=mdl_course.category) AS fb,
	(SELECT mdl_course_categories.parent FROM mdl_course_categories WHERE mdl_course_categories.id=mdl_course.category) AS semesterID,
	(SELECT mdl_course_categories.name FROM mdl_course_categories WHERE mdl_course_categories.id=(SELECT mdl_course_categories.parent FROM mdl_course_categories WHERE mdl_course_categories.id=mdl_course.category)) AS semester
        FROM
                mdl_course
        WHERE
                (SELECT COUNT(mdl_course_modules.course) FROM mdl_course_modules WHERE mdl_course_modules.course=mdl_course.id GROUP BY mdl_course_modules.course) < 2";

    $result = $DB->get_records_sql($sql);
    $persons = array();
    foreach ($result as $id => $courseinfos) {
        $sql = "SELECT  
                                
				{role_assignments}.id as assignmentid,
                                {user}.id as userid,
				{context}.instanceid as course,
				{user}.firstname,
				{user}.lastname,
                                {user}.email
			FROM 
				{role_assignments}, {context}, {role}, {user}
			WHERE
				{role_assignments}.contextid = {context}.id AND
				{context}.instanceid = " . $id . " AND
				{user}.id = {role_assignments}.userid AND
				{role}.name = 'Lehrende' AND
                                {user}.email != ''";
        $teachers = $DB->get_records_sql($sql);
        foreach ($teachers as $assignmentID => $infos) {
            //echo "<pre>".print_r($infos, true)."</pre>";
            $persons[$infos->userid] = $infos;
            $persons[$infos->userid]->courses[$courseinfos->id] = $courseinfos;
        }
    }



    $array = array("Result" => "OK", "Count" => count($result), "Records" => $persons);
    sendeJSON($array);
}

?>