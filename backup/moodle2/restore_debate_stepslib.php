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
 * Class for restore
 *
 * @package     mod_debate
 * @copyright   2021 Safat Shahin <safatshahin@yahoo.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Define all the restore steps that will be used by the restore_debate_activity_structure_step
 */
class restore_debate_activity_structure_step extends restore_activity_structure_step {

    /**
     * Define the structure of the restore workflow.
     */
    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('debate', '/activity/debate');

        if ($userinfo) {
            $paths[] = new restore_path_element('debate_response', '/activity/debate/debate_responses/debate_response');
        }
        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Process a debate restore.
     *
     * @param array $data The data in object form
     */
    protected function process_debate(array $data): void {
        global $DB;
        $data = (object)$data;
        $data->course = $this->get_courseid();
        // Insert the page record.
        $newitemid = $DB->insert_record('debate', $data);
        // Immediately after inserting "activity" record, call this.
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Process debate response
     *
     * @param array $data The data in object form
     */
    protected function process_debate_response(array $data): void {
        global $DB;
        $data = (object)$data;
        $oldid = $data->id;
        $data->debateid = $this->get_new_parentid('debate');
        $data->courseid = $this->get_courseid();
        $newitemid = $DB->insert_record('debate_response', $data);
        $this->set_mapping('debate_response', $oldid, $newitemid);
    }

    /**
     * Add page related files.
     */
    protected function after_execute(): void {
        // Add page related files, no need to match by itemname (just internally handled context).
        $this->add_related_files('mod_debate', 'intro', null);
    }

}

