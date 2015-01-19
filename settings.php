<?php


defined('MOODLE_INTERNAL') || die;
$ADMIN->add('reports', new admin_externalpage('reportmoodleanalyst', get_string('moodleanalyst', 'report_moodleanalyst'), "$CFG->wwwroot/report/moodleanalyst/index.php", 'report/moodleanalyst:view'));
// no report settings
$settings = null;