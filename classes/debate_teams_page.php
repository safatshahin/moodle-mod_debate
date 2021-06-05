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
 * Manage teams page of mod_debate.
 *
 * @package     mod_debate
 * @copyright   2021 Safat Shahin <safatshahin@yahoo.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_debate;

defined('MOODLE_INTERNAL') || die();

/**
 * Class debate_teams_page.
 * A class to help with debate teams page data.
 */
class debate_teams_page {

    public $name = null;
    public $courseid = 0;
    public $debateid = 0;
    public $responsetype = 0;
    public $responseallowed = 0;
    public $groupselection = 0;
    public $active = 0;
    public $timecreated = 0;
    public $timemodified = 0;

    /**
     * debate_teams_page constructor.
     * Builds object if $id provided.
     *
     * @param int $id
     */
    public function __construct(int $id = 0) {
        if (!empty($id)) {
            $this->load_teams_page($id);
        }
    }

    /**
     * Constructs the actual debate_teams_page object given either a $DB object or Moodle form data.
     *
     * @param \stdClass $teamspage
     */
    public function construct_teams_page(\stdClass $teamspage): void {
        if (!empty($teamspage)) {
            $this->id = $teamspage->id;
            $this->name = $teamspage->name;
            $this->courseid = $teamspage->courseid;
            $this->debateid = $teamspage->debateid;
            $this->responsetype = $teamspage->responsetype;
            $this->responseallowed = $teamspage->responseallowed;
            $this->groupselection = $teamspage->groupselection;
            $this->active = $teamspage->active;
        }
    }

    /**
     * Gets the specified debate_team and loads it into the object.
     *
     * @param int $id
     */
    private function load_teams_page(int $id): void {
        global $DB;
        $teamspage = $DB->get_record('debate_teams', array('id' => $id));
        $this->construct_teams_page($teamspage);
    }

    /**
     * Delete the debate_team.
     * @return bool
     */
    public function delete(): bool {
        global $DB;
        if (!empty($this->id)) {
            return $DB->delete_records('debate_teams', array('id' => $this->id));
        }
        return false;
    }

    /**
     * Deactivate/activate the debate_team.
     * @return bool
     */
    public function activate_deactivate(): bool {
        global $DB;
        if (!empty($this->id)) {
            $this->timemodified = time();
            $savesuccess = $DB->update_record('debate_teams', $this);
            if ($savesuccess) {
                return true;
            }
        }
        return false;
    }

    /**
     * Save or create debate_team.
     * @return bool
     */
    public function save(): bool {
        global $DB;
        $savesuccess = false;
        if (!empty($this->id)) {
            $this->timemodified = time();
            $savesuccess = $DB->update_record('debate_teams', $this);
        } else {
            $this->timecreated = time();
            $this->timemodified = time();
            $this->id = $DB->insert_record('debate_teams', $this);
            if (!empty($this->id)) {
                $savesuccess = true;
            }
        }
        return $savesuccess;
    }
}
