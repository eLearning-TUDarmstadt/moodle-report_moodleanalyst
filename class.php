<?php

error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
ini_set("display_errors", 1);
define('MOODLE_INTERNAL', true);
class overview {
	
	/**
	 * HTML-Template f�r Eintr�ge einzelner Kurse in Kurslisten, die Variablen einfach durch Werte ersetzen
	 * @var #MoodleTUCaN# moodle oder tucan, setzt die Klasse entsprechend und kann per CSS angepasst werden
	 * @var #id# Moodle-Kurs-ID
	 * @var #category# Moodle-Kategorie
	 * @var #kursname Moodle-Kursname
	 * @var #idnumber# idnumber in mdl_course (TUCaN-Veranstaltungsnummer)
	 * @var #created# Zeit der Kurs-Erstellung
	 * @var #modified# Zeit der letzten Kurs-�nderung
	 * @var #visibilityImageURL# Link zum Bild, das Sichtbarkeit abbildet
	 */
	private $CourseListEntryHTMLTemplate = '
			<div class="row #MoodleTUCaN#">
				<div class="col-md-8">
					<b>##id#: #category# / #kursname#</b>
					<div class="row">
						<div class="col-md-12">TUCaN-ID: #idnumber#</div>
					</div>
					<div class="row">
						<div class="col-md-6">Erstellt am #created#</div>
	            		<div class="col-md-6">Ge�ndert am #modified#</div>
					</div>
				</div>
				<div class="col-md-4">
					<img src="#visibilityImageURL#" width="90px"/>
				</div>
			</div>
			';
	
	public function Kurse() {
		global $DB;
		$sql = "SELECT {course}.id, {course}.category, {course}.fullname, {course}.visible, {course}.shortname, {course}.idnumber, {course}.timecreated, {course}.timemodified, {course_categories}.name as categoryname FROM {course}, {course_categories} WHERE {course_categories}.id = {course}.category ORDER BY {course}.timecreated DESC";
		$courses = $DB->get_records_sql($sql);
		$puffer = '<h2>'.count($courses).' Kurse:</h2>';
		$timeformat = 'd.m.Y \u\m H:i:s';
		foreach ($courses as $key => $value) {
			$template = $this->CourseListEntryHTMLTemplate;
			if(!empty($value->idnumber)) {
				$template = str_replace('#MoodleTUCaN#', 'tucan', $template);
			}
			else {
				$template = str_replace('#MoodleTUCaN#', 'moodle', $template);
			}
			
			$template = str_replace('#id#', $value->id, $template);
			$template = str_replace('#category#', $value->categoryname, $template);
			$template = str_replace('#kursname#', $value->fullname, $template);
			$template = str_replace('#idnumber#', $value->idnumber, $template);
			$template = str_replace('#created#', date($timeformat, $value->timecreated), $template);
			$template = str_replace('#modified#', date($timeformat, $value->timemodified), $template);
			if($value->visible == 0) {
				$template = str_replace('#visibilityImageURL#', '/report/moodleanalyst/pix/hide.png', $template);
			}	
			else {
				$template = str_replace('#visibilityImageURL#', '/report/moodleanalyst/pix/show.png', $template);
			}
			$puffer .= $template;									
		}
		echo $puffer;
	}
	
	private function navigation($class_methods, $activeMethod = null) {
		
		$puffer = '
				<!-- Fixed navbar -->
			    <div class="navbar navbar-default navbar-fixed-top" role="navigation">
			      <div class="container">
			        <div class="navbar-header">
			          <a class="navbar-brand" href="#">Moodle Report</a>
			        </div>
			        <div class="navbar-collapse collapse">
			          <ul class="nav navbar-nav">';
		
		foreach ($class_methods as $name) {
			if($name == $activeMethod) {
				$puffer .= '<li class="active">';
			}
			else {
				$puffer .= '<li>';
			}
			$puffer .= '<a href="'.$_SERVER['SCRIPT_NAME'].'?nav='.$name.'">'.$name.'</a></li>';
		}
		
		$puffer .= '
			          </ul>
			        </div><!--/.nav-collapse -->
			      </div>
			    </div>
			';
		echo $puffer;
	}
	
	private function header() {
		echo '
			<html>
				<head>
					<link rel="stylesheet" href="/report/moodleanalyst/css/bootstrap.min.css">
					
					<!-- Optional theme -->
					<link rel="stylesheet" href="/report/moodleanalyst/css/bootstrap-theme.min.css">
					<link href="/report/moodleanalyst/css/navbar-fixed-top.css" rel="stylesheet">
					<link href="/report/moodleanalyst/css/grid.css" rel="stylesheet">
				</head>
				<body>
			';
	}
	
	private function footer() {
		echo '
				</body>
			</html>
			';
	}
	
	private function CheckPermission() {
		@session_start();
		//print_r($_SESSION);
		//echo "<pre>".print_r($_SESSION['USER']->access['rdef']['/1:1']['moodle/role:switchroles'], true)."</pre>";
		if (!isset($_SESSION['USER']->id)) {
			//echo "<pre>".print_r($_SESSION['USER'], true)."</pre>";
			exit(-1);
		}
		
		global $DB;
		@$user = $DB->get_record('role_assignments', array('userid' => $_SESSION['USER']->id, 'contextid' => 1), 'roleid', IGNORE_MULTIPLE);
		if(!isset($user->roleid)) {
			echo "Fehlender Login! => Abbruch";
			exit(-1);
		}
		if($user->roleid > 2 OR !$user) {
			echo "Keine ausreichenden Rechte! => Abbruch";
			exit(-1);
		}
		
		//[moodle/role:switchroles] => 1['USER']['access']['rdef']['/1:1']
	}
	
	public function __construct($class_methods) {
		require_once '../../config.php';
		$this->CheckPermission();
		foreach ($class_methods as $key => $value) {
		    if($value == '__construct' OR $value == '__destruct') {
		    	unset($class_methods[$key]);
		    }
		}
		$this->header();
		
		if(isset($_REQUEST['nav'])) {
			$nav = $_REQUEST['nav'];
		}
		else {
			$nav = false;
		}
		
		$this->navigation($class_methods, $nav);
		//echo "<pre>".print_r($_SERVER)."</pre>";
		echo '
			<div class="container">
				<div class="jumbotron">
			';
		
		
		
		if($nav) {
			if (method_exists($this, $nav)) {
				$this->$nav();
			}
			else {
				echo '
						<button type="button" class="btn btn-lg btn-danger">Funktion <i><b>'.$nav.'</b></i> existiert nicht!</button>';
			}
		}
		echo '
				</div>	
			</div>';
		
		
	}
	
	public function __destruct() {
		echo '
			<!-- jQuery (necessary for Bootstraps JavaScript plugins) -->
			<script src="/report/moodleanalyst/js/jquery.min.js"></script>
			<!-- Include all compiled plugins (below), or include individual files as needed -->
   			<script src="/report/moodleanalyst/js/bootstrap.min.js"></script>	
			';
		$this->footer();
	}
	
	
}

$class_methods = get_class_methods('overview');
$moodleAnalyst = new overview($class_methods);



?>