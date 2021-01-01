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
 * @copyright   2020 Safat Shahin <safatshahin@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
require_once(__DIR__.'/classes/debate_constants.php');
require_once (__DIR__.'/../../lib/outputcomponents.php');

global $DB, $USER;

// Course_module ID, or
$id = optional_param('id', 0, PARAM_INT);

// ... module instance id.
$d  = optional_param('d', 0, PARAM_INT);

if ($id) {
    $cm             = get_coursemodule_from_id('debate', $id, 0, false, MUST_EXIST);
    $course         = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('debate', array('id' => $cm->instance), '*', MUST_EXIST);
    $positive_response = $DB->get_records('debate_response', array('courseid' => $course->id, 'debateid' => $moduleinstance->id, 'cmid' => $cm->id, 'responsetype' => debate_constants::MOD_DEBATE_POSITIVE), '', '*');
    $negative_response = $DB->get_records('debate_response', array('courseid' => $course->id, 'debateid' => $moduleinstance->id, 'cmid' => $cm->id, 'responsetype' => debate_constants::MOD_DEBATE_NEGATIVE), '', '*');
} else if ($d) {
    $moduleinstance = $DB->get_record('debate', array('id' => $d), '*', MUST_EXIST);
    $course         = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm             = get_coursemodule_from_instance('debate', $moduleinstance->id, $course->id, false, MUST_EXIST);
    $positive_response = $DB->get_records('debate_response', array('courseid' => $course->id, 'debateid' => $moduleinstance->id, 'cmid' => $cm->id, 'responsetype' => debate_constants::MOD_DEBATE_POSITIVE), '', '*');
    $negative_response = $DB->get_records('debate_response', array('courseid' => $course->id, 'debateid' => $moduleinstance->id, 'cmid' => $cm->id, 'responsetype' => debate_constants::MOD_DEBATE_NEGATIVE), '', '*');
} else {
    print_error(get_string('missingidandcmid', 'mod_debate'));
}
require_login($course, true, $cm);
$modulecontext = context_module::instance($cm->id);

//$event = \mod_debate\event\course_module_viewed::create(array(
//    'objectid' => $moduleinstance->id,
//    'context' => $modulecontext
//));
//$event->add_record_snapshot('course', $course);
//$event->add_record_snapshot('debate', $moduleinstance);
//$event->trigger();

$PAGE->set_url('/mod/debate/debate.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);
//$PAGE->set_pagelayout('incourse');
//$PAGE->set_pagetype('mod-debate-debate');
//$node = $PAGE->settingsnav->find('mod-debate-debate', navigation_node::TYPE_SETTING);
//if ($node) {
//    $node->make_active();
//}

$content = file_rewrite_pluginfile_urls($moduleinstance->intro, 'pluginfile.php', $modulecontext->id, 'mod_debate', 'intro', null);
$formatoptions = new stdClass;
$formatoptions->noclean = true;
$formatoptions->overflowdiv = true;
$formatoptions->context = $modulecontext;
$content = format_text($content, $moduleinstance->introformat, $formatoptions);
$moduleinstance->intro = $content;

$positive = array();
foreach ($positive_response as $pos) {
    $user = $DB->get_record('user', array('id' => (int)$pos->userid), '*', MUST_EXIST);
    $pos->user_full_name = $user->firstname . ' ' . $user->lastname;
    $userpicture = new user_picture($user);
    $pos->user_profile_image = $userpicture->get_url($PAGE)->out(false);
    $positive[] = (array)$pos;
}
$negative = array();
foreach ($negative_response as $neg) {
    $user = $DB->get_record('user', array('id' => (int)$pos->userid), '*', MUST_EXIST);
    $neg->user_full_name = $user->firstname . ' ' . $user->lastname;
    $userpicture = new user_picture($user);
    $neg->user_profile_image = $userpicture->get_url($PAGE)->out(false);
    $negative[] = (array)$neg;
}
$moduleinstance->positive = $positive;
$moduleinstance->negative = $negative;

//js
$user_full_name = $USER->firstname . ' ' . $USER->lastname;
$user_image = new user_picture($USER);
$user_image_url = $user_image->get_url($PAGE)->out(false);
//response type
$response_allowed = $moduleinstance->responsetype;
$positive_response_count = count($positive_response);
$negative_response_count = count($negative_response);
$PAGE->requires->js_call_amd('mod_debate/debate_view', 'init', [$user_full_name, $user_image_url,
    $USER->id, $course->id, $moduleinstance->id, $cm->id, $response_allowed,
    $positive_response_count, $negative_response_count]);
echo $OUTPUT->header();

$output = $PAGE->get_renderer('mod_debate');
echo $output->render_debate_page($moduleinstance);

echo $OUTPUT->footer();
