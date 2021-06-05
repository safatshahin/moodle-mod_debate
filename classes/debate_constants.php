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
 * Constants of mod_debate.
 *
 * @package     mod_debate
 * @copyright   2021 Safat Shahin <safatshahin@yahoo.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_debate;

defined('MOODLE_INTERNAL') || die();

/**
 * Class debate_constants.
 *
 * A class to help with debate teams constants.
 */
class debate_constants {

    /**
     * Positive response value.
     */
    const MOD_DEBATE_POSITIVE = 1;

    /**
     * Negative response value.
     */
    const MOD_DEBATE_NEGATIVE = 0;

    /**
     * Unlimited response allowed from the debate.
     */
    const MOD_DEBATE_RESPONSE_UNLIMITED = 0;

    /**
     * Only one response allowed in any one side.
     */
    const MOD_DEBATE_RESPONSE_ONLY_ONE = 1;

    /**
     * Only one response allowed each side.
     */
    const MOD_DEBATE_RENPONSE_ONE_PER_SECTIOM = 2;

    /**
     * Use debate teams to manage responses.
     */
    const MOD_DEBATE_RESPONSE_USE_TEAMS = 3;

}

