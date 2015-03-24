<?php

require(dirname(__FILE__).'/../../config.php');
require_once($CFG->libdir.'/adminlib.php');




// page parameters
$page    = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 30, PARAM_INT);    // how many per page
$sort    = optional_param('sort', 'timemodified', PARAM_ALPHA);
$dir     = optional_param('dir', 'DESC', PARAM_ALPHA);

admin_externalpage_setup('reportmoodleanalyst', '', null, '', array('pagelayout'=>'report'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('moodleanalyst', 'report_moodleanalyst'));
global $DB;


class moodleAnalyst {
	private $FormatDatum = "d.m.Y, H:i";
	
	private function DatabaseConnect() {
		$dbname=$CFG->dbname;
		$dbhost=$CFG->dbhost;
		$dbuser=$CFG->dbuser;
		$dbpass=$CFG->dbpass;
		$ret = mssql_connect($dbhost,$dbuser,$dbpass);
		mssql_select_db($dbname);
		return $ret;
	}	
	
	function __construct() {
		$this->CreateNavigationTable();
		require(dirname(__FILE__).'/../../config.php');
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
		//$row[] = '<a href="index.php?nav=ShowGeneralInformation">Allgemeine Informationen</a>';
		//$row[] = '<a href="index.php?nav=ShowCategories">Kategorien</a>';
		//$row[] = '<a href="index.php?nav=ShowCourses">Kurse</a>';
		//$row[] = '<a href="index.php?nav=ShowCoursesWithMostContent">Kurse mit meisten Inhalten</a>';
		//$row[] = '<a href="index.php?nav=ShowCoursesWithMostForums">Kurse mit meisten Foren</a>';
		$row[] = '<a href="index.php?nav=ShowCoursesOrderedByAmountOfUsersDESC">Kurse mit Nutzern</a>';
		$row[] = '<a href="index.php?nav=CountCoursesByCourseFormat">Kurs-Formate</a>';
		//$row[] = '<a href="index.php?nav=ShowAdobeConnectSettings">Adobe Connect</a>';
		$row[] = '<a href="index.php?nav=ContentsOfCoursesInCategory">Kursinhalte</a>';
		$row[] = '<a href="index.php?nav=Helios">Helios</a>';
		$row[] = '<a href="index.php?nav=LeereKurse">Leere Kurse</a>';
		$row[] = '<a href="index.php?nav=Tutoren">Tutoren</a>';
		$row[] = '<a href="index.php?nav=game">game</a>';
		$table->data[] = $row;
		echo html_writer::table($table);
		echo $OUTPUT->heading("Kategorien", 4);
		$table = new html_table();
		$table->data  = array();
		$row = array();
				
		$row[] = '<a href="index.php?nav=ShowCategoryFiles">Dateien</a>';
		$row[] = '<a href="index.php?nav=ShowCategoryCommunication">Kommunikation</a>';
		$row[] = '<a href="index.php?nav=ShowCategoryTest">(Selbst-)Überprüfung</a>';
		$row[] = '<a href="index.php?nav=ShowCategoryCooperation">Kooperation</a>';
		$row[] = '<a href="index.php?nav=ShowCategoryCourseOrganisation">Lehrorganisation</a>';
		$row[] = '<a href="index.php?nav=ShowCategoryFeedback">Rückmeldungen</a>';
		
		
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
		$query = "SELECT fullname from mdl_course WHERE id=".$ID.";";
		$result = mssql_query($query);
		$res_array = mssql_fetch_array($result);
		$Fullname = $res_array[0];
		if($asLink) {
			$FullnameLink = "<a href=/course/view.php?id=".$ID.">".$Fullname."</a>";
			return $FullnameLink;
		}
		return $Fullname;
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
	
	private function GetCategoryName($categoryID) {
		$query = "SELECT name FROM mdl_course_categories WHERE id='".$categoryID."';";
		$result = mssql_query($query);
		$sum = mssql_fetch_array($result);
		return $sum[0];
	}
	
	private function GetCategoryOfCourse($id) {
		$query = "SELECT category FROM mdl_course WHERE id=".$id.";";
		$result = mssql_query($query);
		$sum = mssql_fetch_array($result);
		return $sum[0];
	}
	
	private function GetParentCategoryOfCategory($id) {
		$query = "SELECT parent FROM mdl_course_categories WHERE id=".$id.";";
		$result = mssql_query($query);
		$sum = mssql_fetch_array($result);
		return $sum[0];
	}
	
	private function GetFBOfCourse($id, $asLink = true) {
		$FBid = $this->GetCategoryOfCourse($id);
		$FB = $this->GetCategoryName($FBid);
		if($asLink) {
			$FBLink = "<a href=/course/index.php?categoryid=".$FBid.">".$FB."</a>";
			return $FBLink;
		}
		return $FB;
	}
	
	private function GetSemesterOfCourse($id, $asLink = true) {
		$semesterid = $this->GetParentCategoryOfCategory($this->GetCategoryOfCourse($id));
		$semester = $this->GetCategoryName($semesterid);
		if($asLink) {
			GLOBAL $CFG;
			$semester = "<a href=/course/index.php?categoryid=".$semesterid.">".$semester."</a>";
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
	
	private function CountGamesOfCoursesInFB($id) {
		$courses = $this->GetCoursesOfFB($id);
		$count = 0;
		foreach($courses as $key => $value) {
			$context = $this->GetContextIDOfCourse($key);
			$numberOfGamesInCourse = $this->CountGamesInCourse($context);
			$count += $numberOfGamesInCourse;
		}
		return $count;
	}
	
	private function CountTutorsInCourse($context) {
		$sql = $sql = "SELECT count(userid) FROM mdl_role_assignments WHERE roleid=4 AND contextid=".$context;
		$result = mssql_query($sql);
		$array = mssql_fetch_array($result);
		return $array[0];
	}
	
	private function CountGamesInCourse($context) {
		$sql = $sql = "select count (id) from mdl_game_millionaire AND contextid=".$context;
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
				$row[] = "nn"; //Lehrende
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
	
	public function ShowCoursesOrderedByAmountOfUsersDESC($OUTPUT) {
		
		echo $OUTPUT->heading("Course nach Anzahl der Benutzer", 2);
		$table = new html_table();
		$table->head = array("ID", "Kursname", "Anzahl Nutzer");
		//$table->colclasses = array('leftalign formatname', 'leftalign value');
		
		$table->id = 'moodleanalyst';
		$table->attributes['class'] = 'admintable generaltable';
		$table->data  = array();
		
		$query = "SELECT mdl_enrol.courseid, count(mdl_enrol.courseid) AS anzahl FROM mdl_user_enrolments, mdl_enrol WHERE mdl_enrol.id = mdl_user_enrolments.enrolid GROUP BY mdl_enrol.courseid ORDER BY anzahl DESC;";
		$result = mssql_query($query);
		
		$array = array();
		while($id = mssql_fetch_assoc($result)) {
			$array[] = $id;
		}
		
		foreach($array as $key => $value) {
			$row = array();
			$row[] = $array[$key]['courseid'];
			$row[] = $this->GetFullnameByID($array[$key]['courseid']);
			$row[] = $array[$key]['anzahl'];
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
	
public function game($OUTPUT) {
		$table = new html_table();
		
		$table->head = array("Semester", "FB", "Kurse", "game");
		
		$semesters = $this->GetSemestersAsArray();
		
		$coursesInsg = 0;
		$gamesInsg = 0;
		
		foreach($semesters as $key => $value) {
			$row = array();
			$row[] = $value;
			$row[] = "";
			$row[] = "";
			$row[] = "";
			$table->data[] = $row;
			
			$fbs = $this->GetFBsOfSemester($key);
			$courses = 0;
			$games = 0;
			foreach($fbs as $key2 => $value2) {
				$row = array();
				$row[] = "";
				$row[] = $value2;
				$coursesInFB = $this->CountCoursesInFB($key2);
				$row[] = $coursesInFB;
				$gamesInFB = $this->CountGamesOfCoursesInFB($key2);
				$row[] = $gamesInFB;
				$table->data[] = $row;
				$courses += $coursesInFB;
				$games += $tutorsInFB;
			}
			$row = array();
			$row[] = "";
			$row[] = "<b>SUMME</b>";
			$row[] = "<b>".$courses."</b>";
			$row[] = "<b>".$games."</b>";
			$table->data[] = $row;
			$coursesInsg += $courses;
			$gamesInsg += $games;
		}
		$row = array();
		$row[] = "<b>==============</b>";
		$row[] = "<b>SUMME</b>";
		$row[] = "<b>".$coursesInsg."</b>";
		$row[] = "<b>".$gamesInsg."</b>";
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
	$a->ShowCoursesOrderedByAmountOfUsersDESC($OUTPUT);
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

if(isset($_GET['nav']) AND $_GET['nav'] == 'game') {
	$a->game($OUTPUT);
}


echo $OUTPUT->footer();

