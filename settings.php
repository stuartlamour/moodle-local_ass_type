<?php
// This file is part of Moodle - https://moodle.org/
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
 * Plugin administration pages are defined here.
 *
 * @package     local_assess_type
 * @category    admin
 * @copyright   2024 onwards University College London {@link https://www.ucl.ac.uk/}
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author      Alex Yeung <k.yeung@ucl.ac.uk>
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $ADMIN->add(
        'localplugins',
        new admin_category('local_assess_type_settings', new lang_string('pluginname', 'local_assess_type'))
    );
    $settings = new admin_settingpage('managelocalassesstype', new lang_string('settings:pagename', 'local_assess_type'));

    // Setting to enable/disable the plugin.
    $settings->add(new admin_setting_configcheckbox(
        'local_assess_type/enabled',
        get_string('settings:enable', 'local_assess_type'),
        get_string('settings:enable:desc', 'local_assess_type'),
        '1'
    ));

    // LTI types that can be identified as summative.
    $settings->add(new admin_setting_configmultiselect(
        'local_assess_type/ltitypes',
        get_string('config:ltitypes', 'local_assess_type'),
        get_string('config:ltitypes:desc', 'local_assess_type'),
        [],
        \local_assess_type\config::get_all_lti_types()
    ));

    $ADMIN->add('localplugins', $settings);
}
