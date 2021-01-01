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

global $USER;

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

$PAGE->set_url('/mod/debate/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

$content = file_rewrite_pluginfile_urls($moduleinstance->intro, 'pluginfile.php', $modulecontext->id, 'mod_debate', 'intro', null);
$formatoptions = new stdClass;
$formatoptions->noclean = true;
$formatoptions->overflowdiv = true;
$formatoptions->context = $modulecontext;
$content = format_text($content, $moduleinstance->introformat, $formatoptions);
$moduleinstance->intro = $content;

$moduleinstance->grade_capability = false;
$usercontext = context_system::instance();
if (has_capability('mod/debate:addinstance', $usercontext)) {
    $moduleinstance->grade_capability = true;
    $moduleinstance->gradeurl = 'debate_grade.php?id='.$cm->id.'&userid='.$USER->id;
}

$positive = 0;
foreach ($positive_response as $pos) {
    $positive++;
}
$negative = 0;
foreach ($negative_response as $neg) {
    $negative++;
}
$moduleinstance->positive = $positive;
$moduleinstance->negative = $negative;
$moduleinstance->debateurl = 'debate.php?id='.$cm->id;
echo $OUTPUT->header();

$output = $PAGE->get_renderer('mod_debate');
echo $output->render_debate_view($moduleinstance);

echo $OUTPUT->footer();
