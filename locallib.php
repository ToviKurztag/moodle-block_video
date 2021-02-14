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
 *
 * This code fragment is called by moodle_needs_upgrading() and
 * /admin/index.php.
 *
 * @package    block_video
 * @copyright  2020 Chaya@openapp
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
require_once( __DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/video_directory/locallib.php');

function video($id , $courseid) {
    $output = "<div class='videostream'>";
    $config = get_config('videostream');

    if (($config->streaming == "symlink") || ($config->streaming == "php")) {
        $output .= get_video_source_elements_videojs($config->streaming, $id, $courseid);

    } else if ($config->streaming == "hls") {
        // Elements for video sources. (here we get the hls video).
        $output .= get_video_source_elements_hls($id, $courseid);
    }
    $output .= get_bookmark_controls($id);
    // Close video tag.
    $output .= html_writer::end_tag('video');
    // Close videostream div.
    $output .= "</div>";
    return $output;
}

function get_video_source_elements_videojs($type, $id, $courseid) {
    global $CFG, $OUTPUT;

    $width = '800px';
    $height = '500px';

    $videolink = block_videodirectory_createsymlink($id);

    $data = array('width' => $width, 'height' => $height, 'videostream' => $videolink, 'wwwroot' => $CFG->wwwroot, 'videoid' => $id, 'type' => 'video/mp4');
    $output = $OUTPUT->render_from_template("block_videodirectory/hls", $data);
    $output .= video_events($id, $courseid);
    return $output;
}

function block_videodirectory_createHLS($videoid) {
    global $DB;

    $config = get_config('videostream');

    $id = $videoid;
    $streams = $DB->get_records("local_video_directory_multi", array("video_id" => $id));
    if ($streams) {
        foreach ($streams as $stream) {
                $files[] = $stream->filename;
        }
        $hls_streaming = $config->hls_base_url;
    } else {
        $files[] = local_video_directory_get_filename($id);
        $hls_streaming = $config->hlsingle_base_url;
    }
    $parts = array();
    foreach ($files as $file) {
            $parts[] = preg_split("/[_.]/", $file);
    }
    $hls_url = $hls_streaming . $parts[0][0];
    if ($streams) {
        $hls_url .= "_";
        foreach ($parts as $key => $value) {
            $hls_url .= "," . $value[1];
        }
    }
    $hls_url .= "," . ".mp4".$config->nginx_multi."/master.m3u8";
    return $hls_url;
}



function video_events($id, $courseid) {
    global $CFG, $DB;

    $context = context_course::instance($courseid);
    $sesskey = sesskey();
    $jsmediaevent = "<script language='JavaScript'>
        var v = document.getElementsByTagName('video')[0];

        v.addEventListener('seeked', function() { sendEvent('seeked'); }, true);
        v.addEventListener('play', function() { sendEvent('play'); }, true);
        v.addEventListener('stop', function() { sendEvent('stop'); }, true);
        v.addEventListener('pause', function() { sendEvent('pause'); }, true);
        v.addEventListener('ended', function() { sendEvent('ended'); }, true);
        v.addEventListener('ratechange', function() { sendEvent('ratechange'); }, true);

        function sendEvent(event) {
            console.log(event);
            require(['jquery'], function($) {
                $.post('" . $CFG->wwwroot . "/blocks/video/ajax/event_ajax.php',
                 {
                    videoid: " . $id . ",
                    contextid: ".$context->id .",
                    action: event,
                    sesskey: '" . $sesskey . "' } );
            });
        }

    </script>";
    return $jsmediaevent;
}


function is_teacher($user = '') {
    global $USER, $COURSE;
    if (is_siteadmin($USER)) {
        return true;
    }
    // Check if user is editingteacher.
    $context = context_course::instance($COURSE->id);
    $roles = get_user_roles($context, $USER->id, true);
    $keys = array_keys($roles);
    foreach ($keys as $key) {
        if ($roles[$key]->shortname == 'editingteacher') {
            return true;
        }
    }
    return false;
}

function get_bookmark_controls($videoid) {
    global $DB, $USER, $OUTPUT;
    $output = '';
    $isteacher = is_teacher();
        $sql = "select * from {block_video_bookmarks}
        where (userid =? or permission = ?) and video_id = ?";
    $bookmarks = $DB->get_records_sql($sql, ['userid' => $USER->id, 'permission' => 'public', 'video_id' => $videoid]);

    $bookmarks = array_values(array_map(function($a) {
        $a->bookmarkpositionvisible = gmdate("H:i:s", (int)$a->videoposition);
        return $a;
    }, $bookmarks));
    $submit = get_string('submitbookmark' , 'block_video' );
    $output .= $OUTPUT->render_from_template('block_video/bookmark_controls',
    ['bookmarks' => $bookmarks, 'video_id' => $videoid, 'isteacher' => $isteacher, 'submit' => $submit]);
    return $output;
}

function get_video_source_elements_hls($id, $courseid) {
    global $CFG, $OUTPUT, $PAGE;
    $width = '800px';
    $height = '500px';
    $hlsstream = block_videodirectory_createHLS($id);
    $data = array('width' => $width, 'height' => $height, 'videostream' => $hlsstream, 'wwwroot' => $CFG->wwwroot, 'videoid' => $id, 'type' => 'application/x-mpegURL');
    $output = $OUTPUT->render_from_template("block_videodirectory/hls", $data);
    $output .= video_events($id, $courseid);
    return $output;
}



function block_videodirectory_createsymlink($videoid) {
    global $DB;
    $filename = $DB->get_field('local_video_directory', 'filename', [ 'id' => $videoid ]);
    if (substr($filename, -4) != '.mp4') {
        $filename .= '.mp4';
    }
    $config = get_config('local_video_directory');
    return $config->streaming . "/" . $filename;
}



function get_videos_from_zoom($courseid = null) {
    global $COURSE, $DB, $USER, $CFG;

    $course = $DB->get_record("course", ["id"=> $courseid]);
    if ($course == null) {
        $course = $COURSE;
    }
    $result = [];
    $streamingurl = get_config('local_video_directory', 'streaming');

    $role = get_user_roles_in_course($USER->id, $courseid);
    $pos = strpos($role, get_string("student", "block_video"));
    if (isset($pos) & !empty($pos)) {
        $hidden = " AND bv.hidden != 1";
    } else {
        $hidden = "";
    }

    $sql = "SELECT DISTINCT vv.id, vv.orig_filename as name,
    vv.filename,vv.timemodified, thumb, vv.length, bv.hidden
                        FROM  {local_video_directory} vv
                        LEFT JOIN {local_video_directory_zoom} vz
                        ON vv.id = vz.video_id
                        LEFT JOIN {zoom} z
                        ON z.meeting_id = vz.zoom_meeting_id
                        LEFT JOIN {block_video} as bv
                        ON vv.id = bv.videoid
                        WHERE z.course = ?" . $hidden;

                        
        $videos = $DB->get_records_sql($sql, [$course->id]);
    foreach ($videos as $video) {
        $video->source = $CFG->wwwroot . '/blocks/video/viewvideo.php?id=' . $video->id . '&courseid=' . $course->id . '&type=2';
        if ( ! check_file_exist($streamingurl . $video->filename . '.mp4')) {
            unset($videos[$video->id]);
            continue;
        }
        $video->imgurl = $CFG->wwwroot . '/local/video_directory/thumb.php?id=' . $video->id . '&mini=1';
        $video->imgurl = $streamingurl . $video->filename . '-mini.png';
        if (! check_file_exist($streamingurl . $video->filename . '-mini.png')) {
            $video->imgurl = '';
        }
        $video->date = date('d-m-yy H:i:s', $video->timemodified);
    }
    return array_values($videos);
}

function sortdate($a, $b) {
    $a = strtotime($a->date);
    $b = strtotime($b->date);
    return $a < $b ? 1 : -1;
}


function get_showingprefernece_of_user($userid = null) {
    global $USER, $COURSE, $DB;
    if ($userid == null) {
        $userid = $USER->id;
    }
    $data = $DB->get_field('block_video_preferences', 'data', ['userid' => $userid, 'courseid' => $COURSE->id, 'name' => 'videosdisplay']);

    if (!isset($data) || empty($data) || $data == '') {
        $data = get_config('block_video', 'defaultshowingvideos');
    }

    return $data;
}

function get_videos_from_video_directory_by_course($course = null) {
    global $COURSE, $DB, $USER, $CFG;

    if ($course == null) {
        $course = $COURSE;
    }
    $result = [];
    $streamingurl = get_config('local_video_directory', 'streaming');
    $videos = $DB->get_records_sql('SELECT vid.id, vid.orig_filename name, vid.filename, length, vid.timemodified, cat.cat_name
                                    from {local_video_directory_cats} cat
                                    join {local_video_directory_catvid} catvid on cat.id = catvid.cat_id
                                    join {local_video_directory} vid on vid.id = catvid.video_id
                                    where cat.cat_name = ?
                                    ORDER BY vid.timemodified desc', [$course->shortname]);
    foreach ($videos as $video) {
        // $video->source = $streamingurl . $video->filename . '.mp4';
        $video->source = $CFG->wwwroot . '/blocks/video/viewvideo.php?id=' . $video->id . '&courseid=' . $course->id . '&type=2';
        if ( ! check_file_exist($streamingurl . $video->filename . '.mp4')) {
            unset($videos[$video->id]);
            continue;
        }
        $video->imgurl = $CFG->wwwroot . '/local/video_directory/thumb.php?id=' . $video->id . '&mini=1';
        $video->imgurl = $streamingurl . $video->filename . '-mini.png';
        if (! check_file_exist($streamingurl . $video->filename . '-mini.png')) {
            $video->imgurl = '';
        }
        $video->date = date('d-m-yy H:i:s', $video->timemodified);
    }
    return array_values($videos);
}

/*
 * This function return the list of videos from video directory for choose.
 * Params:
 * $course  - optional. If not - global course
 * $userid  - optional. If not - global user->id
 * $public  - optional. 
 * Return true if the file exist and flase if not.
*/
function get_videos_from_video_directory_by_owner($course = null, $userid = null, $public = false) {

    global $DB, $USER, $COURSE, $CFG;
    if ($userid == null) {
        $userid = $USER->id;
    }
    if ($course == null) {
        $course = $COURSE;
    }
    $admins = get_admins();
    $isadmin = false;
    foreach ($admins as $admin) {
        if ($userid == $admin->id) {
            $isadmin = true;
            break;
        }
    }
    $streamingurl = get_config('local_video_directory', 'streaming');
    $catvidcourse = $DB->get_field('local_video_directory_cats', 'id', ['cat_name' => $course->shortname]);
    $catvidcourse = isset($catvidcourse) && !empty($catvidcourse) ? $catvidcourse : 0;
    $videos = $DB->get_records_sql('SELECT vid.id, vid.orig_filename name, vid.filename, length, vid.timemodified, catvid.cat_id
                                            ,vid.private, vid.owner_id, concat(u.firstname, " ", u.lastname) ownername
                                    from {local_video_directory} vid
                                    left join {local_video_directory_catvid} catvid on vid.id = catvid.video_id and catvid.cat_id = ?
                                    left join {user} u on vid.owner_id = u.id
                                    where (vid.owner_id = ? or private = 0 or catvid.cat_id = ?)
                                    ORDER BY name', [$catvidcourse, $userid, $catvidcourse]);
    
    foreach ($videos as $video) {
        $video->select = $video->cat_id == $catvidcourse ? true : false;
        $video->source = $streamingurl . $video->filename . '.mp4';
        if ( ! check_file_exist($video->source)) {
             unset($videos[$video->id]);
             continue;
        }
        //$video->source = $CFG->wwwroot . '/blocks/video/viewvideo.php?id=' . $video->id . '&courseid=' . $course->id . '&type=2';
        $video->imgurl = $CFG->wwwroot . '/local/video_directory/thumb.php?id=' . $video->id . '&mini=1';
        if (! check_file_exist($streamingurl . $video->filename . '-mini.png')) {
            $video->imgurl = '';
        }
        $video->canedit = $video->owner_id == $USER->id || $video->private == 0 || $isadmin ? true : false;
        $video->public = $video->private == 0 ? get_string('yes') : get_string('no');
        $video->date = date('d-m-yy H:i:s', $video->timemodified);
        $video->dateday = date('m-d-yy', $video->timemodified);
    }
    return $videos;
}
/*
 * This function check if remote file is exist.
 * Get $url to the file
 * Return true if the file exist and false if not.
*/
function check_file_exist($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    return $code == 200 ? true : false;
}

