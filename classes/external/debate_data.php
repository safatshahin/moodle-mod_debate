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
 * @copyright   2021 Safat Shahin <safatshahin@yahoo.com>
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
use mod_debate\debate_constants;

/**
 * Class debate_data.
 *
 * @package mod_debate
 * @copyright   2021 Safat Shahin <safatshahin@yahoo.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class debate_data extends external_api {

    /**
     * Check parameters.
     *
     * @return external_function_parameters
     */
    public static function check_debate_response_allocation_parameters(): external_function_parameters {
        return new external_function_parameters(
            array(
                'debateid' => new external_value(PARAM_INT, '', 1),
                'attribute' => new external_value(PARAM_TEXT, '', 1),
                'userid' => new external_value(PARAM_INT, '', 1)
            )
        );
    }

    /**
     * If allowed from Ajax.
     *
     * @return bool
     */
    public static function check_debate_response_allocation_is_allowed_from_ajax(): bool {
        return true;
    }

    /**
     * Endpoint returns.
     *
     * @return external_single_structure
     */
    public static function check_debate_response_allocation_returns(): external_single_structure {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_BOOL, 'Status true or false'),
                'message' => new external_value(PARAM_TEXT, 'Messages')
            )
        );
    }

    /**
     * Check response allocation for the user.
     *
     * @param int $debateid
     * @param string $attribute
     * @param int $userid
     * @return array
     */
    public static function check_debate_response_allocation(int $debateid, string $attribute, int $userid): array {
        global $DB;
        $params = self::validate_parameters(
            self::check_debate_response_allocation_parameters(),
            array(
                'attribute' => $attribute,
                'userid' => $userid,
                'debateid' => $debateid
            )
        );

        $result = array(
            'result' => true,
            'message' => ''
        );

        // Site admin and manage teams capability will be able to add responses without checking any rules.
        $debate = $DB->get_record('debate', array('id' => (int)$params['debateid']), '*', MUST_EXIST);
        $course = $DB->get_record('course', array('id' => $debate->course), '*', MUST_EXIST);
        $coursemodule = get_coursemodule_from_instance('debate', $debate->id, $course->id, false, MUST_EXIST);
        $modulecontext = context_module::instance($coursemodule->id);
        if (is_siteadmin($params['userid']) || has_capability('mod/debate:manageteams', $modulecontext)) {
            return $result;
        }

        $positiveresponsecount = $DB->count_records('debate_response', array('courseid' => $debate->course,
            'debateid' => $debate->id, 'userid' => $params['userid'], 'responsetype' => debate_constants::MOD_DEBATE_POSITIVE));
        $negativeresponsecount = $DB->count_records('debate_response', array('courseid' => $debate->course,
            'debateid' => $debate->id, 'userid' => $params['userid'], 'responsetype' => debate_constants::MOD_DEBATE_NEGATIVE));

        switch ($debate->responsetype) {
            case debate_constants::MOD_DEBATE_RESPONSE_UNLIMITED:
                // Unlimited response.
                break;
            case debate_constants::MOD_DEBATE_RESPONSE_ONLY_ONE:
                // One response in any one side.
                if ($positiveresponsecount > 0 || $negativeresponsecount > 0) {
                    $result['result'] = false;
                    $result['message'] = get_string('one_response_any_side', 'mod_debate');
                }
                break;
            case debate_constants::MOD_DEBATE_RENPONSE_ONE_PER_SECTIOM:
                // One response in each side.
                if ($positiveresponsecount > 0 && $negativeresponsecount > 0) {
                    $result['result'] = false;
                    $result['message'] = get_string('one_response_each_side', 'mod_debate');
                } else if ($attribute === 'positive' && $positiveresponsecount > 0) {
                    $result['result'] = false;
                    $result['message'] = get_string('one_response_each_side', 'mod_debate');
                } else if ($attribute === 'negative' && $negativeresponsecount > 0) {
                    $result['result'] = false;
                    $result['message'] = get_string('one_response_each_side', 'mod_debate');
                }
                break;
            case debate_constants::MOD_DEBATE_RESPONSE_USE_TEAMS:
                // Use debate teams.
                $teamsallocation = new debate_teams($course->id, $params['debateid']);
                $teamresult = $teamsallocation->check_teams_allocation($params);
                $result['result'] = $teamresult['result'];
                $result['message'] = $teamresult['message'];
                break;
        }

        return $result;
    }

    /**
     * Check parameters.
     *
     * @return external_function_parameters
     */
    public static function add_debate_respose_parameters(): external_function_parameters {
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

    /**
     * Check if Ajax allowed.
     *
     * @return bool
     */
    public static function add_debate_respose_is_allowed_from_ajax(): bool {
        return true;
    }

    /**
     * Return parameters.
     *
     * @return external_single_structure
     */
    public static function add_debate_respose_returns(): external_single_structure {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_BOOL, 'Status true or false'),
                'id' => new external_value(PARAM_TEXT, 'id of the insert')
            )
        );
    }

    /**
     * Add debate response.
     *
     * @param int $courseid
     * @param int $debateid
     * @param string $response
     * @param int $responsetype
     * @param int $id
     * @return array
     */
    public static function add_debate_respose(int $courseid, int $debateid,
            string $response, int $responsetype, int $id = 0): array {
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

        $data = (object) $params;
        if (empty($params['id'])) {
            $debateresponse = new debate_response();
            $debateresponse->construct_debate_response($data);
            $addresponse = $debateresponse->save();
            if ($addresponse) {
                $result['result'] = true;
                $result['id'] = $debateresponse->id;
            }
        } else {
            $debateresponse = new debate_response($params['id']);
            $debateresponse->construct_debate_response($data);
            $updateresponse = $debateresponse->save();
            if ($updateresponse) {
                $result['result'] = true;
                $result['id'] = $debateresponse->id;
            }
        }

        return $result;
    }

    /**
     * Check parameters.
     *
     * @return external_function_parameters
     */
    public static function delete_debate_respose_parameters(): external_function_parameters {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, '', 1),
                'debateid' => new external_value(PARAM_INT, '', 1),
                'id' => new external_value(PARAM_INT, '', 1)
            )
        );
    }

    /**
     * Check if Ajax allowed.
     *
     * @return bool
     */
    public static function delete_debate_respose_is_allowed_from_ajax(): bool {
        return true;
    }

    /**
     * Return parameters.
     *
     * @return external_single_structure
     */
    public static function delete_debate_respose_returns(): external_single_structure {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_BOOL, 'Status true or false')
            )
        );
    }

    /**
     * Delete debate response.
     *
     * @param int $courseid
     * @param int $debateid
     * @param int $id
     * @return array
     */
    public static function delete_debate_respose(int $courseid, int $debateid, int $id): array {
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
        $debateresponse = new debate_response($params['id']);
        $result['result'] = $debateresponse->delete();

        return $result;
    }

    /**
     * Check parameter.
     *
     * @return external_function_parameters
     */
    public static function find_debate_respose_parameters(): external_function_parameters {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, '', 1),
                'debateid' => new external_value(PARAM_INT, '', 1),
                'response' => new external_value(PARAM_RAW, '', 1),
                'responsetype' => new external_value(PARAM_INT, '', 1),
                'sentiment' => new external_value(PARAM_RAW, '', 1),
            )
        );
    }

    /**
     * If ajax is allowed.
     *
     * @return bool
     */
    public static function find_debate_respose_is_allowed_from_ajax(): bool {
        return true;
    }

    /**
     * Find matching rebase response returns.
     *
     * @return external_single_structure
     */
    public static function find_debate_respose_returns(): external_single_structure {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_BOOL, 'Status true or false'),
                'data' => new external_value(PARAM_TEXT, 'Matching data'),
                'sentiment' => new external_value(PARAM_TEXT, 'Sentiment data')
            )
        );
    }

    /**
     * Find debate response.
     *
     * @param int $courseid
     * @param int $debateid
     * @param string $response
     * @param int $responsetype
     * @return array
     */
    public static function find_debate_respose(int $courseid, int $debateid, string $response, int $responsetype, string $sentiments): array {
        $params = self::validate_parameters(
            self::find_debate_respose_parameters(),
            array(
                'courseid' => $courseid,
                'debateid' => $debateid,
                'response' => $response,
                'responsetype' => $responsetype,
                'sentiment' => $sentiments,
            )
        );

        $sentimentresponse = [];

        // Sentiment analysis.
        $sentiments = json_decode($sentiments);
        foreach ($sentiments as $sentiment) {
            $results = $sentiment->results;
            foreach ($results as $result) {
                if ($result->match) {
                    $data = new stdClass();
                    $data->sentiment = get_string($sentiment->label, 'mod_debate');
                    $sentimentresponse [] = $data;
                }
            }
        }

        $result = array(
            'result' => true,
            'data' => null,
            'sentiment' => json_encode($sentimentresponse)
        );

        /*
         * [{"label":"identity_attack","results":[{"probabilities":{"0":0.96781021356582642,"1":0.032189793884754181},"match":false}]},
         * {"label":"insult","results":[{"probabilities":{"0":0.052625525742769241,"1":0.94737452268600464},"match":true}]},
         * {"label":"obscene","results":[{"probabilities":{"0":0.40184497833251953,"1":0.59815496206283569},"match":null}]},
         * {"label":"severe_toxicity","results":[{"probabilities":{"0":0.99823951721191406,"1":0.0017605222528800368},"match":false}]},
         * {"label":"sexual_explicit","results":[{"probabilities":{"0":0.80438029766082764,"1":0.19561971724033356},"match":null}]},
         * {"label":"threat","results":[{"probabilities":{"0":0.94552105665206909,"1":0.054478902369737625},"match":false}]},
         * {"label":"toxicity","results":[{"probabilities":{"0":0.02452419325709343,"1":0.97547584772109985},"match":true}]}] debate_view.js:60:52

         * */

        $result['data'] = json_encode(debate_response::find_matching_response($params));
        return $result;
    }

}

