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
require_once( __DIR__ . '/../../config.php');
defined('MOODLE_INTERNAL') || die;

global $PAGE, $OUTPUT, $DB;

require_login();
require_once('locallib.php');

$courseid = required_param('courseid', PARAM_RAW);
$contextcourse = context_course::instance($courseid);
$blockid = $DB->get_field('block_instances', 'id', ['parentcontextid' => $contextcourse->id, 'blockname' => 'video']);
//$contextblock = context_block::instance($blockid);

$PAGE->set_context($contextcourse);
$PAGE->set_heading(get_string('list', 'block_video'));
$PAGE->set_url('/blocks/video/choosevideos.php');
$PAGE->set_title(get_string('list', 'block_video'));
$PAGE->requires->css('/blocks/video/jquery-ui.css');
$PAGE->set_pagelayout('incourse');
$course = $DB->get_record('course', ['id' => $courseid]);
$PAGE->set_course($course);

//if (! has_capability('block/video:addlocalvideos', $contextblock)) {
//    redirect($CFG->wwwroot . '/course/view.php?id=' . $courseid);
//}

$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/blocks/video/javascript/choosevideos.js'));
echo $OUTPUT->header();
$fields = ['selected', 'thumb', 'id', 'name', 'length', 'owner', 'public', 'timecreated'];
$liststrings = [];
foreach ($fields as $field) {
    $liststrings[] = get_string($field, 'block_video');
}

$videos = array_values(get_videos_from_video_directory_by_owner($course));

$datafortemplate = [
    'wwwroot' => $CFG->wwwroot,
    'liststrings' => $liststrings,
    'videos' => $videos
];

echo $OUTPUT->render_from_template('block_video/choosevideos', $datafortemplate);
echo $OUTPUT->footer();
