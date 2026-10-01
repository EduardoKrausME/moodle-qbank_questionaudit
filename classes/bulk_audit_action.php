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

namespace qbank_questionaudit;

use core_question\local\bank\bulk_action_base;
use moodle_url;

/**
 * Bulk audit action.
 *
 * @package    qbank_questionaudit
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class bulk_audit_action extends bulk_action_base {
    /**
     * Bulk action title.
     *
     * @return string
     */
    public function get_bulk_action_title(): string {
        return get_string('auditselected', 'qbank_questionaudit');
    }

    /**
     * Bulk action key.
     *
     * @return string
     */
    public function get_key(): string {
        return 'questionaudit';
    }

    /**
     * Target URL.
     *
     * @return moodle_url
     */
    public function get_bulk_action_url(): moodle_url {
        return new moodle_url('/question/bank/questionaudit/audit.php');
    }

    /**
     * Capabilities used by the question bank to expose the action.
     * Per-question access is checked again on the target page.
     *
     * @return array
     */
    public function get_bulk_action_capabilities(): ?array {
        return ['moodle/question:viewmine', 'moodle/question:viewall'];
    }
}
