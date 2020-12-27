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
 * @copyright   2020 Safat Shahin <safatshahin@gmail.com>
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
                'cmid' => new external_value(PARAM_INT, '', 1),
                'respose' => new external_value(PARAM_TEXT, '', 1),
                'responsetype' => new external_value(PARAM_INT, '', 1)
            )
        );
    }

    public static function add_debate_respose_is_allowed_from_ajax() {
        return true;
    }

    public static function add_debate_respose_returns() {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_BOOL, 'Status true or false')
            )
        );
    }

    public static function add_debate_respose($courseid, $debateid, $cmid, $respose, $responsetype) {
        global $DB;
        $params = self::validate_parameters(
            self::add_debate_respose_parameters(),
            array(
                'courseid' => $courseid,
                'debateid' => $debateid,
                'cmid' => $cmid,
                'respose' => $respose,
                'responsetype' => $responsetype
            )
        );
        $result = array(
            'result' => false,
        );

        $debate_response = new stdClass();
        $debate_response->courseid = $params['courseid'];
        $debate_response->debateid = $params['debateid'];
        $debate_response->cmid = $params['cmid'];
        $debate_response->respose = $params['respose'];
        $debate_response->responsetype = $params['responsetype'];
        $debate_response->timecreated = time();
        $debate_response->timemodified = time();

        $result['result'] = $DB->insert_record('debate_response', $debate_response);
        return $result;
    }

}
