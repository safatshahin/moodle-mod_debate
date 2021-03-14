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
 * List teams table of mod_debate.
 *
 * @package     mod_debate
 * @copyright   2021 Safat Shahin <safatshahin@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require($CFG->dirroot . '/mod/debate/classes/debate_teams_page.php');
require($CFG->dirroot . '/mod/debate/classes/output/tables/debate_teams_table.php');

use \core\output\notification;

require_login();

// Course_module ID, or
$id = optional_param('id', 0, PARAM_INT);
$cmid = optional_param('cmid', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_TEXT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$response = required_param('response', PARAM_INT);

if ($cmid) {
    $cm = get_coursemodule_from_id('debate', $cmid, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('debate', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    print_error(get_string('missingidandcmid', 'mod_debate'));
}

require_login($course, true, $cm);
$modulecontext = context_module::instance($cm->id);

require_capability('mod/debate:manageteams', $modulecontext);

$PAGE->set_url('/mod/debate/debate_teams_page.php', array('id' => $id, 'cmid' => $cmid, 'response' => $response));
$PAGE->set_title(get_string('debate_teams', 'mod_debate'));
$response_text = '';
if ($response == 0) {
    $response_text = get_string('manage_negative_team', 'mod_debate');
} else if ($response == 1) {
    $response_text = get_string('manage_positive_team', 'mod_debate');
}
$PAGE->set_heading($response_text);
$PAGE->set_context($modulecontext);
$PAGE->navbar->add($response_text, new moodle_url('/mod/debate/debate_teams_page.php', array('id' => $id, 'cmid' => $cmid, 'response' => $response)));
$returnurl = new moodle_url('/mod/debate/debate_teams_page.php', array('id' => $id, 'cmid' => $cmid, 'response' => $response));

$debate_team = new debate_teams_page($id);

//table logic
if (!empty($debate_team->id)) {
    if ($action == 'delete') {
        $PAGE->url->param('action', 'delete');
        $a = new stdClass();
        $a->name = $debate_team->name;
        if ($confirm and confirm_sesskey()) {
            if ($debate_team->delete()) {
                $message = get_string('debate_team_deleted', 'mod_debate', $a);
                $messagestyle = notification::NOTIFY_SUCCESS;
            } else {
                $message = get_string('debate_team_delete_failed', 'mod_debate', $a);
                $messagestyle = notification::NOTIFY_ERROR;
            }
            redirect($returnurl, $message, null, $messagestyle);
        }
        $strheading = get_string('delete_debate_team', 'mod_debate');
        $PAGE->navbar->add($strheading);
        $PAGE->set_title($strheading);
        $PAGE->set_heading($strheading);

        echo $OUTPUT->header();

        $yesurl = new moodle_url($CFG->wwwroot . '/mod/debate/debate_teams_page.php', array(
            'id' => $debate_team->id, 'cmid' => $cmid, 'response' => $debate_team->responsetype, 'action' => 'delete', 'confirm' => 1, 'sesskey' => sesskey()
        ));
        $message = get_string('delete_debate_team_confirmation', 'mod_debate', $a);
        echo $OUTPUT->confirm($message, $yesurl, $returnurl);
        echo $OUTPUT->footer();
        die;
    }
    if ($action == 'show') {
        if ($confirm and confirm_sesskey()) {
            $a = new stdClass();
            $a->name = $debate_team->name;
            $message = get_string('debate_team_active', 'mod_debate', $a);
            $messagestyle = notification::NOTIFY_SUCCESS;
            if (!$debate_team->active) {
                $debate_team->active = 1;
                if (!$debate_team->save()) {
                    $message = get_string('debate_team_active_error', 'mod_debate', $a);
                    $messagestyle = notification::NOTIFY_ERROR;
                }
            }
            redirect($returnurl, $message, null, $messagestyle);
        }
    } else if ($action == 'hide') {
        if ($confirm and confirm_sesskey()) {
            $a = new stdClass();
            $a->name = $debate_team->name;
            $message = get_string('debate_team_deactive', 'mod_debate', $a);
            $messagestyle = notification::NOTIFY_SUCCESS;
            // Don't bother doing anything if it's already inactive.
            if ($debate_team->active) {
                $debate_team->active = 0;
                if (!$debate_team->save()) {
                    $message = get_string('debate_team_deactive_error', 'mod_debate', $a);
                    $messagestyle = notification::NOTIFY_ERROR;
                }
            }
            redirect($returnurl, $message, null, $messagestyle);
        }
    }
}

$debate_teams_table = new debate_teams_table('debate_teams_table', $response, $moduleinstance->id, $cm->id);

$params = [
    'editurl' => new moodle_url($CFG->wwwroot . '/mod/debate/debate_teams_form_page.php', array('cmid' => $cm->id, 'response' => $response)),
    'tablehtml' => $debate_teams_table->export_for_template()
];

echo $OUTPUT->header();

$renderer = $PAGE->get_renderer('mod_debate');

echo $renderer->render_table_page($params);

echo $OUTPUT->footer();
