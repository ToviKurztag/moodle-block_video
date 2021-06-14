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

function block_video($id , $courseid) {
    $output = "<div class='videostream'>";
    $config = get_config('videostream');

    if (($config->streaming == "symlink") || ($config->streaming == "php")) {
        $output .= block_video_get_video_videojs($config->streaming, $id, $courseid);
    } else if ($config->streaming == "hls" || ($config->streaming == "vimeo" && !$config->vimeoplayer)) {
        // Elements for video sources. (here we get the hls video).
        $output .= block_video_get_video_hls($id, $courseid);
    } else if($config->streaming == "vimeo") {
        $output .= block_video_get_video_vimeo($id, $courseid);
    }
    $output .= block_video_get_bookmark_controls($id);
    $output .= html_writer::end_tag('video');
    $output .= "</div>";
    return $output;
}


function get_video_source_elements_vimeo($videostream) {
    global $CFG, $OUTPUT, $DB;

   
    $video = $DB->get_record('local_video_directory', ['id' => $videostream->get_instance()->videoid]);
    $videovimeo = $DB->get_record('local_video_directory_vimeo', ['videoid' => $videostream->get_instance()->videoid]);

    $data = array('width' => $width, 'height' => $height, 'symlinkstream' => $videovimeo->streamingurl, 'type' => 'video/mp4',
                  'wwwroot' => $CFG->wwwroot, 'video_id' => $video->id, 'video_vimeoid' => $videovimeo->vimeoid);

    $output = $OUTPUT->render_from_template("mod_videostream/vimeo", $data);

    $output .= $this->video_events($videostream, 1);
    return $output;
}

function block_video_get_video_vimeo($id, $courseid) {
    global $CFG, $OUTPUT, $DB;

    $width = '800px';
    $height = '500px';

    $videovimeo = $DB->get_record('local_video_directory_vimeo', ['videoid' => $id]);

    $data = array('width' => $width, 'height' => $height, 'symlinkstream' => $videovimeo->streamingurl, 'type' => 'video/mp4',
    'wwwroot' => $CFG->wwwroot, 'video_id' => $id, 'video_vimeoid' => $videovimeo->vimeoid);

    $output = $OUTPUT->render_from_template("block_video/vimeo", $data);
    //$output .= block_video_events($id, $courseid);
    return $output;
}

function block_video_get_video_videojs($type, $id, $courseid) {
    global $CFG, $OUTPUT;

    $width = '800px';
    $height = '500px';

    $videolink = block_video_createsymlink($id);

    $data = array('width' => $width, 'height' => $height, 'videostream' => $videolink, 'wwwroot' => $CFG->wwwroot, 'videoid' => $id, 'type' => 'video/mp4');
    $output = $OUTPUT->render_from_template("block_video/hls", $data);
    $output .= block_video_events($id, $courseid);
    return $output;
}

