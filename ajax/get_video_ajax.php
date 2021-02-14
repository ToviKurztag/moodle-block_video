<?php
// This file is part of Moodle - http://moodle.org/
///
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
 *
 * This code fragment is called by moodle_needs_upgrading() and
 * /admin/index.php.
 *
 * @package    block_video
 * @copyright  2020 Chaya@openapp
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once( __DIR__ . '/../../../config.php');
require_login();//print_r('aaa');die;
defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ .'/../locallib.php');
require_once(__DIR__ . '/../../../local/video_directory/locallib.php');
global $DB;



// $videos = $DB->get_records_sql('SELECT DISTINCT v.*, ' . $DB->sql_concat_join("' '", array("firstname", "lastname")) .
// ' AS name
// FROM {local_video_directory} v
// LEFT JOIN {user} u on v.owner_id = u.id

// WHERE (owner_id =' . $USER->id .
// ' OR (private IS NULL OR private = 0))');
$videos = get_videos_from_video_directory_by_owner();
$config = get_config('local_video_directory');
print_r($videos);die;

function my_block_video_get_thumbnail_url($thumb, $videoid, $clean=0) {
    global $CFG, $DB;
    $config = get_config('local_video_directory');

    $dirs = get_directories();
    $thumb = str_replace(".png", "-mini.png", $thumb);
    $thumbdata = explode('-', $thumb);
    $thumbid = $thumbdata[0];
    $thumbseconds = isset($thumbdata[1]) ? "&second=$thumbdata[1]" : '';

    $video = $DB->get_record('local_video_directory', ['id' => $videoid]);
    // echo $video->filename;
    // echo $config->streaming . "/" . local_video_directory_get_filename($videoid) . ".mp4";




    if ((file_exists( $dirs['converted'] . $videoid . ".mp4"))
        || (file_exists( $dirs['converted'] . $video->filename . ".mp4"))) {
        $alt = 'title="' . get_string('play', 'local_video_directory') . '"
            alt="' . get_string('play', 'local_video_directory') . '"';
        if (get_streaming_server_url()) {
            if ($video->filename != $videoid . '.mp4') {
                $playbutton = ' data-video-url="' . htmlspecialchars(get_streaming_server_url()) . "/" .
                $video->filename . '.mp4" data-id="' . $videoid . '"';
            } else {
                $playbutton = ' data-video-url="' . htmlspecialchars(get_streaming_server_url()) . "/" .
                        $videoid . '.mp4" data-id="' . $videoid . '"';
            }
        } else {
            $playbutton = ' data-video-url="play.php?video_id=' .
            $videoid . '" data-id="' . $videoid . '"';
        }
    } else {
        $playbutton = '';
    }

    $thumb = "<div class='video-thumbnail' " . $playbutton . ">" .
              ($thumb ? "<img src='$CFG->wwwroot/local/video_directory/thumb.php?id=$thumbid$thumbseconds&mini=1 '
        class='thumb' " . $playbutton ." >" : get_string('noimage', 'local_video_directory')) . "</div>";

    if ($clean) {
        $thumb = "$CFG->wwwroot/local/video_directory/thumb.php?id=$thumbid$thumbseconds";
    }

    return $thumb;
}

foreach ($videos as $video) {
    $video->selected = '<input type= checkbox>';
// echo $video->filename;die;
    $video->thumb = my_block_video_get_thumbnail_url($video->thumb, $video->id);
    // print_r($video->thumb);
    // $filename = local_video_directory_get_filename($video->id)
    // $video->streaming_url = '<a target="_blank" href="' . get_streaming_server_url() . '/' . $filename . '.mp4">'
    // . get_streaming_server_url() . '/' . $filename . '.mp4</a><br>';
}

// $videodata = json_decode($data);
// $total = count(local_video_directory_get_videos(0, null, null, $search));
//             $videos = local_video_directory_get_videos($order, $videodata->start, $videodata->length, $search);
// echo "<pre>";
print_r(json_encode( array_values($videos), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

// return json_encode( array_values($videos),
// JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
