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
 * Plugin strings are defined here.
 *
 * @package     mod_debate
 * @category    string
 * @copyright   2021 Safat Shahin <safatshahin@yahoo.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Debate';
$string['pluginadministration'] = 'Debate Admin';
$string['modulename'] = 'Debate';
$string['modulenameplural'] = 'Debates';
$string['missingidandcmid'] = 'Missing course module ID';

// Debate form.
$string['debatename'] = 'Name';
$string['topicheader'] = 'Topic';
$string['debate_topic'] = 'Debate topic';
$string['showinmodule'] = 'Show description in the module page';
$string['showinmodule_help'] = 'Show the description in the view module page after the debate topic';
$string['unlimited_response'] = 'Allow unlimited response';
$string['one_response'] = 'Allow one response in any one side';
$string['two_response'] = 'Allow one response in each side';
$string['user_response'] = 'User response type';
$string['debateresponsecom'] = 'Students must post responses';
$string['debateresponsecomgroup'] = 'Require response';
$string['use_teams'] = 'Use teams feature';

// Access.
$string['debate:addinstance'] = 'Add a new debate instance';
$string['debate:view'] = 'View debate content';
$string['debate:deleteanyresponse'] = 'Delete any debate response';
$string['debate:deleteownresponse'] = 'Delete own debate response';
$string['debate:updateownresponse'] = 'Update own debate response';
$string['debate:manageteams'] = 'Manage debate teams';

// View.
$string['pros'] = 'Positive';
$string['cons'] = 'Negative';
$string['pros_response_count'] = 'Positive response count';
$string['cons_response_count'] = 'Negative response count';
$string['join_debate'] = 'Join/View debate';
$string['grade_debater'] = 'Grade debaters';
$string['manage_teams'] = 'Manage teams';

// Debate.
$string['save'] = 'Save';
$string['update'] = 'Update';
$string['cancel'] = 'Cancel';
$string['possible_match'] = 'Possible matching responses';
$string['no_possible_match'] = 'No matching responses';
$string['confirm_delete'] = 'Are you sure you want to delete this response?';
$string['edit_mode_active'] = 'Please finish editing the response, save or cancel changes and then try adding another response.';
$string['empty_response'] = 'Cannot save empty response.';
$string['error_add'] = 'Error updating response, please check the typed response, it can only accept text.';
$string['error_delete'] = 'Error deleting response, please check as your administrator to check the database record or refresh the page.';
$string['success_delete'] = 'Successfully deleted the response';
$string['edit'] = 'Edit';
$string['delete'] = 'Delete';
$string['one_response_any_side'] = 'Only one response allowed in any one side, you can not add any more responses.';
$string['one_response_each_side'] = 'Only one response allowed in each side, you can not add any more responses as you already have a response for this side.';
$string['no_team'] = 'Your team does not allow responses in this side.';
$string['no_more_response'] = 'No more response allowed for this side from your team.';

// Reset.
$string['attemptsdeleted'] = 'Debate responses deleted';
$string['reset_debate_attempts'] = 'Delete debate responses';
$string['reset_debate_teams'] = 'Delete debate teams';
$string['teamsdeleted'] = 'Debate teams deleted';

// Privacy.
$string['privacy:metadata:debate_response:courseid'] = 'The ID of the debate course';
$string['privacy:metadata:debate_response:debateid'] = 'The ID of the debate instance';
$string['privacy:metadata:debate_response:userid'] = 'The ID of the debate response user';
$string['privacy:metadata:debate_response:response'] = 'The response of the user for the debate activity';
$string['privacy:metadata:debate_response:responsetype'] = 'The response type for debate activity from the user';
$string['privacy:metadata:debate_response:timecreated'] = 'The timestamp indicating when a user first recorded an interaction with the debate Course';
$string['privacy:metadata:debate_response:timemodified'] = 'The timestamp indicating when a user last recorded an interaction with the debate Course';
$string['privacy:metadata:debate_response'] = 'Information about the response of the debate topic for a debate Course';

// Events.
$string['event_response_added'] = 'Debate response added';
$string['event_response_added_desc'] = 'The user with id "{$a->userid}" has added a response with id "{$a->objectid}" in the debate with id "{$a->debateid}"';
$string['event_response_updated'] = 'Debate response updated';
$string['event_response_updated_desc'] = 'The user with id {$a->userid} has updated the response with id {$a->objectid} in the debate with id {$a->debateid}';
$string['event_response_error'] = 'Error from debate response';
$string['event_response_error_desc'] = 'There was an error occurred for user with id {$a->userid} while trying to update a debate response';
$string['event_response_deleted'] = 'Debate response deleted';
$string['event_response_deleted_desc'] = 'The user with id {$a->userid} has deleted the response with id {$a->objectid} in the debate with id {$a->debateid}';

// Teams.
$string['pros_user_count'] = 'Positive user count';
$string['cons_user_count'] = 'Negative user count';
$string['manage_positive_team'] = 'Manage positive teams';
$string['manage_negative_team'] = 'Manage negative teams';

// Teams table.
$string['createnew'] = 'Create new team';
$string['goback'] = 'Go back';
$string['id'] = 'ID';
$string['name'] = 'Name';
$string['status'] = 'Status';
$string['timemodified'] = 'Time modified';
$string['actions'] = 'Actions';
$string['active'] = 'Active';
$string['inactive'] = 'Inactive';

// Teams page.
$string['debate_teams'] = 'Debate teams';
$string['debate_team_deleted'] = 'Debate team: {$a->name} is successfully deleted.';
$string['debate_team_delete_failed'] = 'Debate team: {$a->name} could not be deleted.';
$string['delete_debate_team'] = 'Delete debate team';
$string['delete_debate_team_confirmation'] = 'Are you sure you want to delete debate team: {$a->name} ?';
$string['debate_team_active'] = 'Debate team: {$a->name} activated.';
$string['debate_team_active_error'] = 'Failed to activate debate team: {$a->name}.';
$string['debate_team_deactive'] = 'Debate team: {$a->name} deactivated.';
$string['debate_team_deactive_error'] = 'Failed to deactivate debate team: {$a->name}.';
$string['missingresponsetype'] = 'Missing response type';
$string['debate_team_saved'] = 'Debate team saved.';

// Teams form.
$string['name'] = 'Name';
$string['maximum_character_255'] = 'Maximum 255 characters accepted.';
$string['responseallowed'] = 'Response allowed';
$string['maximum_character_2'] = 'Maximum 2 digit numbers allowed for the number of responses';
$string['groupselection'] = 'Selects groups';
$string['edit_negative_team'] = 'Edit negative team';
$string['edit_positive_team'] = 'Edit positive team';
$string['cancel_team_form'] = 'Cancel';

// Search.
$string['search:activity'] = 'Debate - activity information';

// Sentiment.
$string['good_response'] = 'Your response is appropriate.';
$string['identity_attack'] = 'Your response is showing an identity attack.';
$string['insult'] = 'Your response is showing an insult.';
$string['obscene'] = 'Your response is showing an obscene.';
$string['severe_toxicity'] = 'Your response is showing a severe toxicity.';
$string['sexual_explicit'] = 'Your response is showing a sexual explicit.';
$string['threat'] = 'Your response is showing a threat.';
$string['toxicity'] = 'Your response is showing a toxicity.';
