<?php

require(dirname(__FILE__).'/../../config.php');
require_once($CFG->libdir.'/adminlib.php');


defined('MOODLE_INTERNAL') || die;

// page parameters
global $CFG, $DB, $OUTPUT;
admin_externalpage_setup('reportmoodleanalyst', '', null, '', array('pagelayout'=>'report'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('moodleanalyst', 'report_moodleanalyst'));




class moodleAnalyst {
	private $FormatDatum = "d.m.Y, H:i";
	
	function __construct() {
		$this->CreateNavigationTable();
		GLOBAL $CFG;
		//$this->DatabaseConnect();
		
	}
	
	function CreateNavigationTable() {
		global $OUTPUT;
		//
		//Navigation: Tabelle aufsetzen
		//
		$table = new html_table();
		//$table->head = array("eins", "zwei", "drei", "vier", "fuenf");
		//$table->colclasses = array('leftalign eins', 'leftalign zwei', 'leftalign drei', 'leftalign vier', 'leftalign fuenf');
		//$table->id = 'moodleanalyst_navigation';
		//$table->attributes['class'] = 'admintable generaltable';
		$table->data  = array();
		$row = array();
		//$row[] = '<a href="index_old.php?nav=ShowGeneralInformation">Allgemeine Informationen</a>';
		//$row[] = '<a href="index_old.php?nav=ShowCategories">Kategorien</a>';
		//$row[] = '<a href="index_old.php?nav=ShowCourses">Kurse</a>';
		//$row[] = '<a href="index_old.php?nav=ShowCoursesWithMostContent">Kurse mit meisten Inhalten</a>';
		//$row[] = '<a href="index_old.php?nav=ShowCoursesWithMostForums">Kurse mit meisten Foren</a>';
		$row[] = '<a href="index_old.php?nav=Schnittstelle">Schnittstelle</a>';
		$row[] = '<a href="index_old.php?nav=Gruppierungen">Gruppierungen</a>';
		$row[] = '<a href="index_old.php?nav=ShowCoursesOrderedByAmountOfUsersDESC">Kurse mit Nutzern</a>';
		$row[] = '<a href="index_old.php?nav=CountCoursesByCourseFormat">Kurs-Formate</a>';
		//$row[] = '<a href="index_old.php?nav=ShowAdobeConnectSettings">Adobe Connect</a>';
		$row[] = '<a href="index_old.php?nav=ShowGames">Games</a>';
		$row[] = '<a href="index_old.php?nav=ContentsOfCoursesInCategory">Kursinhalte</a>';
		$row[] = '<a href="index_old.php?nav=Helios">Helios</a>';
		$row[] = '<a href="index_old.php?nav=LeereKurse">Leere Kurse</a>';
		$row[] = '<a href="index_old.php?nav=Tutoren">Tutoren</a>';
		$table->data[] = $row;
		echo html_writer::table($table);
		echo $OUTPUT->heading("Kategorien", 4);
		$table = new html_table();
		$table->data  = array();
		$row = array();
				
		$row[] = '<a href="index_old.php?nav=ShowCategoryFiles">Dateien</a>';
		$row[] = '<a href="index_old.php?nav=ShowCategoryCommunication">Kommunikation</a>';
		$row[] = '<a href="index_old.php?nav=ShowCategoryTest">(Selbst-)Selbstüberprüfung</a>';
		$row[] = '<a href="index_old.php?nav=ShowCategoryCooperation">Kooperation</a>';
		$row[] = '<a href="index_old.php?nav=ShowCategoryCourseOrganisation">Lehrorganisation</a>';
		$row[] = '<a href="index_old.php?nav=ShowCategoryFeedback">Rückmeldungen</a>';
		
		
		$table->data[] = $row;
		echo html_writer::table($table);	
	
	}

	private function GetShortnameByID($ID, $asLink = true) {
		$query = "SELECT shortname from mdl_course WHERE id=".$ID.";";
		$result = mssql_query($query);
		$res_array = mssql_fetch_array($result);
		$Shortname = $res_array[0];
		if($asLink) {
			$ShortnameLink = "<a href=/course/view.php?id=".$ID.">".$Shortname."</a>";
			return $ShortnameLink;
		}
		return $Shortname;
	}
	private function GetFullnameByID($ID, $asLink = true) {
		GLOBAL $DB;
		//require_once '../../course/lib.php';
		//$course = new stdClass();
		
		$Fullname = $DB->get_record('course', array('id' => $ID), 'fullname', IGNORE_MISSING);
		
		if(empty($Fullname)) {
			return null;
		}
		else {
			$Fullname = $Fullname->fullname;
			if($asLink) {
				$FullnameLink = "<a href=/course/view.php?id=".$ID.">".$Fullname."</a>";
				return $FullnameLink;
			}
			return $Fullname;
		}
	}	
	private function GetModuleNameById($id) {
		$query = "SELECT name from mdl_modules WHERE id=".$id.";";
		$result = mssql_query($query);
		$ModuleName = mssql_fetch_array($result);
		return $ModuleName[0];		
	}	
	private function GetSumOfDiscussionsInCourse($id) {
		$query = "SELECT count(course) FROM mdl_forum_discussions WHERE course=".$id." GROUP BY course;";
		$result = mssql_query($query);
		$sum = mssql_fetch_array($result);
		return $sum[0];
	}
	private function GetSumOfFoldersByCourseID($id) {
		$query = "SELECT count(course) FROM mdl_folder WHERE course=".$id.";";
		$result = mssql_query($query);
		$sum = mssql_fetch_array($result);
		return $sum[0];
	}
	
	private function GetIdOfModule($name) {
		$query = "SELECT id FROM mdl_modules WHERE name='".$name."';";
		$result = mssql_query($query);
		$sum = mssql_fetch_assoc($result);
		return $sum['id'];
	}
	
	private function GetCategoryName($categoryID, $asLink = true) {
		if ($categoryID == 0) {
			return "<b>OBERSTE EBENE!</b>";
		}
		$query = "SELECT name FROM mdl_course_categories WHERE id='".$categoryID."';";
		$result = mssql_query($query);
		$sum = mssql_fetch_array($result);
		if($asLink) {
			return "<a href='/course/index_old.php?categoryid=".$categoryID."'>".$sum[0]."</a>";
		}
		return $sum[0];
	}
	
	private function GetCategoryOfCourse($id) {
		$query = "SELECT category FROM mdl_course WHERE id=".$id.";";
		$result = mssql_query($query);
		$sum = mssql_fetch_array($result);
		return $sum[0];
	}
	
	private function GetParentCategoryOfCategory($id) {
		GLOBAL $DB;
		$result = $DB->get_record('course_categories', array('id' => $id), 'parent');
		if(empty($result)) {
			return null;
		}
		else {
			return $result->parent;
		}
	}
	
	private function GetFBOfCourse($id, $asLink = true) {
		$FBid = $this->GetCategoryOfCourse($id);
		$FB = $this->GetCategoryName($FBid);
		if($asLink) {
			$FBLink = "<a href=/course/index_old.php?categoryid=".$FBid.">".$FB."</a>";
			return $FBLink;
		}
		return $FB;
	}
	
	private function GetSemesterOfCourse($id, $asLink = true) {
		$semesterid = $this->GetParentCategoryOfCategory($this->GetCategoryOfCourse($id));
		$semester = $this->GetCategoryName($semesterid);
		if($asLink) {
			GLOBAL $CFG;
			$semester = "<a href=/course/index_old.php?categoryid=".$semesterid.">".$semester."</a>";
			return $semester;
		}
		return $semester;
	}
	private function GetIdOfRoleByShortname($name) {
		$query = "SELECT id FROM mdl_role WHERE shortnamename='".$name."';";
		$result = mssql_query($query);
		$sum = mssql_fetch_assoc($result);
		return $sum['id'];
	}
	
	private function GetUserIDsOfPersonsWithRoleInCourse($RoleID, $CourseID) {
		$query = "SELECT userid FROM mdl_role_assignments WHERE shortnamename='".$name."';";
		$result = mssql_query($query);
		$sum = mssql_fetch_assoc($result);
		return $sum['id'];
	}
	
	private function GetAllDiscussionsOfCourse($id) {
		$query = "SELECT id FROM mdl_forum_discussions WHERE course=".$id.";";
		$result = mssql_query($query);
		$discussions = mssql_fetch_array($result);
		return $discussions;
	
	}
	
	private function GetSumOfPostsInCourse($id) {
		$discussions = $this->GetAllDiscussionsOfCourse($id);
		$sum = 0;
		foreach ($discussions as $key => $value) {
			$query = "SELECT count(discussion) from mdl_forum_posts WHERE discussion=".$id[$key].";";
			$result = mssql_query($query);
			$posts = mssql_fetch_array($result);
			$sum += $posts[0];
		}
		return $sum;
	}
	
	private function GetAllCourseIDs() {
		$query = "SELECT id FROM mdl_course ORDER BY id ASC;";
		$result = mssql_query($query);
		//$ret = array();
		while($row = mssql_fetch_assoc($result)) {
			$ret[] = $row;
		}
		return $ret;
	}
	
	private function GetSumOfModuleinstancesByCourseID($module, $course) {
		$query = "SELECT count(course) as anzahl FROM mdl_course_modules WHERE course=".$course." AND module=".$this->GetIdOfModule($module)." GROUP BY course;";
		$result = mssql_query($query);
		$sum = mssql_fetch_array($result);
		if(!isset($sum[0])) {
			$sum[0] = 0;
		}
		return $sum[0];
	}
	
	private function GetEnrolIDOfTypeAndCourse($enrolmenttype, $course) {
		$query = "SELECT id FROM mdl_enrol WHERE courseid=".$course." AND enrol='".$enrolmenttype."';";
		$result = mssql_query($query);
		$sum = mssql_fetch_array($result);
		if(!isset($sum[0])) {
			return -1;
		}
		return $sum[0];
	}
	
	private function GetContextIDOfCourse($course) {
		$sql = "SELECT id FROM mdl_context WHERE contextlevel=50 AND instanceid=".$course;
		$res = mssql_query($sql);
		$res_array = mssql_fetch_array($res);
		$context = $res_array[0];
		return $context;
	}
	
	private function GetPersonsNameByID($id, $asLink = true) {
		$sql = "SELECT firstname, lastname FROM mdl_user WHERE id=".$id;
		$result = mssql_query($sql);
		$res_array = mssql_fetch_assoc($result);
		$fullname = $res_array['firstname']." ".$res_array['lastname'];
		if($asLink) {
			return "<a href=/user/view.php?id=".$id.">".$fullname."</a>";
		}
		return $fullname;
	}
	
	private function GetSemestersAsArray() {
		$sql = "select id, name from mdl_course_categories WHERE parent=0";
		$result = mssql_query($sql);
		$array = array();
		
		while($res = mssql_fetch_assoc($result)) {
			$array[$res['id']] = $res['name'];
		}
		return $array;
	}
	
	private function GetFBsOfSemester($id) {
		$sql = "select id, name from mdl_course_categories WHERE parent=".$id;
		$result = mssql_query($sql);
		$array = array();
		
		while($res = mssql_fetch_assoc($result)) {
			$array[$res['id']] = $res['name'];
		}
		return $array;
	}
	
	private function CountCoursesInFB($id) {
		$sql = "select count(id) from mdl_course WHERE category=".$id;
		$result = mssql_query($sql);
		$array = mssql_fetch_array($result);
		return $array[0];
	}
	
	private function GetCoursesOfFB($id) {
		$sql = "select id from mdl_course WHERE category=".$id;
		$result = mssql_query($sql);
		$array = array();
		
		while($res = mssql_fetch_assoc($result)) {
			$array[$res['id']] = $res['id'];
		}
		return $array;
	}
	
	private function CountTutorsOfCoursesInFB($id) {
		$courses = $this->GetCoursesOfFB($id);
		$count = 0;
		foreach($courses as $key => $value) {
			$context = $this->GetContextIDOfCourse($key);
			$numberOfTutorsInCourse = $this->CountTutorsInCourse($context);
			$count += $numberOfTutorsInCourse;
		}
		return $count;
	}
	
	private function CountTutorsInCourse($context) {
		$sql = $sql = "SELECT count(userid) FROM mdl_role_assignments WHERE roleid=4 AND contextid=".$context;
		$result = mssql_query($sql);
		$array = mssql_fetch_array($result);
		return $array[0];
	}
	
	private function GetTeacherOfCourse($course) {
			$context = $this->GetContextIDOfCourse($course);
			
			$sql = "SELECT userid FROM mdl_role_assignments WHERE roleid=3 AND contextid=".$context;
			$result = mssql_query($sql);
			
			while($id = mssql_fetch_array($result)) {
				$teachers_raw[] = $id[0];
			}
			$teachers = "";
			if(isset($teachers_raw)) {
				foreach($teachers_raw as $key => $value) {
					$teacher_id = $value; 
					$teachers .= $this->GetPersonsNameByID($teacher_id)."<br />";
				}		
				return $teachers;
			}
			else {
				return "";
			}
	}
	
	private function CountUsersEnroledByTypeAndCourse($enrolmenttype, $course) {
		$enrolid = $this->GetEnrolIDOfTypeAndCourse($enrolmenttype, $course);
		if($enrolid != -1) {
		$query = "SELECT count(id) FROM mdl_user_enrolments WHERE enrolid=".$enrolid.";";
		$result = mssql_query($query);
		$sum = mssql_fetch_array($result);
		return $sum[0];
		}
		else {
			return 0;
		}
	}
	
	private function CountUsersInCourse($course) {
		$sum = 0;
		$sum += $this->CountUsersEnroledByTypeAndCourse('manual', $course);
		$sum += $this->CountUsersEnroledByTypeAndCourse('guest', $course);
		$sum += $this->CountUsersEnroledByTypeAndCourse('self', $course);
		return $sum;
	
	}
	
	public function ShowCategories($DB, $OUTPUT) {
		// Header <h2>				Text			Level	Class		ID
		echo $OUTPUT->heading("Kategorien", 2);

		//
		//Tabelle aufsetzen
		//
		$table = new html_table();
		//$table->head  = array($hcolumns['timemodified'], $fullnamedisplay, $hcolumns['plugin'], $hcolumns['name'], $hcolumns['value'], $hcolumns['oldvalue']);
		$table->head = array("id", "name", "idnumber", "parent", "visible", "timemodified");
		//$table->colclasses = array('leftalign date', 'leftalign name', 'leftalign plugin', 'leftalign setting', 'leftalign newvalue', 'leftalign originalvalue');
		$table->colclasses = array('leftalign id', 'leftalign idnumber', 'leftalign parent', 'leftalign visible', 'leftalign timemodified');
		$table->id = 'moodleanalyst';
		$table->attributes['class'] = 'admintable generaltable';
		$table->data  = array();

		$kategorien = $DB->get_records('course_categories',null,'','id,name,idnumber,parent,visible,timemodified');

		foreach ($kategorien as $key => $value) {
				$row = array();
				$row[] = $kategorien[$key]->id;
				$row[] = $kategorien[$key]->name;
				$row[] = $kategorien[$key]->idnumber;
				$row[] = $kategorien[$key]->parent;
				$row[] = $kategorien[$key]->visible;
				$row[] = date($this->FormatDatum, $kategorien[$key]->timemodified);
				$table->data[] = $row;
		}
		echo html_writer::table($table);		
	}
	
	public function ShowCourses($DB, $OUTPUT) {
		// #######################
		// Kurse
		// #######################

		// Header				Text			Level	Class		ID
		echo $OUTPUT->heading("Kurse", 2);

		//
		//Tabelle aufsetzen
		//
		$table = new html_table();
		$table->head = array('id','category','shortname','fullname','visible','idnumber','timecreated','timemodified','numsections','startdate','groupmode');
		//$table->colclasses = array('leftalign date', 'leftalign name', 'leftalign plugin', 'leftalign setting', 'leftalign newvalue', 'leftalign originalvalue');
		$table->colclasses = array('leftalign id','leftalign category','leftalign shortname','leftalign fullname','leftalign visible','leftalign idnumber','leftalign timecreated','leftalign timemodified','leftalign numsections','leftalign startdate','leftalign groupmode');
		$table->id = 'moodleanalyst';
		$table->attributes['class'] = 'admintable generaltable';
		$table->data  = array();

		$kategorien = $DB->get_records('course',null,'','id,category,shortname,fullname,visible,idnumber,timecreated,timemodified,numsections,startdate,groupmode');

		foreach ($kategorien as $key => $value) {
				$row = array();
				$row[] = $kategorien[$key]->id;
				$row[] = $kategorien[$key]->category;
				$row[] = $kategorien[$key]->shortname;
				$row[] = $kategorien[$key]->fullname;
				$row[] = $kategorien[$key]->visible;
				$row[] = $kategorien[$key]->idnumber;
				$row[] = date($this->FormatDatum, $kategorien[$key]->timecreated);
				$row[] = date($this->FormatDatum, $kategorien[$key]->timemodified);
				$row[] = $kategorien[$key]->numsections;
				$row[] = date($this->FormatDatum, $kategorien[$key]->startdate);
				$row[] = $kategorien[$key]->groupmode;
				$table->data[] = $row;
		}
		echo html_writer::table($table);
	}
	
	public function ShowGeneralInformation($DB, $OUTPUT) {
			echo $OUTPUT->heading("Allgemeine Informationen", 2);
	
			$table = new html_table();
			//$table->head  = array($hcolumns['timemodified'], $fullnamedisplay, $hcolumns['plugin'], $hcolumns['name'], $hcolumns['value'], $hcolumns['oldvalue']);
			$table->head = array("Name", "Anzahl");
			//$table->colclasses = array('leftalign date', 'leftalign name', 'leftalign plugin', 'leftalign setting', 'leftalign newvalue', 'leftalign originalvalue');
			$table->colclasses = array('leftalign name', 'leftalign value');
			$table->id = 'moodleanalyst';
			$table->attributes['class'] = 'admintable generaltable';
			$table->data  = array();

			// #######################	
			// Nutzer zählen
			// #######################
			$row = array();
			$row[] = "Anzahl Nutzer";
			$row[] = $DB->count_records('user');
			$table->data[] = $row;

			// #######################
			// Dateien zählen
			// #######################
			$row = array();
			$row[] = "Anzahl Dateien";
			$row[] = $DB->count_records('files');
			$table->data[] = $row;
			
			
			/* Test Spile-Statistik KS
			// #######################
			// Anzahl WwM-Spiele
			// #######################
			$row = array();
			$row[] = "Anzahl Millionär-Spiele";
			$query4 = "select count (id) from mdl_game_cryptex;";
			$result4 = mssql_query($query4);
			$var4= mssql_fetch_array($result4);
			$row[] = $var4;
			$table->data[] = $row;
			*/
			
			echo html_writer::table($table);
	}

	public function ShowCoursesWithMostContent($DB, $OUTPUT) {
		echo $OUTPUT->heading("Kurse mit den meisten Inhalten", 2);
		
		$table = new html_table();
		//$table->head  = array($hcolumns['timemodified'], $fullnamedisplay, $hcolumns['plugin'], $hcolumns['name'], $hcolumns['value'], $hcolumns['oldvalue']);
		$table->head = array("Name", "Kurzname", "Anzahl Inhalte");
		//$table->colclasses = array('leftalign date', 'leftalign name', 'leftalign plugin', 'leftalign setting', 'leftalign newvalue', 'leftalign originalvalue');
		$table->colclasses = array('leftalign fullname', 'leftalign shortname', 'leftalign value');
		$table->id = 'moodleanalyst';
		$table->attributes['class'] = 'admintable generaltable';
		$table->data  = array();
		//$res = $DB->get_records_sql('SELECT COUNT(course),course FROM `mdl_course_sections` GROUP BY course');
		$query = "SELECT course,count(course) from mdl_course_modules GROUP BY course ORDER BY count(course) DESC;";
		$result = mssql_query($query);
		while($id = mssql_fetch_assoc($result)) {
				$row = array();
				$row[] = "<a href='https://mdl-alpha.un.hrz.tu-darmstadt.de/course/view.php?id=".$id["course"].">".$this->GetFullnameByID($id["course"])."</a>";
				$row[] = $this->GetShortnameByID($id["course"]);
				$row[] = $id["computed"];
				$table->data[] = $row;
		}
		echo html_writer::table($table);
	}
	
	public function ShowCoursesWithMostForums($DB, $OUTPUT) {
		echo $OUTPUT->heading("Kurse mit den meisten Foren", 2);
		
		$table = new html_table();
		//$table->head  = array($hcolumns['timemodified'], $fullnamedisplay, $hcolumns['plugin'], $hcolumns['name'], $hcolumns['value'], $hcolumns['oldvalue']);
		$table->head = array("Name", "Kurzname", "Anzahl Foren");
		//$table->colclasses = array('leftalign date', 'leftalign name', 'leftalign plugin', 'leftalign setting', 'leftalign newvalue', 'leftalign originalvalue');
		$table->colclasses = array('leftalign fullname', 'leftalign shortname', 'leftalign value');
		$table->id = 'moodleanalyst';
		$table->attributes['class'] = 'admintable generaltable';
		$table->data  = array();
		//$res = $DB->get_records_sql('SELECT COUNT(course),course FROM `mdl_course_sections` GROUP BY course');
		$query = "SELECT course,count(course) from mdl_course_modules where module=9 GROUP BY course ORDER BY count(course) DESC;";
		$result = mssql_query($query);
		while($id = mssql_fetch_assoc($result)) {
				$row = array();
				$row[] = "<a href='https://mdl-alpha.un.hrz.tu-darmstadt.de/course/view.php?id=".$id["course"].">".$this->GetFullnameByID($id["course"])."</a>";
				$row[] = $this->GetShortnameByID($id["course"]);
				$row[] = $id["computed"];
				$table->data[] = $row;
		}
		echo html_writer::table($table);
	}
	
	public function CountCoursesByCourseFormat($OUTPUT) {
		echo $OUTPUT->heading("Kurs Formate", 2);
		
		$table = new html_table();
		$table->head = array("Kursformat", "Anzahl");
		$table->colclasses = array('leftalign formatname', 'leftalign value');
		$table->id = 'moodleanalyst';
		$table->attributes['class'] = 'admintable generaltable';
		$table->data  = array();
		$query = "SELECT format, Count(format) from mdl_course GROUP BY format;";
		$result = mssql_query($query);
		while($id = mssql_fetch_assoc($result)) {
				$row = array();
				$row[] = $id["format"];
				$row[] = $id["computed"];
				$table->data[] = $row;
		}
		echo html_writer::table($table);	
	}
	
	public function ShowAdobeConnectSettings($OUTPUT) {
		echo $OUTPUT->heading("Kurs Formate", 2);
		
		$table = new html_table();
		$table->head = array("Kursformat", "Anzahl");
		$table->colclasses = array('leftalign formatname', 'leftalign value');
		$table->id = 'moodleanalyst';
		$table->attributes['class'] = 'admintable generaltable';
		$table->data  = array();
		$query = "select name, value from  mdl_config where name like 'adobeconnect%';";
		$result = mssql_query($query);
		while($id = mssql_fetch_assoc($result)) {
				$row = array();
				$row[] = $id["name"];
				$row[] = "'".$id["value"]."'";
				$table->data[] = $row;
		}
		echo html_writer::table($table);	
	}
	
	public function ShowGames($OUTPUT) {
		$query = "select count (id) from mdl_game_cryptex;";
		$result = mssql_query($query);
		$sumC = mssql_fetch_array($result);
		//echo "<pre>";
		print "Anzahl cryptex: ";
		print_r($sumC[0]);
		print "<br>";
		$query = "select count (id) from mdl_game_millionaire;";
		$result = mssql_query($query);
		$sumC = mssql_fetch_array($result);
		//echo "<pre>";
		print "Anzahl Millionär: ";
		print_r($sumC[0]);
		print "<br>";
		$query = "select count (id) from mdl_game_bookquiz;";
		$result = mssql_query($query);
		$sumC = mssql_fetch_array($result);
		//echo "<pre>";
		print "Anzahl bookquiz: ";
		print_r($sumC[0]);
		print "<br>";
		$query = "select count (id) from mdl_game_cross;";
		$result = mssql_query($query);
		$sumC = mssql_fetch_array($result);
		//echo "<pre>";
		print "Anzahl Kreuzworträtsel: ";
		print_r($sumC[0]);
		print "<br>";
		$query = "select count (id) from mdl_game_hangman;";
		$result = mssql_query($query);
		$sumC = mssql_fetch_array($result);
		//echo "<pre>";
		print "Anzahl hangman: ";
		print_r($sumC[0]);
		print "<br>";
		$query = "select count (id) from mdl_game_hiddenpicture;";
		$result = mssql_query($query);
		$sumC = mssql_fetch_array($result);
		//echo "<pre>";
		print "Anzahl hiddenpicture: ";
		print_r($sumC[0]);
		print "<br>";
		$query = "select count (id) from mdl_game_snakes;";
		$result = mssql_query($query);
		$sumC = mssql_fetch_array($result);
		//echo "<pre>";
		print "Anzahl Schlangen und Leitern: ";
		print_r($sumC[0]);
		print "<br>";
		$query = "select count (id) from mdl_game_sudoku;";
		$result = mssql_query($query);
		$sumC = mssql_fetch_array($result);
		//echo "<pre>";
		print "Anzahl Sudoku: ";
		print_r($sumC[0]);
		print "<br>";
	}
	
	public function ShowCategoryFiles($OUTPUT) {
		echo $OUTPUT->heading("Kategorie: Dateien", 2);
		
		$idOfFiles = $this->GetIdOfModule('resource');
		echo $OUTPUT->heading("Modul 'resource' hat ID ".$idOfFiles, 2);
		echo $OUTPUT->heading("Verzeichnisse stehen in Tabelle mdl_folder - in Liste zuerst n Verzeichnisse, dann Dateien", 2);

		$table = new html_table();
		$table->head = array("ID", "Kursname", "Lehrende", "Semester", "FB", "Teilnehmerzahl", "Anzahl Verzeichnisse", "Anzahl Dateien");
		//$table->colclasses = array('leftalign formatname', 'leftalign value');
		$table->id = 'moodleanalyst';
		$table->attributes['class'] = 'admintable generaltable';
		$table->data  = array();
		
		
		$courses = $this->GetAllCourseIDs();
		foreach($courses as $key => $value) {
			$folder = $this->GetSumOfFoldersByCourseID($courses[$key]['id']);
			$file = $this->GetSumOfModuleinstancesByCourseID('resource', $courses[$key]['id']);


			if($folder > 0 || $file > 0) {	
				$row = array();
				$row[] = $courses[$key]['id'];
				$row[] = $this->GetFullnameByID($courses[$key]['id']);
				$row[] = $this->GetTeacherOfCourse($courses[$key]['id']); //Lehrende
				$row[] = $this->GetSemesterOfCourse($courses[$key]['id']); // Semester
				$row[] = $this->GetFBOfCourse($courses[$key]['id']); // FB
				$row[] = $this->CountUsersInCourse($courses[$key]['id']); //Anzahl Nutzer
				$row[] = $folder;
				$row[] = $file;
				$table->data[] = $row;
			}
		}
		echo html_writer::table($table);
		/* KS: Zeigt Anzahl aller Verzeichnisse und Dateien: 
		"SELECT
(SELECT count(mdl_folder.course) AS anzahl_A FROM mdl_folder) as Verzeichnisse, 
(SELECT count(mdl_course_modules.course) AS anzahl_B FROM mdl_course_modules WHERE module=19) as Dateien;"; */
	}
	
	public function ShowCategoryCommunication($OUTPUT) {
		
		echo $OUTPUT->heading("Kategorie: Kommunikation", 2);
		$table = new html_table();
		$table->head = array("ID", "Kursname", "Lehrende", "Semester", "FB", "Teilnehmerzahl", "Foren", "Chats");
		//$table->colclasses = array('leftalign formatname', 'leftalign value');
		$table->id = 'moodleanalyst';
		$table->attributes['class'] = 'admintable generaltable';
		$table->data  = array();
		
		$forum_id = $this->GetIdOfModule('forum');
		$chat_id = $this->GetIdOfModule('chat');

		
		$sql = "SELECT course, count(module) AS anzahl, module FROM mdl_course_modules WHERE module=".$forum_id." OR module=".$chat_id." GROUP BY module, course";
		$result = mssql_query($sql);
		
		while($res = mssql_fetch_array($result)) {
			$array[] = $res;
		}
		
		foreach($array as $key => $value) {
			$course[$array[$key]['course']][$array[$key]['module']] = $array[$key]['anzahl'];
		}
						
		foreach($course as $key => $value) {
			if(!isset($course[$key][$forum_id])) {
				$course[$key][$forum_id] = 0;
			}
			if(!isset($course[$key][$chat_id])) {
				$course[$key][$chat_id] = 0;
			}
			$id = $key;
			$row = array();
			$row[] = $id;
			$row[] = $this->GetFullnameByID($id);
			$row[] = $this->GetTeacherOfCourse($id); //Lehrende
			$row[] = $this->GetSemesterOfCourse($id); // Semester
			$row[] = $this->GetFBOfCourse($id); // FB
			$row[] = $this->CountUsersInCourse($id); //Anzahl Nutzer
			$row[] = $course[$key][$forum_id]; //forum = Abstimmung
			$row[] = $course[$key][$chat_id]; //chat
			$table->data[] = $row;
		}
		echo html_writer::table($table);
		
		
	}
	
	public function ShowCategoryTest($OUTPUT) {
		echo $OUTPUT->heading("Kategorie: (Selbst-)Überprüfung", 2);
		$table = new html_table();
		$table->head = array("ID", "Kursname", "Lehrende", "Semester", "FB", "Teilnehmerzahl", "E-Tests", "Aufgaben", "HotPot", "Lektion", "Spiele");
		//$table->colclasses = array('leftalign formatname', 'leftalign value');
		$table->id = 'moodleanalyst';
		$table->attributes['class'] = 'admintable generaltable';
		$table->data  = array();
		
		$quiz_id = $this->GetIdOfModule('quiz');
		$assign_id = $this->GetIdOfModule('assign');
		$hotpot_id = $this->GetIdOfModule('hotpot');
		$lesson_id = $this->GetIdOfModule('lesson');
		$game_id = $this->GetIdOfModule('game');
		
		$sql = "SELECT course, count(module) AS anzahl, module FROM mdl_course_modules WHERE module=".$quiz_id." OR module=".$assign_id." OR module=".$hotpot_id." OR module=".$lesson_id." OR module=".$game_id." GROUP BY module, course";
		$result = mssql_query($sql);
		
		while($res = mssql_fetch_array($result)) {
			$array[] = $res;
		}
		
		foreach($array as $key => $value) {
			$course[$array[$key]['course']][$array[$key]['module']] = $array[$key]['anzahl'];
		}
						
		foreach($course as $key => $value) {
			if(!isset($course[$key][$quiz_id])) {
				$course[$key][$quiz_id] = 0;
			}
			if(!isset($course[$key][$assign_id])) {
				$course[$key][$assign_id] = 0;
			}
			if(!isset($course[$key][$hotpot_id])) {
				$course[$key][$hotpot_id] = 0;
			}
			if(!isset($course[$key][$lesson_id])) {
				$course[$key][$lesson_id] = 0;
			}
			if(!isset($course[$key][$game_id])) {
				$course[$key][$game_id] = 0;
			}
			$id = $key;
			$row = array();
			$row[] = $id;
			$row[] = $this->GetFullnameByID($id);
			$row[] = $this->GetTeacherOfCourse($id); //Lehrende
			$row[] = $this->GetSemesterOfCourse($id); // Semester
			$row[] = $this->GetFBOfCourse($id); // FB
			$row[] = $this->CountUsersInCourse($id); //Anzahl Nutzer
			$row[] = $course[$key][$quiz_id]; //choice = Abstimmung
			$row[] = $course[$key][$assign_id]; //feedback
			$row[] = $course[$key][$hotpot_id]; //hotquestion = Nachgefragt
			$row[] = $course[$key][$lesson_id]; //hotquestion = Nachgefragt
			$row[] = $course[$key][$game_id]; //hotquestion = Nachgefragt
			$table->data[] = $row;
		}
		echo html_writer::table($table);
	}
	
	public function ShowCategoryCooperation($OUTPUT) {
		echo $OUTPUT->heading("Kategorie: Kooperation", 2);
		
		$idOfWiki = $this->GetIdOfModule('wiki');
		$idOfData = $this->GetIdOfModule('data');
		$idOfGlossary = $this->GetIdOfModule('glossary');
		$idOfWorkshop = $this->GetIdOfModule('workshop');
		
		$table = new html_table();
		$table->head = array("ID", "Kursname", "Lehrende", "Semester", "FB", "Teilnehmerzahl", "Wikis", "Datenbanken", "Glossare", "Workshop");
		//$table->colclasses = array('leftalign formatname', 'leftalign value');
		$table->id = 'moodleanalyst';
		$table->attributes['class'] = 'admintable generaltable';
		$table->data  = array();
		
		
		$wiki_id = $this->GetIdOfModule('wiki');
		$data_id = $this->GetIdOfModule('data');
		$glossary_id = $this->GetIdOfModule('glossary');
		$workshop_id = $this->GetIdOfModule('workshop');
		
		$sql = "SELECT course, count(module) AS anzahl, module FROM mdl_course_modules WHERE module=".$wiki_id." OR module=".$data_id." OR module=".$glossary_id." OR module=".$workshop_id." GROUP BY module, course";
		$result = mssql_query($sql);
		
		while($res = mssql_fetch_array($result)) {
			$array[] = $res;
		}
		
		foreach($array as $key => $value) {
			$course[$array[$key]['course']][$array[$key]['module']] = $array[$key]['anzahl'];
		}
						
		foreach($course as $key => $value) {
			if(!isset($course[$key][$wiki_id])) {
				$course[$key][$wiki_id] = 0;
			}
			if(!isset($course[$key][$data_id])) {
				$course[$key][$data_id] = 0;
			}
			if(!isset($course[$key][$glossary_id])) {
				$course[$key][$glossary_id] = 0;
			}
			if(!isset($course[$key][$workshop_id])) {
				$course[$key][$workshop_id] = 0;
			}
			$id = $key;
			$row = array();
			$row[] = $id;
			$row[] = $this->GetFullnameByID($id);
			$row[] = $this->GetTeacherOfCourse($id); //Lehrende
			$row[] = $this->GetSemesterOfCourse($id); // Semester
			$row[] = $this->GetFBOfCourse($id); // FB
			$row[] = $this->CountUsersInCourse($id); //Anzahl Nutzer
			$row[] = $course[$key][$wiki_id]; //choice = Abstimmung
			$row[] = $course[$key][$data_id]; //feedback
			$row[] = $course[$key][$glossary_id]; //hotquestion = Nachgefragt
			$row[] = $course[$key][$workshop_id]; //hotquestion = Nachgefragt
			$table->data[] = $row;
		}
		echo html_writer::table($table);
		
	}
	
	public function ShowCategoryCourseOrganisation($OUTPUT) {
		echo $OUTPUT->heading("Kategorie: Lehrorganisation", 2);
		
		echo $OUTPUT->heading("Gruppen stehen in der Tabelle mdl_groups", 2);
		$table = new html_table();
		$table->head = array("ID", "Kursname", "Lehrende", "Semester", "FB", "Teilnehmerzahl", "Gruppen");
		//$table->colclasses = array('leftalign formatname', 'leftalign value');
		$table->id = 'moodleanalyst';
		$table->attributes['class'] = 'admintable generaltable';
		$table->data  = array();
		
		
		$query = "SELECT courseid, count(courseid) AS anzahl FROM mdl_groups GROUP BY courseid ORDER BY count(courseid) DESC;";
		$result = mssql_query($query);
		while($id = mssql_fetch_assoc($result)) {
				$row = array();
				$row[] = $id["courseid"];
				$row[] = $this->GetFullnameByID($id["courseid"]);
				$row[] = "nn"; //Lehrende
				$row[] = $this->GetSemesterOfCourse($id["courseid"]); // Semester
				$row[] = $this->GetFBOfCourse($id["courseid"]); // FB
				$row[] = $this->CountUsersInCourse($id["courseid"]); //Anzahl Nutzer
				$row[] = $id['anzahl']; //gruppen
				$table->data[] = $row;
		}
		echo html_writer::table($table);
		
	}
	
	public function ShowCategoryFeedback($OUTPUT) {
		echo $OUTPUT->heading("Kategorie: Rückmeldungen", 2);
		$table = new html_table();
		$table->head = array("ID", "Kursname", "Lehrende", "Semester", "FB", "Teilnehmerzahl", "Abstimmung", "Feedback", "Nachgefragt");
		//$table->colclasses = array('leftalign formatname', 'leftalign value');
		$table->id = 'moodleanalyst';
		$table->attributes['class'] = 'admintable generaltable';
		$table->data  = array();
		
		$choice_id = $this->GetIdOfModule('choice');
		$feedback_id = $this->GetIdOfModule('feedback');
		$hotquestion_id = $this->GetIdOfModule('hotquestion');
		
		$sql = "SELECT course, count(module) AS anzahl, module FROM mdl_course_modules WHERE module=".$choice_id." OR module=".$feedback_id." OR module=".$hotquestion_id." GROUP BY module, course";
		$result = mssql_query($sql);
		
		while($res = mssql_fetch_array($result)) {
			$array[] = $res;
		}
		
		foreach($array as $key => $value) {
			$course[$array[$key]['course']][$array[$key]['module']] = $array[$key]['anzahl'];
		}
						
		foreach($course as $key => $value) {
			if(!isset($course[$key][$choice_id])) {
				$course[$key][$choice_id] = 0;
			}
			if(!isset($course[$key][$feedback_id])) {
				$course[$key][$feedback_id] = 0;
			}
			if(!isset($course[$key][$hotquestion_id])) {
				$course[$key][$hotquestion_id] = 0;
			}
			$id = $key;
			$row = array();
			$row[] = $id;
			$row[] = $this->GetFullnameByID($id);
			$row[] = $this->GetTeacherOfCourse($id); //Lehrende
			$row[] = $this->GetSemesterOfCourse($id); // Semester
			$row[] = $this->GetFBOfCourse($id); // FB
			$row[] = $this->CountUsersInCourse($id); //Anzahl Nutzer
			$row[] = $course[$key][$choice_id]; //choice = Abstimmung
			$row[] = $course[$key][$feedback_id]; //feedback
			$row[] = $course[$key][$hotquestion_id]; //hotquestion = Nachgefragt
			$table->data[] = $row;
		}
		echo html_writer::table($table);
		
	}
	
	public function ShowCoursesOrderedByAmountOfUsersDESC() {
		GLOBAL $OUTPUT, $DB;
		echo $OUTPUT->heading("Course nach Anzahl der Benutzer", 2);
		$table = new html_table();
		$table->head = array("ID", "Kursname", "Anzahl Nutzer");
		//$table->colclasses = array('leftalign formatname', 'leftalign value');
		
		$table->id = 'moodleanalyst';
		$table->attributes['class'] = 'admintable generaltable';
		$table->data  = array();
		
		
		
		$query = "SELECT TOP (50) mdl_enrol.courseid, count(mdl_enrol.courseid) AS anzahl FROM mdl_user_enrolments, mdl_enrol WHERE mdl_enrol.id = mdl_user_enrolments.enrolid GROUP BY mdl_enrol.courseid ORDER BY anzahl DESC;";
		$array = $DB->get_records_sql($query);
		
		foreach($array as $key => $value) {
			$row = array();
			$row[] = $array[$key]->courseid;
			$row[] = $this->GetFullnameByID($array[$key]->courseid);
			$row[] = $array[$key]->anzahl;
			$table->data[] = $row;
		}
		
		
		echo html_writer::table($table);
		
	}
	
	public function ContentsOfCoursesInCategory($OUTPUT) {
		$query = "select id, name, description from mdl_course_categories;";
		$result = mssql_query($query);
		while($id = mssql_fetch_assoc($result)) {
				echo $OUTPUT->heading($id['name'], 2);
				echo $OUTPUT->heading($id['description'], 6);
				
				$query2 = "select id, fullname, shortname, summary from mdl_course WHERE category = ".$id['id'].";";
				$result2 = mssql_query($query2);
				
				while($id2 = mssql_fetch_assoc($result2)) {
						$table2 = new html_table();
						$table2->id = 'moodleanalyst';
						$table2->attributes['class'] = 'admintable generaltable';
						$table2->data  = array();
						$row = array();
						$row[] = "<b>Fullname: </B>";
						$row[] = $id2['fullname'];
						$table2->data[] = $row;
						unset($row);
						$row = array();
						$row[] = "<b>Shortname: </B>";
						$row[] = $id2['shortname'];
						$table2->data[] = $row;
						unset($row);
						$row = array();
						$row[] = "<b>ID: </B>";
						$row[] = $id2['id'];
						$table2->data[] = $row;
						unset($row);
						$query3 = "SELECT module, count(module) as counted FROM mdl_course_modules WHERE course=".$id2['id']." GROUP BY module, course ORDER BY counted DESC;";
						$result3 = mssql_query($query3);
						
						while($id3 = mssql_fetch_assoc($result3)) {
							$row = array();
							$row[] = $this->GetModuleNameById($id3['module']).": ";
							$row[] = $id3['counted'];
							$table2->data[] = $row;
							if($id3['module'] == "9") {
								unset($row);
								$row = array();
								$row[] = " - Themen (insgesamt):";
								$row[] = $this->GetSumOfDiscussionsInCourse($id2['id']);
								$table2->data[] = $row;
								unset($row);
								$row = array();
								$row[] = " - Beiträge (insgesamt):";
								$row[] = $this->GetSumOfPostsInCourse($id2['id']);
								$table2->data[] = $row;								
							}
							
						}
						
						
						echo html_writer::table($table2);
				}
				
				
				
		}
	}
	
	private function GetSemesterOfChildInAnyLevel($child_id) {
		$sql = "SELECT parent FROM mdl_course_categories WHERE id=".$child_id;
		$res = mssql_query($sql);
		$result = mssql_fetch_assoc($res);
		$result = $result['parent'];
		if($result == 0) {
			return $child_id;
		}
		else {
			return $this->GetSemesterOfChildInAnyLevel($result);
		}
	}
	
	private function BuildCategoryHierarchy($StartCategoryID = 0) {
		GLOBAL $DB;
		$categories = $DB->get_records('course_categories', array("parent" => $StartCategoryID),'id ASC', 'id');
		foreach ($categories as $key => $value) {
			$categories[$key] = $this->BuildCategoryHierarchy($key);
		}
		return $categories;
	} 
	
	
	
	private function PopulateCategoryHierarchyWithInformations($hierarchy, $infos, $category_id = 0) {
		GLOBAL $DB;
		$gesamt = 0;
		$schnittstellenkurse = 0;
		$manuell = 0;
		
		//Kurse, die direkt in Kategorie liegen
		$tmp_gesamt = $DB->count_records('course', array('category' => $category_id));				
		$tmp_manuell = $DB->count_records('course', array('category' => $category_id, 'idnumber' => ''));
		$tmp_schnittstellenkurse = $tmp_gesamt - $tmp_manuell;
		
		$gesamt = $gesamt + $tmp_gesamt;
		$schnittstellenkurse = $schnittstellenkurse + $tmp_schnittstellenkurse;
		$manuell = $manuell + $tmp_manuell;
		
		if(!empty($hierarchy)) {
			foreach ($hierarchy as $key => $value) {
				$infos = $this->PopulateCategoryHierarchyWithInformations($value, $infos, $key);
				$gesamt = $gesamt + $infos[$key]->gesamt;
				$schnittstellenkurse = $schnittstellenkurse + $infos[$key]->schnittstellenkurse;
				$manuell = $manuell + $infos[$key]->manuell;
			}
		}
		
		$infos[$category_id]->gesamt = $gesamt;
		$infos[$category_id]->schnittstellenkurse = $schnittstellenkurse;
		$infos[$category_id]->manuell = $manuell;
		return $infos;
	}
	
	private function BuildCategoryTable($categories, $infos) {
		$html = "<table>";
		foreach ($categories as $key => $value) {
			if($key =='infos') {
				continue;
			}
			$html .= "<tr>";
			$html .= '<td align="left"   valign="top" ><b>';
			$html .= $this->GetCategoryName($key)." (#".$key.")";
			$html .= "</B><br />
					<table>
					<tr>
						<td>Schnittstelle</td><td>+</td><td>Manuell</td><td>=</td><td>Gesamt</td>
					</tr>
					<tr>
						<td>".$infos[$key]->schnittstellenkurse."</td><td>+</td><td>".$infos[$key]->manuell."</td><td>=</td><td>".$infos[$key]->gesamt."</td>
					</tr>
					</table>
					</td>";
			$html .= '<td align="left"   valign="top" >';
			$html .= '
					<canvas id="'.$key.'" width="100" height="100"></canvas>
					<script>
						var ctx = document.getElementById("'.$key.'").getContext("2d");
						var data = [
							{
								value: '.$infos[$key]->schnittstellenkurse.',
								color:"#B1BD00"
							},
							{
								value : '.$infos[$key]->manuell.',
								color : "#F5A300"
							}			
						]
						var options = {
							//Boolean - Whether we should show a stroke on each segment
							segmentShowStroke : true,
							
							//String - The colour of each segment stroke
							segmentStrokeColor : "#fff",
							
							//Number - The width of each segment stroke
							segmentStrokeWidth : 2,
							
							//Boolean - Whether we should animate the chart	
							animation : false,
							
							//Number - Amount of animation steps
							animationSteps : 100,
							
							//String - Animation easing effect
							animationEasing : "easeOutBounce",
							
							//Boolean - Whether we animate the rotation of the Pie
							animateRotate : false,
						
							//Boolean - Whether we animate scaling the Pie from the centre
							animateScale : false,
							
							//Function - Will fire on animation completion.
							onAnimationComplete : null
						}
						new Chart(ctx).Pie(data,options);
					</script>
					';
			$html .= "</td>";
			if(!empty($value)) {
				$html .= '<td align="left"   valign="top" >';
				$html .= $this->BuildCategoryTable($value, $infos);
				$html .= "</td>";
			}
			$html .= "</tr>";
		}
		$html .= "</table>";
		return $html;
	}
	
	private function CreatePieDiagrammOfCategoriesWithParent($parent_id) {
		GLOBAL $DB;
		$categories = $this->BuildCategoryHierarchy();
		$infos = $DB->get_records('course_categories');
		
		$html = '
					<script>
						var ctx = document.getElementById("canvas").getContext("2d");
						var data = [
							{
								value: '.$infos[$parent_id]->schnittstellenkurse.',
								color:"#B1BD00"
							},
							{
								value : '.$infos[$parent_id]->manuell.',
								color : "#F5A300"
							}			
						]
						var options = {
							//Boolean - Whether we should show a stroke on each segment
							segmentShowStroke : true,
							
							//String - The colour of each segment stroke
							segmentStrokeColor : "#fff",
							
							//Number - The width of each segment stroke
							segmentStrokeWidth : 2,
							
							//Boolean - Whether we should animate the chart	
							animation : false,
							
							//Number - Amount of animation steps
							animationSteps : 100,
							
							//String - Animation easing effect
							animationEasing : "easeOutBounce",
							
							//Boolean - Whether we animate the rotation of the Pie
							animateRotate : false,
						
							//Boolean - Whether we animate scaling the Pie from the centre
							animateScale : false,
							
							//Function - Will fire on animation completion.
							onAnimationComplete : null
						}
						new Chart(ctx).Pie(data,options);
					</script>
					';
		return $html;
	}
	
	private function Pie($elementName, $arrayOfData) {
		$html = "
					<script>
						var ctx = document.getElementById('".$elementName."').getContext('2d');
						var data = [";
		foreach ($arrayOfData as $key => $value) {
			$html .= "{
								value: $value,
								
							},";
										//color:"#B1BD00"
		}
		$html .= '
						]
						var options = {
							//Boolean - Whether we should show a stroke on each segment
							segmentShowStroke : true,
				
							//String - The colour of each segment stroke
							segmentStrokeColor : "#fff",
				
							//Number - The width of each segment stroke
							segmentStrokeWidth : 2,
				
							//Boolean - Whether we should animate the chart
							animation : false,
				
							//Number - Amount of animation steps
							animationSteps : 100,
				
							//String - Animation easing effect
							animationEasing : "easeOutBounce",
				
							//Boolean - Whether we animate the rotation of the Pie
							animateRotate : false,
		
							//Boolean - Whether we animate scaling the Pie from the centre
							animateScale : false,
				
							//Function - Will fire on animation completion.
							onAnimationComplete : null
						}
						new Chart(ctx).Pie(data,options);
					</script>
					';
		return $html;
	}
	
	private function CreateTable($array) {
		$table = "<table>";
		$table .= "
				<tr>
				<th>ID</th>
				<th>Name</th>
				<th>Manuell</th>
				<th>Schnittstelle</th>
				<th>Gesamt</th>
				</tr>";
		foreach ($array as $key => $value) {
			$table .= "<tr>
						<td>$value</td>
						<td>".$this->GetCategoryName($value, false)."</td>
					</tr>
					";
		}
		$table .= "</table>";
		$table = str_replace(array("\n", "\r"), "", $table);
		
		$html = "
		<script src='https://code.jquery.com/jquery-2.1.0.min.js'></script>
				<script>
		var table = '$table';
		$( '#tabelle1' ).html(table);
</script>";
		return $html;
	}
	
	private function CreateSkriptForCharts($categories, $infos, $parent) {
		$CountCourses = array();
		if($parent == 0) {
			foreach ($categories as $key => $value) {
				$CountCourses[] = $infos[$key]->gesamt;
			}
		}
		else {
			foreach ($categories[$parent] as $key => $value) {
				$CountCourses[] = $infos[$key]->gesamt;
			}
		}
		
		$SchnittstellenUndManuell = array($infos[$parent]->manuell, $infos[$parent]->schnittstellenkurse);
		
		
		
		$html = $this->Pie("canvas1", $CountCourses);
		$html .= $this->CreateTable($CountCourses);
		$html .= $this->Pie("canvas2", $SchnittstellenUndManuell);
		return $html;
	}
	
	private function BuildSchnittstellenGUI($categories, $infos) {
		$html = '<script src="/report/moodleanalyst/Chart.js"></script>';
		$html .= '<script src="https://code.jquery.com/jquery-2.1.0.min.js"></script>';
		
		
		
		$html .= "<div id='interface'>
				<table>
				<tr>
					<td>
						<canvas id='canvas1' width='200' height='200'></canvas>
					</td>
					<td>
						<canvas id='canvas2' width='200' height='200'></canvas>
					</td>
					<td>
						<div id='tabelle1'>Blabla</div>
					</td>
					
				</tr>
				</table>
				</div>";
		$html .= $this->CreateSkriptForCharts($categories, $infos, 0);
		return $html;
	}
	
	public function Gruppierungen() {
		GLOBAL $OUTPUT, $DB;
		
		$result = $DB->get_records_sql('SELECT courseid, {course}.fullname, {course}.shortname, count(courseid) AS CountGroupings FROM {groupings}, {course} WHERE {course}.id = {groupings}.courseid GROUP BY {groupings}.courseid, {course}.fullname, {course}.shortname ORDER BY CountGroupings DESC');
		
		$table = new html_table();
		$table->head = array("ID", "Kurs", "Gruppierungen");
		
		$gesamtZahl = 0;
		
		foreach($result as $key => $value) {
			$row = array();
			$row[] = $value->courseid;
			$row[] = "<a href=/group/groupings.php?id=".$value->courseid.">".$value->fullname."</a>";
			$row[] = $value->countgroupings;
			$gesamtZahl += $value->countgroupings;
			$table->data[] = $row;
		}
		echo $OUTPUT->heading("Gruppierungen: ".$gesamtZahl, 2);
		echo html_writer::table($table);
	
	}
		
	public function Schnittstelle($OUTPUT) {
		GLOBAL $DB, $CFG, $OUTPUT;
		echo $OUTPUT->heading("Schnittstelle", 2);
		
		$categories = $this->BuildCategoryHierarchy();
		$allCategories = $DB->get_records('course_categories');
		$infos = $this->PopulateCategoryHierarchyWithInformations($categories, $allCategories);
		echo '<script src="/report/moodleanalyst/Chart.js"></script>';
		
		$labels = "";
		foreach ($categories as $key => $value) {
			$labels .= '"'.$this->GetCategoryName($key, false).'",';
		}
		$labels = substr($labels, 0, strlen($labels)-1);
		
		$data_manuell = "";
		foreach ($categories as $key => $value) {
			$data_manuell .= $infos[$key]->manuell.',';
		}
		$data_manuell = substr($data_manuell, 0, strlen($data_manuell)-1);
		
		$data_schnitt = "";
		foreach ($categories as $key => $value) {
			$data_schnitt .= $infos[$key]->schnittstellenkurse.',';
		}
		$data_schnitt = substr($data_schnitt, 0, strlen($data_schnitt)-1);
		$data_sum = $data_manuell + $data_schnitt;
		
		
		echo ' 
					<canvas id="linechart" width="1000" height="600"></canvas>
					<script>
						var ctx = document.getElementById("linechart").getContext("2d");
						var data = {
							labels : ['.$labels.'],
							datasets : [
								{
									fillColor : "rgba(245,163,0,0.5)",
									strokeColor : "rgba(220,220,220,1)",
									pointColor : "rgba(220,220,220,1)",
									pointStrokeColor : "#fff",
									data : ['.$data_manuell.']
								},
								{
									fillColor : "rgba(177,189,0,0.5)",
									strokeColor : "rgba(151,187,205,1)",
									pointColor : "rgba(151,187,205,1)",
									pointStrokeColor : "#fff",
									data : ['.$data_schnitt.']
								},
								{
									fillColor : "rgba(245,245,245,0.5)",
									strokeColor : "rgba(104,104,104,1)",
									pointColor : "rgba(104,104,104,1)",
									pointStrokeColor : "#fff",
									data : ['.$data_sum.']
								}
							]
						}
						var options = {			
							//Boolean - If we show the scale above the chart data			
							scaleOverlay : false,
							
							//Boolean - If we want to override with a hard coded scale
							scaleOverride : false,
							
							//** Required if scaleOverride is true **
							//Number - The number of steps in a hard coded scale
							scaleSteps : null,
							//Number - The value jump in the hard coded scale
							scaleStepWidth : null,
							//Number - The scale starting value
							scaleStartValue : null,
						
							//String - Colour of the scale line	
							scaleLineColor : "rgba(0,0,0,.1)",
							
							//Number - Pixel width of the scale line	
							scaleLineWidth : 1,
						
							//Boolean - Whether to show labels on the scale	
							scaleShowLabels : true,
							
							//Interpolated JS string - can access value
							scaleLabel : "<%=value%>",
							
							//String - Scale label font declaration for the scale label
							scaleFontFamily : "\'Arial\'",
							
							//Number - Scale label font size in pixels	
							scaleFontSize : 12,
							
							//String - Scale label font weight style	
							scaleFontStyle : "normal",
							
							//String - Scale label font colour	
							scaleFontColor : "#666",	
							
							///Boolean - Whether grid lines are shown across the chart
							scaleShowGridLines : true,
							
							//String - Colour of the grid lines
							scaleGridLineColor : "rgba(0,0,0,.05)",
							
							//Number - Width of the grid lines
							scaleGridLineWidth : 1,	
							
							//Boolean - Whether the line is curved between points
							bezierCurve : true,
							
							//Boolean - Whether to show a dot for each point
							pointDot : true,
							
							//Number - Radius of each point dot in pixels
							pointDotRadius : 3,
							
							//Number - Pixel width of point dot stroke
							pointDotStrokeWidth : 1,
							
							//Boolean - Whether to show a stroke for datasets
							datasetStroke : true,
							
							//Number - Pixel width of dataset stroke
							datasetStrokeWidth : 2,
							
							//Boolean - Whether to fill the dataset with a colour
							datasetFill : true,
							
							//Boolean - Whether to animate the chart
							animation : true,
						
							//Number - Number of animation steps
							animationSteps : 60,
							
							//String - Animation easing effect
							animationEasing : "easeOutQuart",
						
							//Function - Fires when the animation is complete
							onAnimationComplete : null
							
						}
						new Chart(ctx).Line(data,options);
					</script>
		';
		
		
		echo $this->BuildCategoryTable($categories, $infos);
		
		//echo $this->BuildSchnittstellenGUI($categories, $infos);
		
	}
	
	public function Helios($OUTPUT) {
		$query = "SELECT count(course) as anzahl FROM mdl_url WHERE externalurl LIKE '%moodleload.hrz.tu-darmstadt.de%'  GROUP BY parameters";
		$result = mssql_query($query);
		$res_array = mssql_fetch_array($result);
		$anzahl = $res_array[0];
	
		echo $OUTPUT->heading("Helios-Statistik", 2);
		echo $OUTPUT->heading("Links gesamt: ".$anzahl, 4);
		$table = new html_table();
		$table->head = array("ID", "Semester", "FB", "Kursname", "Lehrende", "Anzahl Helios-Links");
		
		$query = "SELECT course, COUNT(course) as anzahl FROM mdl_url WHERE externalurl LIKE '%moodleload.hrz.tu-darmstadt.de%'  GROUP BY course ORDER BY anzahl DESC;";
		$result = mssql_query($query);
		while($res = mssql_fetch_assoc($result)) {
			$array[] = $res;
		}
		
		foreach($array as $key => $value) {
			$id = $array[$key]['course'];
			$row = array();
			$row[] = $id;
			$row[] = $this->GetSemesterOfCourse($id, $asLink = true);
			$row[] = $this->GetFBOfCourse($id);
			$row[] = $this->GetFullnameByID($id);
			$row[] = $this->GetTeacherOfCourse($id);
			$row[] = $array[$key]['anzahl'];
			$table->data[] = $row;
		}
		
		echo html_writer::table($table);
	}
	
	public function LeereKurse($OUTPUT) {
		$query = "SELECT course,COUNT(course) AS anzahl FROM mdl_course_modules GROUP BY course HAVING COUNT(course) = 1";
		$result = mssql_query($query);
		while($res = mssql_fetch_assoc($result)) {
			$array[] = $res;
		}
		
		$anzahl = count($array);
		
		echo $OUTPUT->heading("Leere Kurse - Anzahl: ".$anzahl, 2);
		$table = new html_table();
		$table->head = array("ID", "Semester", "FB", "Kursname", "Lehrende");
		
		
		
		foreach($array as $key => $value) {
			$id = $array[$key]['course'];
			$row = array();
			$row[] = $id;
			$row[] = $this->GetSemesterOfCourse($id, $asLink = true);
			$row[] = $this->GetFBOfCourse($id);
			$row[] = $this->GetFullnameByID($id);
			$row[] = $this->GetTeacherOfCourse($id);
			$table->data[] = $row;
		}
		
		echo html_writer::table($table);
	}

	public function Tutoren($OUTPUT) {
		$table = new html_table();
		
		$table->head = array("Semester", "FB", "Kurse", "Tutoren");
		
		$semesters = $this->GetSemestersAsArray();
		
		$coursesInsg = 0;
		$tutorsInsg = 0;
		
		foreach($semesters as $key => $value) {
			$row = array();
			$row[] = $value;
			$row[] = "";
			$row[] = "";
			$row[] = "";
			$table->data[] = $row;
			
			$fbs = $this->GetFBsOfSemester($key);
			$courses = 0;
			$tutors = 0;
			foreach($fbs as $key2 => $value2) {
				$row = array();
				$row[] = "";
				$row[] = $value2;
				$coursesInFB = $this->CountCoursesInFB($key2);
				$row[] = $coursesInFB;
				$tutorsInFB = $this->CountTutorsOfCoursesInFB($key2);
				$row[] = $tutorsInFB;
				$table->data[] = $row;
				$courses += $coursesInFB;
				$tutors += $tutorsInFB;
			}
			$row = array();
			$row[] = "";
			$row[] = "<b>SUMME</b>";
			$row[] = "<b>".$courses."</b>";
			$row[] = "<b>".$tutors."</b>";
			$table->data[] = $row;
			$coursesInsg += $courses;
			$tutorsInsg += $tutors;
		}
		$row = array();
		$row[] = "<b>==============</b>";
		$row[] = "<b>SUMME</b>";
		$row[] = "<b>".$coursesInsg."</b>";
		$row[] = "<b>".$tutorsInsg."</b>";
		$table->data[] = $row;
		
		
		echo html_writer::table($table);
	}
}

