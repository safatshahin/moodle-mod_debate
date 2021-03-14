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
 * Manage teams form of mod_debate.
 *
 * @package     mod_debate
 * @copyright   2021 Safat Shahin <safatshahin@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require($CFG->dirroot.'/mod/debate/classes/debate_teams_page.php');
require($CFG->dirroot.'/mod/debate/classes/output/forms/debate_teams_form.php');

use \core\output\notification;

require_login();
if (!is_siteadmin()) {
    print_error('nopermissions', 'error');
}

$context = context_system::instance();

$id = optional_param('id', null, PARAM_INT);
$cmid = required_param('cmid', PARAM_INT);
$response = required_param('response', PARAM_INT);

$courseid = null;
$cm = get_coursemodule_from_id('debate', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$moduleinstance = $DB->get_record('debate', array('id' => $cm->instance), '*', MUST_EXIST);
$courseid = $course->id;

$data =  new debate_teams_page($id);
$editoroptions = array(
    'subdirs' => 0,
    'noclean' => true,
    'context' => $context,
    'removeorphaneddrafts' => true,
);

if ($response == 0) {
    $title = get_string('edit_negative_team', 'mod_debate');
} else if ($response == 1) {
    $title = get_string('edit_positive_team', 'mod_debate');
}
$PAGE->set_url('/mod/debate/debate_teams_form_page.php', array('id' => $id, 'cmid' => $cmid, 'response' => $response));
$PAGE->set_pagelayout('admin');
$PAGE->set_context($context);
$PAGE->navbar->add($title);
$PAGE->set_title($title);
$PAGE->set_heading(get_string("pluginname", 'mod_debate'));

//$returnurl = new moodle_url($CFG->wwwroot . '/mod/debate/debate_teams_page.php', array('id' => $cmid, 'response' => $response));
$args = array(
    'editoroptions' => $editoroptions,
    'data' => $data,
    'courseid' => $courseid,
    'cmid' => $cmid,
    'response' => $response
);

$debate_team_form = new debate_teams_form(null, $args);

if ($debate_team_form->is_cancelled()) {
//    redirect($returnurl);
} else if ($savedata = $debate_team_form->get_data()) {
    $returnurl = new moodle_url($CFG->wwwroot . '/mod/debate/debate_teams_page.php', array('cmid' => $savedata->cmid, 'response' => $savedata->response));
    $new_debate_team =  new debate_teams_page();
    if (empty($savedata->id)) {
        $savedata->active = 1;
        $savedata->courseid = $course->id;
        $savedata->debateid = $moduleinstance->id;
        $savedata->responsetype = $response;
    }
    if(!empty($savedata->groupselection)) {
        $savedata->groupselection = implode(",", $savedata->groupselection);
    }

    $new_debate_team->construct_teams_page($savedata);
    if ($new_debate_team->save()) {
        $message = get_string('debate_team_saved', 'mod_debate');
        $messagestyle = notification::NOTIFY_SUCCESS;
        redirect($returnurl, $message, null, $messagestyle);
    } else {
        $message = get_string('debate_team_save_error', 'mod_debate');
        $messagestyle = notification::NOTIFY_ERROR;
        redirect($returnurl, $message, null, $messagestyle);
    }
    redirect($returnurl, $message, null, $messagestyle);
}

$params = [
    'title' => $title,
    'formhtml' => $debate_team_form->render()
];

echo $OUTPUT->header();

$renderer = $PAGE->get_renderer('mod_debate');

echo $renderer->render_form_page($params);

echo $OUTPUT->footer();

