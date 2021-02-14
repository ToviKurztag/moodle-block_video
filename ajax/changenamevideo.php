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
// define()
require_once(__DIR__ . '/../../../config.php');
global $USER, $DB;

$vidid = required_param('vidid', PARAM_RAW);
$name = required_param('videoname', PARAM_RAW);

if ($name == '') {
    // echo 'empty name';
}

if ($record = $DB->get_record('local_video_directory', ['id' => $vidid])) {
    $record->orig_filename = $name;
    $record->timemodified = time();
    $DB->update_record('local_video_directory', $record);
} 
/*else {
    $record = new stdClass();
    $record->videoid = $vidid;
    $record->name = $name;
    $record->usermodifiedid = $USER->id;
    $record->timemodified = time();
    $DB->insert_record('block_video', $record);
}*/

// echo $name;
