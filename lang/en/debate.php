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
 * @copyright   2021 Safat Shahin <safatshahin@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Debate';
$string['pluginadministration'] = 'Debate Admin';
$string['modulename'] = 'Debate';
$string['modulenameplural'] = 'Debates';

//mod form
$string['debatename'] = 'Name';
$string['topicheader'] = 'Topic';
$string['debate_topic'] = 'Debate topic';
$string['showinmodule'] = 'Show description in the module page';
$string['showinmodule_help'] = 'Show the description in the view module page after the debate topic';
$string['unlimited_response'] = 'Allow unlimited response';
$string['one_response'] = 'Allow one response in any one side';
$string['two_response'] = 'Allow one response in each side';
$string['user_response'] = 'User response type';
$string['reset_debate_attempts'] = 'Delete debate responses';
$string['debateresponsecom'] = 'Students must post responses';
$string['debateresponsecomgroup'] = 'Require response';

//access
$string['debate:addinstance'] = 'Add a new debate instance';
$string['debate:view'] = 'View debate content';
$string['debate:deleteanyresponse'] = 'Delete any debate response';
$string['debate:deleteownresponse'] = 'Delete own debate response';
$string['debate:updateownresponse'] = 'Update own debate response';

//view
$string['pros'] = 'Positive';
$string['cons'] = 'Negative';
$string['pros_response_count'] = 'Positive response count';
$string['cons_response_count'] = 'Negative response count';
$string['join_debate'] = 'Join/View debate';
$string['grade_debater'] = 'Grade debaters';

//debate
$string['save'] = 'Save';
$string['update'] = 'Update';
$string['cancel'] = 'Cancel';
$string['possible_match'] = 'Possible matching responses';
$string['no_possible_match'] = 'No matching responses';
$string['confirm_delete'] = 'Are you sure you want to delete this response?';
$string['edit_mode_active'] = 'Either the edit mode is active or no more response is allowed.';
$string['empty_response'] = 'Cannot save empty response';
$string['error_add'] = 'Error updating response, please check the typed response, it can only accept text';
$string['error_delete'] = 'Error deleting response, please check database for more info';
$string['edit'] = 'Edit';
$string['delete'] = 'Delete';

//reset
$string['attemptsdeleted'] = 'Debate responses deleted';

//privacy
$string['privacy:metadata:debate_response:courseid'] = 'The ID of the debate course';
$string['privacy:metadata:debate_response:debateid'] = 'The ID of the debate instance';
$string['privacy:metadata:debate_response:userid'] = 'The ID of the debate response user';
$string['privacy:metadata:debate_response:response'] = 'The response of the user for the debate activity';
$string['privacy:metadata:debate_response:responsetype'] = 'The response type for debate activity from the user';
$string['privacy:metadata:debate_response:timecreated'] = 'The timestamp indicating when a user first recorded an interaction with the debate Course';
$string['privacy:metadata:debate_response:timemodified'] = 'The timestamp indicating when a user last recorded an interaction with the debate Course';
$string['privacy:metadata:debate_response'] = 'Information about the response of the debate topic for a debate Course';

//event
$string['event_response_added'] = 'Debate response added';
$string['event_response_updated'] = 'Debate response updated';
$string['event_response_error'] = 'Error from debate response';
$string['event_response_deleted'] = 'Debate response deleted';
