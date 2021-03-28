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
 * Get the data for the teams of mod_debate.
 *
 * @package     mod_debate
 * @copyright   2021 Safat Shahin <safatshahin@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_debate;

use coding_exception;
use dml_exception;

defined('MOODLE_INTERNAL') || die();

/**
 * Class debate_teams.
 * A class to help with debate teams data.
 */
class debate_teams {

    public $courseid = null;
    public $debateid = null;
    public $active = 1;

    /**
     * debate_teams constructor.
     * Builds object if $id provided.
     * @param null $courseid
     * @param null $debateid
     */
    public function __construct($courseid = null, $debateid = null) {
        if (!empty($courseid) && !empty($debateid)) {
            $this->construct_teams($courseid, $debateid);
        }
    }

    /**
     * Constructs the actual debate_teams object given the specific data.
     * @param $courseid
     * @param $debateid
     */
    private function construct_teams($courseid, $debateid) {
        $this->courseid = $courseid;
        $this->debateid = $debateid;
    }

    /**
     * Gets the number of team member according to the response type passed.
     * @param $responsetype
     * @return int
     * @throws dml_exception
     */
    public function get_team_member_count($responsetype) {
        global $DB;
        $team_member_count = 0;
        $debate_team_groups = $DB->get_records('debate_teams', array('courseid' => $this->courseid,
            'debateid' => $this->debateid, 'responsetype' => $responsetype, 'active' => $this->active));
        $team_groups = array();
        foreach ($debate_team_groups as $debate_team_group) {
            $groups = explode(",", $debate_team_group->groupselection);
            foreach ($groups as $group) {
                $team_groups[] = $group;
            }
        }
        foreach ($team_groups as $team_group) {
            $group_member_count = $DB->get_record('groups_members', array('groupid' => (int)$team_group), 'count(id) as usercount');
            if ((int)$group_member_count->usercount > 0) {
                $team_member_count = $team_member_count + (int)$group_member_count->usercount;
            }
        }
        return $team_member_count;
    }

    /**
     * Checks whether the requested response is allowed for the user.
     * @param $params
     * @return array
     * @throws dml_exception|coding_exception
     */
    public function check_response_allocation($params) {
        global $DB;
        $result = array(
            'result' => false,
            'message' => get_string('no_team', 'mod_debate')
        );
        if ($params['attribute'] === 'positive') {
            $responsetype = 1;
            $responseallowed = $params['positive_response'];
            } else {
            $responsetype = 0;
            $responseallowed = $params['negative_response'];
        }
        //check if user is in the team and did not exceed the allowed response number
        $debate_team_groups = $DB->get_records('debate_teams', array('courseid' => $this->courseid,
            'debateid' => $this->debateid, 'responsetype' => $responsetype, 'active' => $this->active));
        foreach ($debate_team_groups as $debate_team_group) {
            $groups = explode(",", $debate_team_group->groupselection);
            foreach ($groups as $group) {
                $group_members = $DB->get_records('groups_members', array('groupid' => (int)$group));
                foreach ($group_members as $group_member) {
                    if ((int)$group_member->userid == $params['userid']) {
                        if ($responseallowed < $debate_team_group->responseallowed ||
                            $debate_team_group->responseallowed == 0) {
                            $result['result'] = true;
                            $result['message'] = '';
                        } else {
                            $result['message'] = get_string('no_more_response', 'mod_debate');
                        }
                    }
                }
            }
        }

        return $result;
    }
}
