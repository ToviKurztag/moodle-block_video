<?php


define('AJAX_SCRIPT', true);
require __DIR__ . '/../../../config.php';
if (!isloggedin() || isguestuser()) {
 	print_error('No permissions');
}


if ($id = optional_param('delete', null, PARAM_INT)) {
    // $DB->delete_records('videostreambookmarks', ['id' => $id, 'userid' => $USER->id]);.
    $DB->delete_records('block_video_bookmarks', ['id' => $id]);
    die('1');
}
$videotype = required_param('videotype', PARAM_INT);
$video_id = required_param('id', PARAM_RAW);
$videoposition = required_param('bookmarkposition', PARAM_FLOAT);
$text = required_param('bookmarkname', PARAM_RAW);
// $bookmarkflag = required_param('bookmarkflag', PARAM_RAW);
$userid = $USER->id;
$timemodified = time();
$permission = optional_param('teacherbookmark', null, PARAM_INT) == 1 ? 'public' : 'private';
$object = compact('userid', 'video_id', 'videoposition', 'text',  'permission', 'timemodified');
$id = $DB->insert_record('block_video_bookmarks', $object);
echo json_encode($DB->get_record('block_video_bookmarks', ['id' => $id]));
