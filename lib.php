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
 * @copyright   2020 Safat Shahin <safatshahin@gmail.com>
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
//    switch ($feature) {
//        case FEATURE_GRADE_HAS_GRADE:
//            return true;
//        case FEATURE_MOD_INTRO:
//            return true;
//        default:
//            return null;
//    }

    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;

        default: return null;
    }
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
    if($mform) {
        $moduleinstance->topicformat = $moduleinstance->topic['format'];
        $moduleinstance->topic = $moduleinstance->topic['text'];
    }

    $id = $DB->insert_record('debate', $moduleinstance);

    $DB->set_field('course_modules', 'instance', $id, array('id'=>$cmid));
    $context = context_module::instance($cmid);

    if ($mform and !empty($moduleinstance->topic['itemid'])) {
        $draftitemid = $moduleinstance->topic['itemid'];
        $moduleinstance->topic = file_save_draft_area_files($draftitemid, $context->id, 'mod_debate', 'topic', 0, page_get_editor_options($context), $moduleinstance->topic);
        $DB->update_record('debate', $moduleinstance);
    }

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
    $draftitemid = $moduleinstance->topic['itemid'];

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;
    $moduleinstance->topic = $moduleinstance->topic['text'];
    $moduleinstance->topicformat = $moduleinstance->topic['format'];

    $DB->update_record('debate', $moduleinstance);

    $context = context_module::instance($cmid);
    if ($draftitemid) {
        $moduleinstance->topic = file_save_draft_area_files($draftitemid, $context->id, 'mod_debate', 'topic', 0, page_get_editor_options($context), $moduleinstance->topic);
        $DB->update_record('debate', $moduleinstance);
    }

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

    $exists = $DB->get_record('debate', array('id' => $id));
    if (!$exists) {
        return false;
    }

    $cm = get_coursemodule_from_instance('debate', $id);
    \core_completion\api::update_completion_date_event($cm->id, 'debate', $id, null);

    $DB->delete_records('debate', array('id' => $id));

    return true;
}

/**
 * Is a given scale used by the instance of mod_debate?
 *
 * This function returns if a scale is being used by one mod_debate
 * if it has support for grading and scales.
 *
 * @param int $moduleinstanceid ID of an instance of this module.
 * @param int $scaleid ID of the scale.
 * @return bool True if the scale is used by the given mod_debate instance.
 */
function debate_scale_used($moduleinstanceid, $scaleid) {
    global $DB;

    if ($scaleid && $DB->record_exists('debate', array('id' => $moduleinstanceid, 'grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Checks if scale is being used by any instance of mod_debate.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param int $scaleid ID of the scale.
 * @return bool True if the scale is used by any mod_debate instance.
 */
function debate_scale_used_anywhere($scaleid) {
    global $DB;

    if ($scaleid and $DB->record_exists('debate', array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Creates or updates grade item for the given mod_debate instance.
 *
 * Needed by {@see grade_update_mod_grades()}.
 *
 * @param stdClass $moduleinstance Instance object with extra cmidnumber and modname property.
 * @param bool $reset Reset grades in the gradebook.
 * @return void.
 */
function debate_grade_item_update($moduleinstance, $reset=false) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    $item = array();
    $item['itemname'] = clean_param($moduleinstance->name, PARAM_NOTAGS);
    $item['gradetype'] = GRADE_TYPE_VALUE;

    if ($moduleinstance->grade > 0) {
        $item['gradetype'] = GRADE_TYPE_VALUE;
        $item['grademax']  = $moduleinstance->grade;
        $item['grademin']  = 0;
    } else if ($moduleinstance->grade < 0) {
        $item['gradetype'] = GRADE_TYPE_SCALE;
        $item['scaleid']   = -$moduleinstance->grade;
    } else {
        $item['gradetype'] = GRADE_TYPE_NONE;
    }
    if ($reset) {
        $item['reset'] = true;
    }

    grade_update('/mod/debate', $moduleinstance->course, 'mod', 'mod_debate', $moduleinstance->id, 0, null, $item);
}

/**
 * Delete grade item for given mod_debate instance.
 *
 * @param stdClass $moduleinstance Instance object.
 * @return grade_item.
 */
function debate_grade_item_delete($moduleinstance) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    return grade_update('/mod/debate', $moduleinstance->course, 'mod', 'debate',
                        $moduleinstance->id, 0, null, array('deleted' => 1));
}

/**
 * Update mod_debate grades in the gradebook.
 *
 * Needed by {@see grade_update_mod_grades()}.
 *
 * @param stdClass $moduleinstance Instance object with extra cmidnumber and modname property.
 * @param int $userid Update grade of specific user only, 0 means all participants.
 */
function debate_update_grades($moduleinstance, $userid = 0) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    // Populate array of grade objects indexed by userid.
    $grades = array();
    grade_update('/mod/debate', $moduleinstance->course, 'mod', 'mod_debate', $moduleinstance->id, 0, $grades);
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
