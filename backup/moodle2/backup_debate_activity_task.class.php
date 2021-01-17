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
 * Defines backup_debate_activity_task class.
 *
 * @package     mod_debate
 * @copyright   2020 Safat Shahin <safatshahin@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/debate/backup/moodle2/backup_debate_stepslib.php');

/**
 * Provides the steps to perform one complete backup of the Debate instance.
 */
class backup_debate_activity_task extends backup_activity_task {

    /**
     * No specific settings for this activity.
     */
    protected function define_my_settings() {
    }

    /**
     * Defines a backup step to store the instance data in the debate.xml file.
     */
    protected function define_my_steps() {
        $this->add_step(new backup_debate_activity_structure_step('debate_structure', 'debate.xml'));
    }

    /**
     * Encodes URLs to the index.php and view.php scripts.
     *
     * @param string $content some HTML text that eventually contains URLs to the activity instance scripts.
     * @return string $content The content with the URLs encoded.
     */
    static public function encode_content_links($content) {

        global $CFG;

        $base = preg_quote($CFG->wwwroot.'/mod/debate', '#');

        // Link to the list of debates.
        $pattern = "#(".$base."\/index.php\?id\=)([0-9]+)#";
        $content = preg_replace($pattern, '$@DEBATEINDEX*$2@$', $content);

        // Link to debate view by moduleid.
        $pattern = "#(".$base."\/view.php\?id\=)([0-9]+)#";
        $content = preg_replace($pattern, '$@DEBATEVIEWBYID*$2@$', $content);

        // Link to debate response by moduleid.
        $pattern = "#(".$base."\/debate.php\?id\=)([0-9]+)#";
        $content = preg_replace($pattern, '$@@DEBATEDEBATEBYID*$2@$', $content);

        return $content;
    }
}
