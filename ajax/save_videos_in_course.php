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

$videos = optional_param('videos',null, PARAM_RAW);
$course = required_param('course', PARAM_RAW);
$courseid = explode('-', $course)[1];
$videos = json_decode($videos);

//print_r($videos);die;
foreach ($videos as $vid) {
    $video = $DB->get_record('block_video_course', ['courseid' => $courseid, 'videoid' => $vid->id]);

    if (isset($video) && !empty($video)) {
        if ($vid->checked == false) {
            $DB->delete_records('block_video_course', ['courseid' => $courseid, 'videoid' => $vid->id]);
        }
    } else if ($vid->checked == true) {
        $v = new stdClass();
        $v->videoid = $vid->id;
        $v->courseid = $courseid;
        $v->usermodifiedid = $USER->id;
        $v->timemodified = time();
        $DB->insert_record('block_video_course', $v);
    }
}
print_r($CFG->wwwroot . '/course/view.php?id=' . $courseid);