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
 * @copyright   2021 Safat Shahin <safatshahin@yahoo.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_debate;

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
     * @param int $courseid
     * @param int $debateid
     */
    public function __construct(int $courseid = 0, int $debateid = 0) {
        if (!empty($courseid) && !empty($debateid)) {
            $this->construct_teams($courseid, $debateid);
        }
    }

    /**
     * Constructs the actual debate_teams object given the specific data.
     *
     * @param int $courseid
     * @param int $debateid
     */
    private function construct_teams(int $courseid, int $debateid): void {
        $this->courseid = $courseid;
        $this->debateid = $debateid;
    }

    /**
     * Gets the number of team member according to the response type passed.
     *
     * @param int $responsetype
     * @return int
     */
    public function get_team_member_count(int $responsetype): int {
        global $DB;
        $teammembercount = 0;
        $debateteamgroups = $DB->get_records('debate_teams', array('courseid' => $this->courseid,
            'debateid' => $this->debateid, 'responsetype' => $responsetype, 'active' => $this->active));
        $teamgroups = array();
        foreach ($debateteamgroups as $debateteamgroup) {
            $groups = explode(",", $debateteamgroup->groupselection);
            foreach ($groups as $group) {
                $teamgroups[] = $group;
            }
        }
        foreach ($teamgroups as $teamgroup) {
            $groupmembercount = $DB->get_record('groups_members',
                    array('groupid' => (int)$teamgroup), 'count(id) as usercount');
            if ((int)$groupmembercount->usercount > 0) {
                $teammembercount = $teammembercount + (int)$groupmembercount->usercount;
            }
        }
        return $teammembercount;
    }

    /**
     * Checks whether the requested response is allowed for the user in the team.
     *
     * @param array $params
     * @return array
     */
    public function check_teams_allocation(array $params): array {
        global $DB;
        $result = array(
            'result' => false,
            'message' => get_string('no_team', 'mod_debate')
        );
        if ($params['attribute'] === 'positive') {
            $responsetype = 1;
        } else {
            $responsetype = 0;
        }
        // Count current responses.
        $responsecount = $DB->count_records('debate_response', array('courseid' => $this->courseid,
            'debateid' => $this->debateid, 'userid' => $params['userid'], 'responsetype' => $responsetype));
        // Check if user is in the team and did not exceed the allowed response number.
        $debateteamgroups = $DB->get_records('debate_teams', array('courseid' => $this->courseid,
            'debateid' => $this->debateid, 'responsetype' => $responsetype, 'active' => $this->active));
        foreach ($debateteamgroups as $debateteamgroup) {
            $groups = explode(",", $debateteamgroup->groupselection);
            foreach ($groups as $group) {
                $groupmembers = $DB->get_records('groups_members', array('groupid' => (int)$group));
                foreach ($groupmembers as $groupmember) {
                    if ((int)$groupmember->userid == $params['userid']) {
                        if ($responsecount < $debateteamgroup->responseallowed ||
                                $debateteamgroup->responseallowed == 0) {
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
