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

defined('MOODLE_INTERNAL') || die();

class block_video extends block_base {
    public function init() {
        $this->title = get_string('video', 'block_video');
    }
    // The PHP tag and the curly bracket for the class definition 
    // will only be closed after there is another function added in the next section.
    function has_config() {
        return true;
    }
    public function get_content() {
 
        global $OUTPUT, $COURSE, $PAGE, $CFG, $USER, $DB;
	// Do not show block content for guest or non logged on user
	// There should be a right way of doing this...
	// require_login() make infinite redirects
	// --Yedidia
	if ($USER->id <= 1) {
		return "";
	}
        if ($this->content !== null) {
          return $this->content;
        }
        require_once(__DIR__ . '/locallib.php');
        $PAGE->requires->js(new moodle_url($CFG->wwwroot . '/blocks/video/javascript/javascript.js'));

        $hiddenzoomvideos = get_config('block_video', 'hiddenzoomvideos');
        $videos = get_videos_from_zoom($COURSE->id);
        $videos = array_map(function($v)
        {
            if (isset($v->hidden) && !empty($v->hidden)) {
                $obj = (array)$v;
                $obj['hiddenclass'] = 'fa-eye-slash';
                $v = (object)$obj;
            } else {
                $obj = (array)$v;
                $obj['hiddenclass'] = $hiddenzoomvideos? 'fa-eye-slash' :'fa-eye';
                $v = (object)$obj;
            }
            return $v;
        }, $videos);

        $zoomvideos = get_videos_from_video_directory_by_course($COURSE);
        $this->content = new stdClass;;
        $contextcourse = context_course::instance($COURSE->id);

        $data = [
            'wwwroot' => $CFG->wwwroot,
            'dirrtl' => current_language() == 'he' ? true : false,
            'course' => $COURSE->id,
            'canaddlocalvideos' => has_capability('block/video:addlocalvideos', $contextcourse),
            'caneditnamevideo' => has_capability('block/video:editnamevideo', $contextcourse),
            'showingpreferencelist' => get_showingprefernece_of_user() == 'list' ? true : false,
            'videos' => $videos,
            'showvideos' => is_array($videos) && count($videos) > 0 ? true : false,
            'zoomvideos' => $zoomvideos,
            'showzoomvideos' => count($zoomvideos) > 0 || has_capability('block/video:addlocalvideos', $contextcourse) ? true : false,
            'haszoomvideos' => count($zoomvideos) > 0 ? true : false,
            'showheader' => count($zoomvideos) > 0 || count($videos) > 0 ? true : false
        ];
        //if (!(count($zoomvideos) > 0 && !(count($videos)) || has_capability('block/video:addlocalvideos', $contextcourse)) ) {
        //    return $this->content;
        //}
        $this->content->text = $OUTPUT->render_from_template('block_video/blockvideo', $data);
        return $this->content;
    }
}
