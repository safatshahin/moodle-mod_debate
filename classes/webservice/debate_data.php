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

namespace mod_debate\webservice;
defined('MOODLE_INTERNAL') || die;

global $CFG;
require_once("$CFG->libdir/externallib.php");
require_once("$CFG->dirroot/webservice/externallib.php");

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use context_system;
use stdClass;

class debate_data extends external_api {

    public static function add_debate_respose_parameters() {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, '', 1),
                'debateid' => new external_value(PARAM_INT, '', 1),
                'response' => new external_value(PARAM_TEXT, '', 1),
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
        global $DB, $USER;
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
            $debate_response = new stdClass();
            $debate_response->courseid = $params['courseid'];
            $debate_response->debateid = $params['debateid'];
            $debate_response->response = $params['response'];
            $debate_response->responsetype = $params['responsetype'];
            $debate_response->userid = $USER->id;
            $debate_response->timecreated = time();
            $debate_response->timemodified = time();

            $add_response = $DB->insert_record('debate_response', $debate_response, true);
            if ($add_response) {
                $result['result'] = true;
                $result['id'] = $add_response;
            }
        } else {
            $debate_response = new stdClass();
            $debate_response->id = $params['id'];
            $debate_response->courseid = $params['courseid'];
            $debate_response->debateid = $params['debateid'];
            $debate_response->response = $params['response'];
            $debate_response->responsetype = $params['responsetype'];
            $debate_response->userid = $USER->id;
            $debate_response->timemodified = time();

            $update_response = $DB->update_record('debate_response', $debate_response, true);
            if ($update_response) {
                $result['result'] = true;
                $result['id'] = $update_response;
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
        global $DB;
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

        $result['result'] = $DB->delete_records('debate_response',
                array('courseid' => $params['courseid'], 'debateid' => $params['debateid'], 'id' => $params['id']));

        return $result;
    }

    public static function find_debate_respose_parameters() {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, '', 1),
                'debateid' => new external_value(PARAM_INT, '', 1),
                'response' => new external_value(PARAM_TEXT, '', 1),
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
        global $DB;
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
            'result' => false,
            'data' => null
        );

        $datas = $DB->get_records('debate_response', array('courseid' => $params['courseid'],
            'debateid' => $params['debateid'], 'responsetype' => $params['responsetype']), '', 'response');


        $blacklist_words = array('i','a','about','an','and','are','as','at','be','by','com','de','en','for',
            'from','how','in','is','it','la','of','on','or','that','the','this','to','was','what','when','where',
            'who','will','with','und','the','www', "such", "have", "then");

        $clean_response = preg_replace('/\s\s+/i', '', $response);
        $clean_response = trim($clean_response);
        $clean_response = preg_replace('/[^a-zA-Z0-9 -]/', '', $clean_response);
        $clean_response = strtolower($clean_response);

        //all the words from typed response
        preg_match_all('/\b.*?\b/i', $clean_response, $response_words);
        $response_words = $response_words[0];

        //remove invalid words
        foreach ($response_words as $key => $word) {
            if ( $word == '' || in_array(strtolower($word), $blacklist_words) || strlen($word) <= 2 ) {
                unset($response_words[$key]);
            }
        }

        $response_word_counter = count($response_words);
        if (!empty($datas)) {
            foreach ($datas as $key => $data) {
                $data_counter = 0;
                foreach ($response_words as $response_word) {
                    if (strpos($data->response, $response_word) == false) {
                        $data_counter++;
                    }
                }
                if ($data_counter == $response_word_counter) {
                    unset($datas[$key]);
                }
            }
        }
        $final_data = array();
        foreach ($datas as $dt) {
            $final_data[] = $dt;
        }
        $result['result'] = true;
        $result['data'] = json_encode($final_data);
        return $result;
    }


}
