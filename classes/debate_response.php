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

use context_module;
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
    public $cmid;

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
        $debate_response = $DB->get_record('debate_response', array('id' => $id));
        if (!empty($debate_response)) {
            $this->id = $debate_response->id;
            $this->courseid = $debate_response->courseid;
            $this->debateid = $debate_response->debateid;
            $this->userid = $debate_response->userid;
            $this->response = $debate_response->response;
            $this->responsetype = $debate_response->responsetype;
            $this->timecreated = $debate_response->timecreated;
            $this->timemodified = $debate_response->timemodified;
        }
    }

    /**
     * Constructs the actual debate_response object given either a $DB object or Moodle form data.
     * @param $debate_response
     */
    public function construct_debate_response($debate_response) {
        if (!empty($debate_response)) {
            $this->courseid = $debate_response->courseid;
            $this->debateid = $debate_response->debateid;
            $this->response = $debate_response->response;
            $this->responsetype = $debate_response->responsetype;
        }
    }

    /**
     * Delete the debate_response.
     * @return bool
     */
    public function delete(): bool {
        global $DB;
        $delete_success = false;
        if (!empty($this->id)) {
            $delete_success = $DB->delete_records('debate_response', array('id' => $this->id));
            if ($delete_success) {
                $event_success = self::calculate_completion(true);
                if ($event_success) {
                    self::after_delete();
                } else {
                    self::update_error();
                }
                $delete_success = $event_success;
            }
        }
        return $delete_success;
    }

    /**
     * Save or create debate_response.
     * @return bool
     */
    public function save(): bool {
        global $DB, $USER;
        $save_success = false;
        if (!empty($this->id)) {
            $this->timemodified = time();
            $save_success = $DB->update_record('debate_response', $this);
            if($save_success) {
                $event_success = self::calculate_completion();
                if ($event_success) {
                    self::after_update();
                } else {
                    self::update_error();
                }
                $save_success = $event_success;
            }
        } else {
            $this->userid = $USER->id;
            $this->timecreated = time();
            $this->id = $DB->insert_record('debate_response', $this);
            if (!empty($this->id)) {
                $event_success = self::calculate_completion();
                if ($event_success) {
                    self::after_create();
                } else {
                    self::update_error();
                }
                $save_success = $event_success;
            }
        }
        return $save_success;
    }

    /**
     * calculate completion for debate instance.
     * @param bool $delete
     * @return bool
     */
    public function calculate_completion($delete = false): bool {
        global $DB;
        $result = false;
        $debate = $DB->get_record('debate', array('id' => (int)$this->debateid), '*', MUST_EXIST);
        $course = $DB->get_record('course', array('id' => (int)$this->courseid), '*', MUST_EXIST);
        $course_module = get_coursemodule_from_instance('debate', $debate->id, $course->id, false, MUST_EXIST);
        if ($course_module) {
            $this->cmid = $course_module->id;
            $user_response_count = $DB->count_records_select('debate_response',
                'debateid = :debateid AND courseid = :courseid AND userid = :userid',
                array('debateid' => (int)$debate->id, 'courseid' => (int)$course->id, 'userid' => $this->userid), 'COUNT("id")');
            $completion = new \completion_info($course);
            if ($delete) {
                if ($completion->is_enabled($course_module) == COMPLETION_TRACKING_AUTOMATIC &&
                    (int)$debate->debateresponsecomcount > 0) {
                    if ($user_response_count >= (int)$debate->debateresponsecomcount) {
                        $completion->update_state($course_module, COMPLETION_COMPLETE, $this->userid);
                    } else {
                        $current = $completion->get_data($course_module, false, $this->userid);
                        $current->completionstate = COMPLETION_INCOMPLETE;
                        $current->timemodified    = time();
                        $current->overrideby      = null;
                        $completion->internal_set_data($course_module, $current);
                    }
                }
            } else {
                if ($completion->is_enabled($course_module) == COMPLETION_TRACKING_AUTOMATIC
                    && (int)$debate->debateresponsecomcount > 0 &&
                    $user_response_count >= (int)$debate->debateresponsecomcount) {
                    $completion->update_state($course_module, COMPLETION_COMPLETE, $this->userid);
                }
            }
            $result = true;
        }

        return $result;
    }

    /**
     * create event for debate_response.
     */
    public function after_create() {
        global $USER;
        $event = debate_response_added::create(
            array(
                'context' => context_module::instance($this->cmid),
                'userid' => $USER->id,
                'objectid' => $this->id,
                'other' => array(
                    'debateid' => $this->debateid
                )
            )
        );
        $event->trigger();
    }

    /**
     * update event for debate_response.
     */
    public function after_update() {
        global $USER;
        $event = debate_response_updated::create(
            array(
                'context' => context_module::instance($this->cmid),
                'userid' => $USER->id,
                'objectid' => $this->id,
                'other' => array(
                    'debateid' => $this->debateid
                )
            )
        );
        $event->trigger();
    }

    /**
     * delete event for debate_response.
     */
    public function after_delete() {
        global $USER;
        $event = debate_response_deleted::create(
            array(
                'context' => context_module::instance($this->cmid),
                'userid' => $USER->id,
                'objectid' => $this->id,
                'other' => array(
                    'debateid' => $this->debateid
                )
            )
        );
        $event->trigger();
    }

    /**
     * error event for debate_response.
     */
    public function update_error() {
        global $USER;
        $event = debate_response_error::create(
            array(
                'userid' => $USER->id
            )
        );
        $event->trigger();
    }

    /**
     * find matching responses for debate_response.
     * @param $params
     * @return array
     */
    public static function find_matching_response($params): array {
        global $DB;
        $datas = $DB->get_records('debate_response', array('courseid' => $params['courseid'],
            'debateid' => $params['debateid'], 'responsetype' => $params['responsetype']), '', 'response');

        $exclude_words = array('i','a','about','an','and','are','as','at','be','by','com','de','en','for',
            'from','how','in','is','it','la','of','on','or','that','the','this','to','was','what','when','where',
            'who','will','with','und','the','www', "such", "have", "then");

        $clean_response = preg_replace('/\s\s+/i', '', $params['response']);
        $clean_response = trim($clean_response);
        $clean_response = preg_replace('/[^a-zA-Z0-9 -]/', '', $clean_response);
        $clean_response = strtolower($clean_response);

        //all the words from typed response
        preg_match_all('/\b.*?\b/i', $clean_response, $response_words);
        $response_words = $response_words[0];

        //remove invalid words
        foreach ($response_words as $key => $word) {
            if ( $word == '' || in_array(strtolower($word), $exclude_words) || strlen($word) <= 2 ) {
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
        return $final_data;
    }

}
