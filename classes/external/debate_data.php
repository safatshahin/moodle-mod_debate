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
 * webservices for mod_debate.
 *
 * @package     mod_debate
 * @copyright   2021 Safat Shahin <safatshahin@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_debate\external;
defined('MOODLE_INTERNAL') || die;

global $CFG;
require_once($CFG->libdir.'/externallib.php');
require_once($CFG->dirroot.'/webservice/externallib.php');
require_once($CFG->dirroot.'/lib/completionlib.php');

use context_module;
use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use mod_debate\debate_response;
use stdClass;
use context_system;
use mod_debate\debate_teams;

class debate_data extends external_api {

    public static function check_debate_response_allocation_parameters() {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, '', 1),
                'debateid' => new external_value(PARAM_INT, '', 1),
                'debatetype' => new external_value(PARAM_INT, '', 1),
                'attribute' => new external_value(PARAM_TEXT, '', 1),
                'positive_response' => new external_value(PARAM_INT, '', 1),
                'negative_response' => new external_value(PARAM_INT, '', 1),
                'userid' => new external_value(PARAM_INT, '', 1)
            )
        );
    }

    public static function check_debate_response_allocation_is_allowed_from_ajax() {
        return true;
    }

    public static function check_debate_response_allocation_returns() {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_BOOL, 'Status true or false'),
                'message' => new external_value(PARAM_TEXT, 'Messages')
            )
        );
    }

    public static function check_debate_response_allocation($courseid, $debateid, $debatetype, $attribute,
                                                            $positive_response, $negative_response, $userid) {
        global $DB;
        $params = self::validate_parameters(
            self::check_debate_response_allocation_parameters(),
            array(
                'courseid' => $courseid,
                'debateid' => $debateid,
                'debatetype' => $debatetype,
                'attribute' => $attribute,
                'positive_response' => $positive_response,
                'negative_response' => $negative_response,
                'userid' => $userid
            )
        );
        $result = array(
            'result' => true,
            'message' => ''
        );

        //site admin and manage teams capability will be able to add responses without checking any rules
        $debate = $DB->get_record('debate', array('id' => (int)$params['debateid']), '*', MUST_EXIST);
        $course = $DB->get_record('course', array('id' => $debate->course), '*', MUST_EXIST);
        $course_module = get_coursemodule_from_instance('debate', $debate->id, $course->id, false, MUST_EXIST);
        $modulecontext = context_module::instance($course_module->id);
        if (is_siteadmin($params['userid']) || has_capability('mod/debate:manageteams', $modulecontext)) {
            return $result;
        }

        switch ($params["debatetype"]) {
            case 0:
                // UNLIMITED RESPONSE
                break;
            case 1:
                // ONE RESPONSE IN ANY ONE SIDE
                if ($params["positive_response"] > 0 || $params["negative_response"] > 0) {
                    $result['result'] = false;
                    $result['message'] = get_string('one_response_any_side', 'mod_debate');
                }
                break;
            case 2:
                // ONE RESPONSE IN EACH SIDE
                if ($params["positive_response"] > 0 && $params["negative_response"] > 0) {
                    $result['result'] = false;
                    $result['message'] = get_string('one_response_each_side', 'mod_debate');
                } else if ($attribute === 'positive' && $params["positive_response"] > 0) {
                    $result['result'] = false;
                    $result['message'] = get_string('one_response_each_side', 'mod_debate');
                } else if ($attribute === 'negative' && $params["negative_response"] > 0) {
                    $result['result'] = false;
                    $result['message'] = get_string('one_response_each_side', 'mod_debate');
                }
                break;
            case 3:
                // USE DEBATE TEAMS
                $teams_allocation = new debate_teams($params['courseid'], $params['debateid']);
                $team_result = $teams_allocation->check_response_allocation($params);
                $result['result'] = $team_result['result'];
                $result['message'] = $team_result['message'];
                break;
        }

        return $result;
    }

    public static function add_debate_respose_parameters() {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, '', 1),
                'debateid' => new external_value(PARAM_INT, '', 1),
                'response' => new external_value(PARAM_RAW, '', 1),
                'responsetype' => new external_value(PARAM_INT, '', 1),
                'id' => new external_value(PARAM_INT, '', 0)
            )
        );
    }

    public static function add_debate_respose_is_allowed_from_ajax() {
        return true;
    }

    public static function add_debate_respose_returns() {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_BOOL, 'Status true or false'),
                'id' => new external_value(PARAM_TEXT, 'id of the insert')
            )
        );
    }

    public static function add_debate_respose($courseid, $debateid, $response, $responsetype, $id = null) {
        $params = self::validate_parameters(
            self::add_debate_respose_parameters(),
            array(
                'courseid' => $courseid,
                'debateid' => $debateid,
                'response' => $response,
                'responsetype' => $responsetype,
                'id' => $id
            )
        );
        $result = array(
            'result' => false,
            'id' => null
        );
        if (empty($params['id'])) {
            $data = (object) $params;
            $debate_response = new debate_response();
            $debate_response->construct_debate_response($data);
            $add_response = $debate_response->save();
            if ($add_response) {
                $result['result'] = true;
                $result['id'] = $debate_response->id;
            }
        } else {
            $data = (object) $params;
            $debate_response = new debate_response($params['id']);
            $debate_response->construct_debate_response($data);
            $update_response = $debate_response->save();
            if ($update_response) {
                $result['result'] = true;
                $result['id'] = $debate_response->id;
            }
        }

        return $result;
    }

    public static function delete_debate_respose_parameters() {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, '', 1),
                'debateid' => new external_value(PARAM_INT, '', 1),
                'id' => new external_value(PARAM_INT, '', 1)
            )
        );
    }

    public static function delete_debate_respose_is_allowed_from_ajax() {
        return true;
    }

    public static function delete_debate_respose_returns() {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_BOOL, 'Status true or false')
            )
        );
    }

    public static function delete_debate_respose($courseid, $debateid, $id) {
        $params = self::validate_parameters(
            self::delete_debate_respose_parameters(),
            array(
                'courseid' => $courseid,
                'debateid' => $debateid,
                'id' => $id
            )
        );
        $result = array(
            'result' => false
        );
        $debate_response = new debate_response($params['id']);
        $result['result'] = $debate_response->delete();

        return $result;
    }

    public static function find_debate_respose_parameters() {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, '', 1),
                'debateid' => new external_value(PARAM_INT, '', 1),
                'response' => new external_value(PARAM_RAW, '', 1),
                'responsetype' => new external_value(PARAM_INT, '', 1)
            )
        );
    }

    public static function find_debate_respose_is_allowed_from_ajax() {
        return true;
    }

    public static function find_debate_respose_returns() {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_BOOL, 'Status true or false'),
                'data' =>new external_value(PARAM_TEXT, 'Matching data')
            )
        );
    }

    public static function find_debate_respose($courseid, $debateid, $response, $responsetype) {
        $params = self::validate_parameters(
            self::find_debate_respose_parameters(),
            array(
                'courseid' => $courseid,
                'debateid' => $debateid,
                'response' => $response,
                'responsetype' => $responsetype
            )
        );
        $result = array(
            'result' => true,
            'data' => null
        );

        $result['data'] = json_encode(debate_response::find_matching_response($params));
        return $result;
    }


}
