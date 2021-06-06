<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Private debate module utility functions
 *
 * @package    mod_debate
 * @copyright  2021 Safat Shahin <safatshahin@yahoo.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

global $CFG;
require_once($CFG->libdir.'/filelib.php');
require_once($CFG->libdir.'/resourcelib.php');
require_once($CFG->dirroot.'/mod/debate/lib.php');

/**
 * File browsing support class
 */
class debate_content_file_info extends file_info_stored {

    /**
     * Returns parent file_info instance.
     *
     * @return file_info|null
     */
    public function get_parent() {
        if ($this->lf->get_filepath() === '/' and $this->lf->get_filename() === '.') {
            return $this->browser->get_file_info($this->context);
        }
        return parent::get_parent();
    }

    /**
     * Returns localised visible name.
     *
     * @return string
     */
    public function get_visible_name() {
        if ($this->lf->get_filepath() === '/' and $this->lf->get_filename() === '.') {
            return $this->topvisiblename;
        }
        return parent::get_visible_name();
    }

}

/**
 * Get editor options.
 *
 * @param context $context
 * @return array
 */
function debate_get_editor_options($context) {
    global $CFG;
    return array('subdirs' => 1, 'maxbytes' => $CFG->maxbytes, 'maxfiles' => -1, 'changeformat' => 1,
            'context' => $context, 'noclean' => 1, 'trusttext' => 0);
}

