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
 * Defines the version of videostream.
 *
 * This code fragment is called by moodle_needs_upgrading() and
 * /admin/index.php.
 *
 * @package    block_video
 * @copyright  2020 Sarav@openapp
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// defined('MOODLE_INTERNAL') || die;

require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/locallib.php');
// global $PAGE;

$id = required_param('id', PARAM_RAW);
$courseid = optional_param('courseid', 1 , PARAM_INT);

// Block page for non auth users
require_login($courseid);

$course = $DB->get_record('course', ['id' => $courseid]);
$videoname = $DB->get_field('local_video_directory', 'orig_filename', ['id' => $id]);
$url = new moodle_url('/blocks/videodirectory/view.php', array('id' => $id, 'courseid' => $courseid));
$PAGE->set_url($url);
$PAGE->set_heading($course->fullname);
$PAGE->set_title($course->shortname . ': ' . $videoname);
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add($videoname);
$PAGE->requires->css('/blocks/videodirectory/videojs-seek-buttons/videojs-seek-buttons.css');
$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/blocks/video/videostyle.css'));

$contextcourse = context_course::instance($courseid);
$PAGE->set_context($contextcourse);
$blockid = $DB->get_field('block_instances', 'id', ['parentcontextid' => $contextcourse->id, 'blockname' => 'video']);

$PAGE->set_course($course);
$PAGE->set_pagelayout('course');

require_login();

if (!is_enrolled($contextcourse, $USER->id) && !is_siteadmin($USER) && !has_capability('block/video:viewvideo', $contextblock)) {
    redirect($CFG->wwwroot . '/course/view.php?id=' . $courseid);
}

$_SESSION['videoid'] = $id;
$context = context_course::instance($courseid);
//print_r($context->id);die;


$event = \block_video\event\video_view::create(array(
    'objectid' => $context->id,
    'contextid' => $context->id,
    ));
$event->trigger();

$output = '';
$output .= block_video($id, $courseid);
echo $OUTPUT->header();
echo $OUTPUT->heading($videoname);
echo $output;
echo $OUTPUT->footer();