$a = new moodleAnalyst();

if(!isset($_GET['nav']) OR $_GET['nav'] == 'ShowGeneralInformation') {
	$a->ShowGeneralInformation($DB, $OUTPUT);
}
if(isset($_GET['nav']) AND $_GET['nav'] == 'ShowCategories') {
	$a->ShowCategories($DB, $OUTPUT);
}
if(isset($_GET['nav']) AND $_GET['nav'] == 'ShowCourses') {
	$a->ShowCourses($DB, $OUTPUT);
}
if(isset($_GET['nav']) AND $_GET['nav'] == 'ShowCoursesWithMostContent') {
	$a->ShowCoursesWithMostContent($DB, $OUTPUT);
}
if(isset($_GET['nav']) AND $_GET['nav'] == 'ShowCoursesWithMostForums') {
	$a->ShowCoursesWithMostForums($DB, $OUTPUT);
}

if(isset($_GET['nav']) AND $_GET['nav'] == 'CountCoursesByCourseFormat') {
	$a->CountCoursesByCourseFormat($OUTPUT);
}

if(isset($_GET['nav']) AND $_GET['nav'] == 'ShowAdobeConnectSettings') {
	$a->ShowAdobeConnectSettings($OUTPUT);
}

if(isset($_GET['nav']) AND $_GET['nav'] == 'ShowGames') {
	$a->ShowGames($OUTPUT);
}

