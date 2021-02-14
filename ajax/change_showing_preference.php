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

 // original plugin by 2013 Jonas Nockert <jonasnockert@gmail.com>


/**
 * Defines the version of videostream.
 *
 * This code fragment is called by moodle_needs_upgrading() and
 * /admin/index.php.
 *
 * @package    block_video
 * @copyright  2020 Chaya@openapp
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// defined('MOODLE_INTERNAL') || die;
require_once(__DIR__ . '/../../../config.php');
global $USER, $DB;

$prefer = required_param('prefer', PARAM_RAW);
$course = required_param('course', PARAM_RAW);
$courseid = explode('-', $course)[1];

if ($record = $DB->get_record('block_video_preferences', ['userid' => $USER->id,
                                                          'courseid' => $courseid,
                                                          'name' => 'videosdisplay'])) {
    $record->data = $prefer;
    $record->timemodified = time();
    $DB->update_record('block_video_preferences', $record);
} else {
    $record = new stdClass();
    $record->userid = $USER->id;
    $record->courseid = $courseid;
    $record->name = 'videosdisplay';
    $record->data = $prefer;
    $record->timemodified = time();
    $DB->insert_record('block_video_preferences', $record);
}