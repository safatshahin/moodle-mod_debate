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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Library of interface functions and constants.
 *
 * @package     mod_debate
 * @copyright   2021 Safat Shahin <safatshahin@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
function debate_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;

        default: return null;
    }
}

/**
 * Implementation of the function for printing the form elements that control
 * whether the course reset functionality affects the debate response.
 *
 * @param $mform the course reset form that is being built.
 */
function debate_reset_course_form_definition($mform) {
    $mform->addElement('header', 'debateheader', get_string('modulenameplural', 'mod_debate'));
    $mform->addElement('advcheckbox', 'reset_debate_attempts',
        get_string('reset_debate_attempts', 'mod_debate'));
    $mform->addElement('advcheckbox', 'reset_debate_teams',
        get_string('reset_debate_teams', 'mod_debate'));
}


/**
 * Course reset form defaults.
 * @return array the defaults.
 */
function debate_reset_course_form_defaults($course) {
    return array(
        'reset_debate_attempts' => 0,
        'reset_debate_teams' => 0
    );
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * @param $data
 * @return array status array
 */
function debate_reset_userdata($data) {
    global $DB;

    $componentstr = get_string('modulenameplural', 'mod_debate');
    $status = array();
    if (!empty($data->reset_debate_attempts)) {
        $DB->delete_records_select('debate_response', '', array($data->courseid));
        $status[] = array(
            'component' => $componentstr,
            'item' => get_string('attemptsdeleted', 'mod_debate'),
            'error' => false);
    }
    if (!empty($data->reset_debate_teams)) {
        $DB->delete_records_select('debate_teams', '', array($data->courseid));
        $status[] = array(
            'component' => $componentstr,
            'item' => get_string('teamsdeleted', 'mod_debate'),
            'error' => false);
    }
    return $status;
}

/**
 * List the actions that correspond to a view of this module.
 * This is used by the participation report.
 *
 * Note: This is not used by new logging system. Event with
 *       crud = 'r' and edulevel = LEVEL_PARTICIPATING will
 *       be considered as view action.
 *
 * @return array
 */
function debate_get_view_actions() {
    return array('view','view all');
}

/**
 * List the actions that correspond to a post of this module.
 * This is used by the participation report.
 *
 * Note: This is not used by new logging system. Event with
 *       crud = ('c' || 'u' || 'd') and edulevel = LEVEL_PARTICIPATING
 *       will be considered as post action.
 *
 * @return array
 */
function debate_get_post_actions() {
    return array('update', 'add');
}

/**
 * Saves a new instance of the mod_debate into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $moduleinstance An object from the form.
 * @param mod_debate_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function debate_add_instance($moduleinstance, $mform = null) {
    global $CFG, $DB;
    require_once("$CFG->libdir/resourcelib.php");

    $cmid = $moduleinstance->coursemodule;

    $moduleinstance->timecreated = time();

    $id = $DB->insert_record('debate', $moduleinstance);
    $moduleinstance->id = $id;

    $DB->set_field('course_modules', 'instance', $id, array('id'=>$cmid));

    $completiontimeexpected = !empty($moduleinstance->completionexpected) ? $moduleinstance->completionexpected : null;
    \core_completion\api::update_completion_date_event($cmid, 'debate', $id, $completiontimeexpected);

    return $id;
}

/**
 * Updates an instance of the mod_debate in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $moduleinstance An object from the form in mod_form.php.
 * @param mod_debate_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function debate_update_instance($moduleinstance, $mform = null) {
    global $CFG, $DB;
    require_once("$CFG->libdir/resourcelib.php");

    $cmid        = $moduleinstance->coursemodule;
    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

    $DB->update_record('debate', $moduleinstance);

    $completiontimeexpected = !empty($moduleinstance->completionexpected) ? $moduleinstance->completionexpected : null;
    \core_completion\api::update_completion_date_event($cmid, 'debate', $moduleinstance->id, $completiontimeexpected);

    return true;
}

/**
 * Removes an instance of the mod_debate from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function debate_delete_instance($id) {
    global $DB;

    if (!$debate = $DB->get_record('debate', array('id'=>$id))) {
        return false;
    }
    if (!$cm = get_coursemodule_from_instance('debate', $debate->id)) {
        return false;
    }
    if (!$course = $DB->get_record('course', array('id'=>$cm->course))) {
        return false;
    }

    $context = context_module::instance($cm->id);

    // now get rid of all files
    $fs = get_file_storage();
    $fs->delete_area_files($context->id);

    \core_completion\api::update_completion_date_event($cm->id, 'debate', $debate->id, null);

    $DB->delete_records('debate_response', array('debateid' => $id));
    $DB->delete_records('debate', array('id' => $id));

    return true;
}

/**
 * Returns the lists of all browsable file areas within the given module context.
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@see file_browser::get_file_info_context_module()}.
 *
 * @package     mod_debate
 * @category    files
 *
 * @param stdClass $course.
 * @param stdClass $cm.
 * @param stdClass $context.
 * @return string[].
 */
function debate_get_file_areas($course, $cm, $context) {
    return array();
}

/**
 * File browsing support for mod_debate file areas.
 *
 * @package     mod_debate
 * @category    files
 *
 * @param file_browser $browser.
 * @param array $areas.
 * @param stdClass $course.
 * @param stdClass $cm.
 * @param stdClass $context.
 * @param string $filearea.
 * @param int $itemid.
 * @param string $filepath.
 * @param string $filename.
 * @return file_info Instance or null if not found.
 */
function debate_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 * Serves the files from the mod_debate file areas.
 *
 * @package     mod_debate
 * @category    files
 *
 * @param stdClass $course The course object.
 * @param stdClass $cm The course module object.
 * @param stdClass $context The mod_debate's context.
 * @param string $filearea The name of the file area.
 * @param array $args Extra arguments (itemid, path).
 * @param bool $forcedownload Whether or not force download.
 * @param array $options Additional options affecting the file serving.
 */
function debate_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, $options = array()) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    require_login($course, true, $cm);
    send_file_not_found();
}

/**
 * Mark the activity completed (if required) and trigger the course_module_viewed event.
 *
 * @param  stdClass $debate       debate object
 * @param  stdClass $course     course object
 * @param  stdClass $cm         course module object
 * @param  stdClass $context    context object
 * @since Moodle 3.0
 */
function debate_view($debate, $course, $cm, $context) {
    global $DB, $USER;
    // Trigger course_module_viewed event.
    $params = array(
        'context' => $context,
        'objectid' => $debate->id
    );

    $event = \mod_debate\event\course_module_viewed::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('debate', $debate);
    $event->trigger();

    // Completion.
    $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
    $user_response_count = $DB->count_records_select('debate_response', 'debateid = :debateid AND courseid = :courseid AND userid = :userid',
        array('debateid' => (int)$debate->id, 'courseid' => (int)$course->id, 'userid' => $USER->id), 'COUNT("id")');
    if ($user_response_count >= (int)$debate->debateresponsecomcount) {
        $completion->update_state($cm, COMPLETION_COMPLETE, $USER->id);
    } else {
        $current = $completion->get_data($cm, false, $USER->id);
        $current->completionstate = COMPLETION_INCOMPLETE;
        $current->timemodified    = time();
        $current->overrideby      = null;
        $completion->internal_set_data($cm, $current);
    }

}

