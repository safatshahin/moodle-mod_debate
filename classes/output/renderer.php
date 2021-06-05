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
 * Renderer for mod_debate.
 *
 * @package     mod_debate
 * @copyright   2021 Safat Shahin <safatshahin@yahoo.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_debate\output;

defined('MOODLE_INTERNAL') || die;

use mod_debate;
use stdClass;

class renderer extends \plugin_renderer_base {

    /**
     * Render debate view.
     *
     * @param $moduleinstance
     * @return string Template
     */
    public function render_debate_view($moduleinstance) {
        return $this->render_from_template('mod_debate/view', $moduleinstance);
    }

    /**
     * Render debate page.
     *
     * @param $moduleinstance
     * @return string Template
     */
    public function render_debate_page($moduleinstance) {
        return $this->render_from_template('mod_debate/debate', $moduleinstance);
    }

    /**
     * Render rebate teams page.
     *
     * @param $moduleinstance
     * @return string Template
     */
    public function render_debate_teams($moduleinstance) {
        return $this->render_from_template('mod_debate/debate_teams', $moduleinstance);
    }

    /**
     * Render action buttons.
     *
     * @param $params
     * @return string Template
     */
    public function render_action_buttons($params) {
        $context = new stdClass();
        $context->id = $params['id'];
        $context->buttons = $params['buttons'];
        return $this->render_from_template('mod_debate/action_buttons', $context);
    }

    /**
     * Render form page.
     *
     * @param $params
     * @return string Template
     */
    public function render_form_page($params) {
        $context = new stdClass();
        $context->title = $params['title'];
        $context->formhtml = $params['formhtml'];
        return $this->render_from_template('mod_debate/debate_teams_form', $context);
    }

    /**
     * Render teams table.
     *
     * @param $params
     * @return string Template
     */
    public function render_table_page($params) {
        $context = new stdClass();
        $context->editurl = $params['editurl'];
        $context->tablehtml = $params['tablehtml'];
        return $this->render_from_template('mod_debate/debate_teams_table', $context);
    }

    public function render_cancel_button($cancelurl) {
        $data = new stdClass();
        $data->cancelurl = $cancelurl;
        return $this->render_from_template('mod_debate/cancel_button', $data);
    }

}

