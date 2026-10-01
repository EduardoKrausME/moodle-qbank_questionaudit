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

namespace qbank_questionaudit\local;

/**
 * Central capability checks used by pages and tests.
 *
 * @package    qbank_questionaudit
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class access_manager {
    /**
     * Whether the current user may view a question.
     *
     * @param int $questionid Question id.
     * @return bool
     */
    public static function can_view_question(int $questionid): bool {
        return question_has_capability_on($questionid, 'view');
    }

    /**
     * Require view access to one question.
     *
     * @param int $questionid Question id.
     */
    public static function require_view_question(int $questionid): void {
        question_require_capability_on($questionid, 'view');
    }

    /**
     * Whether the current user may edit a question.
     *
     * @param int $questionid Question id.
     * @return bool
     */
    public static function can_edit_question(int $questionid): bool {
        return question_has_capability_on($questionid, 'edit');
    }
}