if(isset($_GET['nav']) AND $_GET['nav'] == 'ContentsOfCoursesInCategory') {
	$a->ContentsOfCoursesInCategory($OUTPUT);
}

//=========================================================

if(isset($_GET['nav']) AND $_GET['nav'] == 'ShowCategoryFiles') {
	$a->ShowCategoryFiles($OUTPUT);
}

if(isset($_GET['nav']) AND $_GET['nav'] == 'ShowCategoryCommunication') {
	$a->ShowCategoryCommunication($OUTPUT);
}

if(isset($_GET['nav']) AND $_GET['nav'] == 'ShowCategoryTest') {
	$a->ShowCategoryTest($OUTPUT);
}

if(isset($_GET['nav']) AND $_GET['nav'] == 'ShowCategoryCooperation') {
	$a->ShowCategoryCooperation($OUTPUT);
}
if(isset($_GET['nav']) AND $_GET['nav'] == 'ShowCategoryCourseOrganisation') {
	$a->ShowCategoryCourseOrganisation($OUTPUT);
}
if(isset($_GET['nav']) AND $_GET['nav'] == 'ShowCategoryFeedback') {
	$a->ShowCategoryFeedback($OUTPUT);
}

if(isset($_GET['nav']) AND $_GET['nav'] == 'ShowCoursesOrderedByAmountOfUsersDESC') {
	$a->ShowCoursesOrderedByAmountOfUsersDESC();
}

if(isset($_GET['nav']) AND $_GET['nav'] == 'Helios') {
	$a->Helios($OUTPUT);
}

if(isset($_GET['nav']) AND $_GET['nav'] == 'LeereKurse') {
	$a->LeereKurse($OUTPUT);
}

if(isset($_GET['nav']) AND $_GET['nav'] == 'Tutoren') {
	$a->Tutoren($OUTPUT);
}

if(isset($_GET['nav']) AND $_GET['nav'] == 'Schnittstelle') {
	$a->Schnittstelle($OUTPUT);
}

if(isset($_GET['nav']) AND $_GET['nav'] == 'Gruppierungen') {
	$a->Gruppierungen();
}
 

echo $OUTPUT->footer();

