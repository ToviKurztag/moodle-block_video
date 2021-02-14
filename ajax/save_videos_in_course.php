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

$catvidcourse = $DB->get_field_sql('SELECT cat.id
                                    from {local_video_directory_cats} cat
                                    join {course} c on cat.cat_name = c.shortname
                                    where c.id = ?', [$courseid]);
if (!isset($catvidcourse) || empty($catvidcourse)) {
    $shortname = $DB->get_field('course', 'shortname', ['id' => $courseid]);
    $cat = new stdClass();
    $cat->father_id = 0;
    $cat->cat_name = $shortname;
    $catvidcourse = $DB->insert_record('local_video_directory_cats', $cat);
}

foreach ($videos as $vid) {
    if ($vidcat = $DB->get_record('local_video_directory_catvid', ['cat_id' => $catvidcourse, 'video_id' => $vid->id])) {
        if ($vid->checked == false) {
            $DB->delete_records('local_video_directory_catvid', ['cat_id' => $catvidcourse, 'video_id' => $vid->id]);
        }
    } else if ($vid->checked == true) {
        $vidcat = new stdClass();
        $vidcat->cat_id = $catvidcourse;
        $vidcat->video_id = $vid->id;
        $DB->insert_record('local_video_directory_catvid', $vidcat);
    }
}
print_r($CFG->wwwroot . '/course/view.php?id=' . $courseid);