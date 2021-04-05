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
 * Debate response class for mod_debate.
 *
 * @package     mod_debate
 * @copyright   2021 Safat Shahin <safatshahin@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_debate;


defined('MOODLE_INTERNAL') || die;

use mod_debate\event\debate_response_added;
use mod_debate\event\debate_response_updated;
use mod_debate\event\debate_response_deleted;
use mod_debate\event\debate_response_error;

class debate_response {
    public $id;
    public $courseid;
    public $debateid;
    public $userid;
    public $response;
    public $responsetype;
    public $timecreated = 0;
    public $timemodified = 0;

    /**
     * debate_response constructor.
     * Builds object if $id provided.
     * @param $id
     */
    public function __construct(int $id = 0) {
        if (!empty($id)) {
            $this->load_debate_response($id);
        }
    }

    /**
     * Gets the specified debate_response and loads it into the object.
     * @param $id
     */
    public function load_debate_response($id) {
        global $DB;
        $debate_reponse = $DB->get_record('debate_response', array('id' => $id));
        if (!empty($debate_reponse)) {
            $this->id = $debate_reponse->id;
        }
        $this->construct_debate_response($debate_reponse);
    }

    /**
     * Constructs the actual debate_response object given either a $DB object or Moodle form data.
     * @param $debate_reponse
     */
    public function construct_debate_response($debate_reponse) {
        if (!empty($debate_reponse)) {
            $this->courseid = $debate_reponse->courseid;
            $this->debateid = $debate_reponse->debateid;
            $this->userid = $debate_reponse->userid;
            $this->response = $debate_reponse->response;
            $this->responsetype = $debate_reponse->responsetype;
        }
    }

    /**
     * Delete the debate_response.
     * @return bool
     */
    public function delete() {
        global $DB;
        if (!empty($this->id)) {
            $delete_success = $DB->delete_records('debate_response', array('id' => $this->id));
        }
        if ($delete_success) {
            self::after_delete();
        }
        return $delete_success;
    }

    /**
     * Save or create debate_response.
     * @return bool
     */
    public function save() {
        global $DB;
        $savesuccess = false;
        if (!empty($this->id)) {
            $this->timemodified = time();
            $savesuccess = $DB->update_record('debate_response', $this);
            if($savesuccess) {
                self::after_update();
            }
        } else {
            $this->timecreated = time();
            $this->timemodified = time();
            $this->id = $DB->insert_record('debate_response', $this);
            if (!empty($this->id)) {
                $savesuccess = true;
                self::after_create();
            }
        }
        if (!$savesuccess) {
            self::update_error();
        }
        return $savesuccess;
    }

    /**
     * create event for debate_response.
     * @return bool
     */
    public function after_create() {
        global $USER;
        $event = debate_response_added::create(
            array(
                'userid' => $USER->id,
                'objectid' => $this->id
            )
        );
        $event->trigger();
    }

    /**
     * update event for debate_response.
     * @return bool
     */
    public function after_update() {
        global $USER;
        $event = debate_response_updated::create(
            array(
                'userid' => $USER->id,
                'objectid' => $this->id
            )
        );
        $event->trigger();
    }

    /**
     * delete event for debate_response.
     * @return bool
     */
    public function after_delete() {
        global $USER;
        $event = debate_response_deleted::create(
            array(
                'userid' => $USER->id,
                'objectid' => $this->id
            )
        );
        $event->trigger();
    }

    /**
     * error event for debate_response.
     * @return bool
     */
    public function update_error() {
        global $USER;
        $event = debate_response_error::create(
            array(
                'userid' => $USER->id,
                'objectid' => $this->id
            )
        );
        $event->trigger();
    }

}
