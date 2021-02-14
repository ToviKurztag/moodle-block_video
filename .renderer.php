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

 * This code fragment is called by moodle_needs_upgrading() and
 * /admin/index.php.
 *
 * @package    block_video
 * @copyright  2020 Sarav@openapp
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;


class block_video_renderer extends plugin_renderer_base {


function video($id , $courseid) {
    $output = "<div class='videostream'>";
    $config = get_config('videostream');

    if (($config->streaming == "symlink") || ($config->streaming == "php")) {
    
    //get_video_source_elements_videojs($config->streaming, $id, $courseid);
    $output .= get_video_source_elements_videojs($config->streaming, $id, $courseid);

    } else if ($config->streaming == "hls") {
        // Elements for video sources. (here we get the hls video).
        $output .= get_video_source_elements_hls($id, $courseid);
    }
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
                $.post('" . $CFG->wwwroot . "/blocks/videodirectory/ajax.php',
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

function get_bookmark_controls($videoid) {
    global $DB, $USER, $OUTPUT;
    $output = '';
    $bookmarks = $DB->get_records('videostreambookmarks', ['userid' => $USER->id, 'videoid' => $videoid]);
    $bookmarks = array_values(array_map(function ($a) {
        $a->bookmarkpositionvisible = gmdate("H:i:s", (int) $a->bookmarkposition);
        return $a;
    }, $bookmarks));
    $output .= $OUTPUT->render_from_template('block_videodirectory/bookmark_controls',
        ['bookmarks' => $bookmarks, 'videoid' => $videoid]);
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


    /*
    public function video($videoid) {
        // return $this->page;
        // $this->page->set_context(null);
        // $this->page->set_pagelayout('incourse');
        // $this->page->set_title("ppp");
        // $this->page->set_heading("lll");

        global $CFG, $DB, $OUTPUT;
        $output  = '';
        $videourl = $this->createhls($videoid);
        $output = $this->get_video_source_elements_hls($videoid, $videourl);
        $output .= $this->get_bookmark_controls($videoid);
        // Close video tag.
        $output .= html_writer::end_tag('video');
        return $output;
    }
    public function createhls($videoid) {
        global $DB;

        $config = get_config('videostream');
        $id = $videoid;
        $streams = $DB->get_records("local_video_directory_multi", array("video_id" => $id));
        if ($streams) {
            foreach ($streams as $stream) {
                $files[] = $stream->filename;
            }
            $hlsstreaming = $config->hls_base_url;
        } else {
            $files[] = local_video_directory_get_filename($id);
            $hlsstreaming = $config->hlsingle_base_url;
        }

        $parts = array();
        foreach ($files as $file) {
            $parts[] = preg_split("/[_.]/", $file);
        }

        $hlsurl = $hlsstreaming . $parts[0][0];
        if ($streams) {
            $hlsurl .= "_";

            foreach ($parts as $key => $value) {
                 $hlsurl .= "," . $value[1];
            }
        }
        $hlsurl .= "," . ".mp4".$config->nginx_multi."/master.m3u8";

        return $hlsurl;
    }


    private function get_video_source_elements_hls($videoid, $videourl) {
        global $CFG, $OUTPUT;
        $data = array('width' => '790',
                      'height' => '420',
                      'video_id' => $videoid,
                      'hlsstream' => $videourl);
        $output = $OUTPUT->render_from_template("block_video/hls", $data);
        return $output;
    }


    public function get_bookmark_controls($videoid) {
        global $DB, $USER;
        $output = '';
        $isteacher = $this->is_teacher();
            $sql = "select * from {block_video_bookmarks}
            where (userid =? or permission = ?) and video_id = ?";
        $bookmarks = $DB->get_records_sql($sql, ['userid' => $USER->id, 'permission' => 'public', 'video_id' => $videoid]);

        $bookmarks = array_values(array_map(function($a) {
            $a->bookmarkpositionvisible = gmdate("H:i:s", (int)$a->videoposition);
            return $a;
        }, $bookmarks));
        $submit = get_string('submitbookmark' , 'block_video' );
        $output .= $this->output->render_from_template('block_video/bookmark_controls',
        ['bookmarks' => $bookmarks, 'video_id' => $videoid, 'isteacher' => $isteacher, 'submit' => $submit]);
        return $output;
    }

    public function is_teacher($user = '') {
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
    }*/

}



// plugins: {
//     videoJsResolutionSwitcher: {
//     default: 'low',
//     dynamicLabel: true
//     }
//     }
    
//         } 
        
//         );
//         player.src({
//             src: '{{hlsstream}}',
//             type: 'application/x-mpegURL'
//            }); 
//           player.updateSrc([
//           {
//             src: '{{hlsstream}}?SD',
//             type: 'application/x-mpegURL',
//             label: 'SD',
//             res: 360
//           },
          
//           {
//             src: '{{hlsstream}}?HD',
//             type: 'application/x-mpegURL',
//             label: 'HD',
//             res: 720
//           }
          
//           ]
