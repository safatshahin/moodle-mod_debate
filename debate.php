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
 * Prints an instance of mod_debate.
 *
 * @package     mod_debate
 * @copyright   2021 Safat Shahin <safatshahin@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
require_once (__DIR__.'/../../lib/outputcomponents.php');
global $DB, $OUTPUT, $PAGE, $USER;
use mod_debate\debate_constants;

// Course_module ID, or
$id = optional_param('id', 0, PARAM_INT);

// ... module instance id.
$d  = optional_param('d', 0, PARAM_INT);

if ($id) {
    $cm             = get_coursemodule_from_id('debate', $id, 0, false, MUST_EXIST);
    $course         = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('debate', array('id' => $cm->instance), '*', MUST_EXIST);
    $positive_response = $DB->get_records('debate_response', array('courseid' => $course->id, 'debateid' => $moduleinstance->id, 'responsetype' => debate_constants::MOD_DEBATE_POSITIVE), '', '*');
    $negative_response = $DB->get_records('debate_response', array('courseid' => $course->id, 'debateid' => $moduleinstance->id, 'responsetype' => debate_constants::MOD_DEBATE_NEGATIVE), '', '*');
} else if ($d) {
    $moduleinstance = $DB->get_record('debate', array('id' => $d), '*', MUST_EXIST);
    $course         = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm             = get_coursemodule_from_instance('debate', $moduleinstance->id, $course->id, false, MUST_EXIST);
    $positive_response = $DB->get_records('debate_response', array('courseid' => $course->id, 'debateid' => $moduleinstance->id, 'responsetype' => debate_constants::MOD_DEBATE_POSITIVE), '', '*');
    $negative_response = $DB->get_records('debate_response', array('courseid' => $course->id, 'debateid' => $moduleinstance->id, 'responsetype' => debate_constants::MOD_DEBATE_NEGATIVE), '', '*');
} else {
    print_error(get_string('missingidandcmid', 'mod_debate'));
}
require_login($course, true, $cm);
$modulecontext = context_module::instance($cm->id);

require_capability('mod/debate:view', $modulecontext);

// Completion and trigger events.
debate_view($moduleinstance, $course, $cm, $modulecontext);

$PAGE->set_url('/mod/debate/debate.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);
$PAGE->navbar->add(get_string('join_debate', 'mod_debate'), new moodle_url('/mod/debate/debate.php', array('id' => $cm->id)));

$content = file_rewrite_pluginfile_urls($moduleinstance->intro, 'pluginfile.php', $modulecontext->id, 'mod_debate', 'intro', null);
$formatoptions = new stdClass;
$formatoptions->noclean = true;
$formatoptions->overflowdiv = true;
$formatoptions->context = $modulecontext;
$content = format_text($content, $moduleinstance->introformat, $formatoptions);
$moduleinstance->intro = $content;

$user_positive_response_count = 0;
$user_negative_response_count = 0;
$positive = array();
foreach ($positive_response as $pos) {
    if ((int)$pos->userid == $USER->id) {
        $user_positive_response_count ++;
    }
    $user = $DB->get_record('user', array('id' => (int)$pos->userid), '*', MUST_EXIST);
    $pos->user_full_name = $user->firstname . ' ' . $user->lastname;
    $userpicture = new user_picture($user);
    $pos->user_profile_image = $userpicture->get_url($PAGE)->out(false);
    //capability in mustache
    $pos->user_capability = false;
    $pos->user_edit_capability = false;
    $pos->user_delete_capability = false;
    if ((int)$pos->userid == $USER->id && has_capability('mod/debate:updateownresponse', $modulecontext)) {
        $pos->user_edit_capability = true;
    }
    if (((int)$pos->userid == $USER->id && has_capability('mod/debate:deleteownresponse', $modulecontext)) ||
        has_capability('mod/debate:deleteanyresponse', $modulecontext)) {
        $pos->user_delete_capability = true;
    }
    if ($pos->user_edit_capability || $pos->user_delete_capability) {
        $pos->user_capability = true;
    }
    $pos->elementid = 'element'.$pos->id;
    $pos->elementidcontainer = 'element'.$pos->id.'container';
    $positive[] = (array)$pos;
}
$negative = array();
foreach ($negative_response as $neg) {
    if ((int)$neg->userid == $USER->id) {
        $user_negative_response_count ++;
    }
    $user = $DB->get_record('user', array('id' => (int)$neg->userid), '*', MUST_EXIST);
    $neg->user_full_name = $user->firstname . ' ' . $user->lastname;
    $userpicture = new user_picture($user);
    $neg->user_profile_image = $userpicture->get_url($PAGE)->out(false);
    //capability in mustache
    $neg->user_capability = false;
    $neg->user_edit_capability = false;
    $neg->user_delete_capability = false;
    if ((int)$neg->userid == $USER->id && has_capability('mod/debate:updateownresponse', $modulecontext)) {
        $neg->user_edit_capability = true;
    }
    if (((int)$neg->userid == $USER->id && has_capability('mod/debate:deleteownresponse', $modulecontext)) ||
        has_capability('mod/debate:deleteanyresponse', $modulecontext)) {
        $neg->user_delete_capability = true;
    }
    if ($neg->user_edit_capability || $neg->user_delete_capability) {
        $neg->user_capability = true;
    }
    $neg->elementid = 'element'.$neg->id;
    $neg->elementidcontainer = 'element'.$neg->id.'container';
    $negative[] = (array)$neg;
}

$moduleinstance->positive = $positive;
$moduleinstance->negative = $negative;

//js
$user_full_name = $USER->firstname . ' ' . $USER->lastname;
$user_image = new user_picture($USER);
$user_image_url = $user_image->get_url($PAGE)->out(false);
$moduleinstance->current_user_profile_image = $user_image_url;
$moduleinstance->current_user_full_name = $user_full_name;
//response type
$response_allowed = $moduleinstance->responsetype;

//capability in js
$user_edit_capability = has_capability('mod/debate:updateownresponse', $modulecontext);
$user_delete_capability = has_capability('mod/debate:deleteownresponse', $modulecontext);
$user_capability = false;
if ($user_edit_capability || $user_delete_capability) {
    $user_capability = true;
}
$PAGE->requires->js_call_amd('mod_debate/debate_view', 'init', [$user_full_name, $user_image_url,
    $USER->id, $course->id, $moduleinstance->id, $response_allowed,
    $user_positive_response_count, $user_negative_response_count,
    $user_capability, $user_edit_capability, $user_delete_capability]);
echo $OUTPUT->header();

$output = $PAGE->get_renderer('mod_debate');
echo $output->render_debate_page($moduleinstance);

echo $OUTPUT->footer();
