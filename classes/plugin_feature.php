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

use action_link;
use core\context;
use core_question\local\bank\plugin_features_base;
use core_question\local\bank\view;
use moodle_url;
use renderable;

/**
 * Question bank integration points for the audit plugin.
 *
 * @package    qbank_questionaudit
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class plugin_feature extends plugin_features_base {
    /**
     * Add an audit action to each accessible question.
     *
     * @param view $qbank Question bank view.
     * @return array
     */
    public function get_question_actions(view $qbank): array {
        return [new audit_action($qbank)];
    }

    /**
     * Add the bulk audit action.
     *
     * @param view|null $qbank Question bank view.
     * @return array
     */
    public function get_bulk_actions(?view $qbank = null): array {
        if ($qbank === null || $qbank->is_listing_specific_versions()) {
            return [];
        }
        return [new bulk_audit_action($qbank)];
    }

    /**
     * Add a category audit control above the question bank.
     *
     * @param view $qbank Question bank view.
     * @param context $context Current question category context.
     * @param int $categoryid Current category id.
     * @return renderable[]
     */
    public function get_question_bank_controls(view $qbank, context $context, int $categoryid): array {
        if (!has_any_capability(['moodle/question:viewmine', 'moodle/question:viewall'], $context)) {
            return [];
        }

        $url = new moodle_url('/question/bank/questionaudit/audit.php', [
            'categoryid' => $categoryid,
            'returnurl' => $qbank->returnurl,
            'sesskey' => sesskey(),
        ]);

        return [
            450 => new action_link(
                $url,
                get_string('auditcategory', 'qbank_questionaudit'),
                null,
                ['class' => 'btn btn-secondary']
            ),
        ];
    }
}