function block_video_createHLS($videoid) {
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


function block_video_events($id, $courseid) {
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

function block_video_get_bookmark_controls($videoid) {
    global $DB, $USER, $OUTPUT;
    $output = '';
    $isteacher = is_teacher();
        $sql = "select * from {block_video_bookmarks}
        where (userid =? or permission = ?) and video_id = ?";
    $bookmarks = $DB->get_records_sql($sql, ['userid' => $USER->id, 'permission' => 'public', 'video_id' => $videoid]);

    $bookmarks = array_values(array_map(function($a) {
        $a->bookmarkpositionvisible = gmdate("H:i:s", (int)$a->videoposition);
        $a->permission = $a->permission == 'public'? 1: 0;
        return $a;
    }, $bookmarks));
    //print_r($bookmarks);die;
    $submit = get_string('submitbookmark' , 'block_video' );
    $output .= $OUTPUT->render_from_template('block_video/bookmark_controls',
    ['bookmarks' => $bookmarks, 'video_id' => $videoid, 'isteacher' => $isteacher, 'submit' => $submit, 'userid' => $USER->id]);
    return $output;
}

function block_video_get_video_hls($id, $courseid) {
    global $CFG, $OUTPUT, $PAGE, $DB;
    $width = '800px';
    $height = '500px';
    $config = get_config('videostream');

    if ($config->streaming == "vimeo") {
        $hlsstream = $DB->get_field_sql("SELECT streaminghls FROM {local_video_directory_vimeo} WHERE videoid = ? limit 1",
        ['videoid' => $id]);
    } else {
        $hlsstream = block_video_createHLS($id);
    }
    
    $data = array('width' => $width, 'height' => $height, 'videostream' => $hlsstream, 'wwwroot' => $CFG->wwwroot, 'videoid' => $id, 'type' => 'application/x-mpegURL');
    $output = $OUTPUT->render_from_template("block_video/hls", $data);
    $output .= block_video_events($id, $courseid);
    return $output;
}

function block_video_createsymlink($videoid) {
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

    $filename = $CFG->dirroot . '/local/video_directory/cloud/locallib.php';
    if (file_exists($filename)) {
        require_once($CFG->dirroot . '/local/video_directory/cloud/locallib.php');
    }
    $course = $DB->get_record("course", ["id"=> $courseid]);
    if ($course == null) {
        $course = $COURSE;
    }
    $result = [];
    $streamingurl = get_config('local_video_directory', 'streaming') . '/';
   
    $sql = "SELECT DISTINCT vv.id, vv.orig_filename as name,
    vv.filename,vv.timemodified, vv.timecreated, thumb, vv.length, bv.hidden
                        FROM  {local_video_directory} vv
                        LEFT JOIN {local_video_directory_zoom} vz
                        ON vv.id = vz.video_id
                        LEFT JOIN {zoom} z
                        ON z.meeting_id = vz.zoom_meeting_id
                        LEFT JOIN {block_video} as bv
                        ON vv.id = bv.videoid WHERE z.course = ?";

    $videos = $DB->get_records_sql($sql, [$course->id]);

    foreach ($videos as $video) {
        $video->source = $CFG->wwwroot . '/blocks/video/viewvideo.php?id=' . $video->id . '&courseid=' . $course->id . '&type=2';
        if (get_config('local_video_directory_cloud', 'cloudtype') == 'Vimeo') {
            if(!isset(get_data_vimeo($video->id)->streamingurl)) {
                unset($videos[$video->id]);
            }
        } else {
            if ( ! check_file_exist($streamingurl . $video->filename . '.mp4')) {
                unset($videos[$video->id]);
                continue;
            }
        }
        
        if (get_config('local_video_directory_cloud', 'cloudtype') == 'Vimeo') {
            $video->imgurl =  get_data_vimeo($video->id)->thumburl;
            if (!isset($video->imgurl)) {
                $video->imgurl = '';
            }
        } else {
            $video->imgurl = $CFG->wwwroot . '/local/video_directory/thumb.php?id=' . $video->id . '&mini=1';
        }
        $video->date = date('d-m-Y H:i:s', $video->timecreated);
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
    
    $filename = $CFG->dirroot . '/local/video_directory/cloud/locallib.php';
    if (file_exists($filename)) {
        require_once($CFG->dirroot . '/local/video_directory/cloud/locallib.php');
    }    if ($course == null) {
        $course = $COURSE;
    }
    $result = [];
    $streamingurl = get_config('local_video_directory', 'streaming') . '/';
    
    $sql = 'SELECT vid.id, vid.orig_filename name, vid.filename, length, vid.timemodified, vid.timecreated, vc.courseid
        from mdl_block_video_course vc
        join mdl_local_video_directory vid on vid.id = vc.videoid
        where vc.courseid = ?
        ORDER BY vid.timemodified desc';
    $videos = $DB->get_records_sql($sql,  [$course->id]);
    foreach ($videos as $video) {
        $video->source = $CFG->wwwroot . '/blocks/video/viewvideo.php?id=' . $video->id . '&courseid=' . $course->id . '&type=2';
        
        if (get_config('local_video_directory_cloud', 'cloudtype') == 'Vimeo') {
            if(!isset(get_data_vimeo($video->id)->streamingurl)) {
                unset($videos[$video->id]);
            }
        } else {
            if ( !check_file_exist($streamingurl . $video->filename . '.mp4')) {
                unset($videos[$video->id]);
                continue;
            }
        }
        $video->imgurl = $CFG->wwwroot . '/local/video_directory/thumb.php?id=' . $video->id . '&mini=1';     
        $video->date = date('d-m-Y H:i:s', $video->timecreated);
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
   
    $filename = $CFG->dirroot . '/local/video_directory/cloud/locallib.php';
    if (file_exists($filename)) {
        require_once($CFG->dirroot . '/local/video_directory/cloud/locallib.php');
    }
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
    $streamingurl = get_config('local_video_directory', 'streaming') . '/';

    if ($isadmin) {
        $sql = 'SELECT vid.id, vid.orig_filename name, vid.filename, length, vid.timemodified, vid.timecreated, vc.courseid
            ,vid.private, vid.owner_id, concat(u.firstname, " ", u.lastname) as ownername
            from mdl_local_video_directory vid
            left join mdl_block_video_course vc on vid.id = vc.videoid and vc.courseid = ?
            left join mdl_user u on vid.owner_id = u.id
            where (vid.owner_id in  
                (SELECT userid
                FROM mdl_role_assignments as a
                join mdl_context as c 
                on a.contextid = c.id
                where a.roleid = 3 and c.contextlevel = 50 and c.instanceid = ?
                ) 
            or private = 0) OR  vc.courseid = ?
            ORDER BY name';
            $videos = $DB->get_records_sql($sql, [$course->id, $course->id, $course->id]);
    } else {
        $sql = 'SELECT vid.id, vid.orig_filename name, vid.filename, length, vid.timemodified, vid.timecreated, vc.courseid
                ,vid.private, vid.owner_id, concat(u.firstname, " ", u.lastname) as ownername
                from mdl_local_video_directory vid
                left join mdl_block_video_course vc on vid.id = vc.videoid and vc.courseid = ?
                left join mdl_user u on vid.owner_id = u.id
                where (vid.owner_id = ? or private = 0) OR  vc.courseid = ?
                ORDER BY name';
        
        $videos = $DB->get_records_sql($sql, [ $course->id, $userid, $course->id]);
    }
    foreach ($videos as $video) {
        $video->select = $video->courseid == $course->id ? true : false;
        $video->source = $streamingurl . $video->filename . '.mp4';

        if (get_config('local_video_directory_cloud', 'cloudtype') == 'Vimeo') {
            if(!isset(get_data_vimeo($video->id)->streamingurl)) {
                unset($videos[$video->id]);
            }
        } else {
            if ( !check_file_exist($video->source)) { 
                    unset($videos[$video->id]);
                    continue;
            }
        }
        $video->imgurl = $CFG->wwwroot . '/local/video_directory/thumb.php?id=' . $video->id . '&mini=1';
        $video->canedit = $video->owner_id == $USER->id || $video->private == 0 || $isadmin ? true : false;
        $video->public = $video->private == 0 ? get_string('yes') : get_string('no');
        $video->date = date('d-m-Y H:i:s', $video->timecreated);
        $video->dateday = date('m-d-Y', $video->timecreated);
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

