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

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
   
    $settings->add(new admin_setting_heading(
        'block_video/generalsetting',
        get_string('generalsetting', 'block_video'), '')
    );
    $choices = ['list' => get_string('list', 'block_video'),
                'table' => get_string('table', 'block_video')];

    $settings->add(new admin_setting_configselect(
        'block_video/defaultshowingvideos',
        get_string('defaultshowingvideos', 'block_video'),
        get_string('defaultshowingvideos_help', 'block_video'), '', $choices)
    );
    $settings->add(new admin_setting_configcheckbox(
        'block_video/addpublicbookmarks',
        get_string('addpublicbookmarks', 'block_video'),
        get_string('addpublicbookmarks_help', 'block_video'), 1)
    );
    $settings->add(new admin_setting_configcheckbox(
        'block_video/hiddenzoomvideos',
        get_string('hiddenzoomvideos', 'block_video'),
        get_string('hiddenzoomvideos_help', 'block_video'), 0)
    );
}
