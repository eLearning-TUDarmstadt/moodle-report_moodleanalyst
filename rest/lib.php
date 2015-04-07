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

/**
 * Converts a Unix-timestamp into a JavaScript Date
 * 
 * @param int $timestamp
 * @return Date
 */
function createDateForJavaScript($timestamp) {
    $formatPrefix = "Y,";
    // Javascript starts counting with 0
    $month = ((int) date('m', $timestamp)) - 1;
    $formatSuffix = ",d,H,i,s";
    return "Date(" . date($formatPrefix, $timestamp) . $month . date($formatSuffix, $timestamp) . ")";
}

?>