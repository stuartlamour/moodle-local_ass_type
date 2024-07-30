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
 * Lib.
 *
 * @package    local_assess_type
 * @copyright  2024 onwards University College London {@link https://www.ucl.ac.uk/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Stuart Lamour <s.lamour@ucl.ac.uk>
 */

/**
 * Check if an activity is sits mapped.
 *
 * @param int $cmid The activity id.
 */
function local_assess_type_sitsmapped($cmid): bool {
    global $DB;

    $dbman = $DB->get_manager();
    $table = 'local_sitsgradepush_mapping';
    if ($dbman->table_exists($table)) {
        $conditions = ['sourceid' => $cmid, 'sourcetype' => 'mod'];
        if ($DB->get_record($table, $conditions, 'id')) {
            return true;
        }
    }
    return false;
}

/**
 * Check if an activity can be summative.
 *
 * @param string $modtype The activity type e.g. quiz.
 */
function local_assess_type_canbesummative($modtype): bool {
    // Activites which can be marked summative.
    $modarray = [
        'assign',
        'quiz',
        'workshop',
        'turnitintooltwo',
    ];

    if (in_array($modtype, $modarray)) {
        return true;
    }
    return false;
}

/**
 * Add Formative or Summative select options to mods.
 *
 * @param moodleform $formwrapper
 * @param MoodleQuickForm $mform
 */
function local_assess_type_coursemodule_standard_elements($formwrapper, $mform) {
    global $DB;

    $cm = $formwrapper->get_current();
    // Check list of mods where this is enabled.
    if (!local_assess_type_canbesummative($cm->modulename)) {
        return; // Exit if not enabled.
    }

    // Flag if new cm.
    $newcm = true;
    if ($cmid = $cm->coursemodule) {
        $newcm = false;
    }
    // Flag if sits mapped.
    $sitsmapped = false;
    if ($cmid) {
        $sitsmapped = local_assess_type_sitsmapped($cmid);
    }

    // Mform element.
    $options = [];
    $options[''] = get_string('defaultoption', 'local_assess_type');
    $options['0'] = get_string('formativeoption', 'local_assess_type');
    $options['1'] = get_string('summativeoption', 'local_assess_type');
    $options['2'] = get_string('dummyoption', 'local_assess_type');;
    $attributes = [];
    $attributes['required'] = 'required';

    // Disable changes when sits mapped.
    if ($sitsmapped) {
        $attributes['disabled'] = 'disabled';
    }
    $select = $mform->createElement(
        'select',
        'assessment_type',
        get_string('fieldlabel', 'local_assess_type'),
        $options,
        $attributes
    );

    // Set to summative when sits mapped.
    if ($sitsmapped) {
        $select->setSelected(1);
    }

    // Set existing option from db (when not sits mapped or new).
    if (!$sitsmapped && $cmid) {
        if ($record = $DB->get_record('local_assess_type', ['cmid' => $cmid], 'type')) {
            $select->setSelected($record->type);
        }
    }

    // Link to edit when cm exists.
    $link = '';
    if ($cmid) {
        $url = new \moodle_url('/local/sitsgradepush/dashboard.php', ['id' => $cm->course]);
        $link = '<br>
        <a href="' . $url . '" target="_blank">'
        . get_string('editinsits', 'local_assess_type') .
        '</a>';
    }

    $info = $mform->createElement('html',
    '<div class="col-md-9 offset-md-3 pb-3">'
    . get_string('info', 'local_assess_type') .
    $link .
    '</div>');

    // Add form elements to the dom.
    $mform->insertElementBefore($select, 'introeditor');
    $mform->insertElementBefore($info, 'introeditor');
}

/**
 * Save Formative or Summative select options.
 *
 * @param stdClass $data Data from the form submission.
 * @param stdClass $course The course.
 */
function local_assess_type_coursemodule_edit_post_actions($data, $course): stdClass {
    // Check assessment_type is in $data.
    // It is impossible to not be set in GUI, but Behat throws a wobly without this.
    if (!isset($data->assessment_type)) {
        return $data;
    }

    global $DB;
    $table = 'local_assess_type';

    // Record for update/insert.
    $r = new \stdClass();
    $r->type = $data->assessment_type;
    $r->cmid = $data->coursemodule;
    $r->courseid = $course->id;

    // If record exists.
    if ($record = $DB->get_record($table, ['cmid' => $r->cmid], 'id, type')) {
        // If record has changed.
        if ($record->type != $r->type) {
            $r->id = $record->id;
            $DB->update_record(
                $table,
                $r,
                $bulk = false
            );
        }
    } else {
        $DB->insert_record(
            $table,
            $r,
            $returnid = false,
            $bulk = false
        );
    }
    return $data;
}
