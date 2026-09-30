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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

namespace qbank_questionaudit;

use core_question\local\bank\question_action_base;

/**
 * Per-question audit action.
 *
 * @package    qbank_questionaudit
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class audit_action extends question_action_base {
    /** @var string Action label. */
    protected string $label;

    /**
     * Initialise cached strings.
     */
    public function init(): void {
        parent::init();
        $this->label = get_string('auditquestion', 'qbank_questionaudit');
    }

    /**
     * Position after standard edit/preview actions.
     *
     * @return int
     */
    public function get_menu_position(): int {
        return 350;
    }

    /**
     * Build action URL, icon and label.
     *
     * @param \stdClass $question Question bank row.
     * @return array
     */
    protected function get_url_icon_and_label(\stdClass $question): array {
        if (!\question_bank::is_qtype_installed($question->qtype)) {
            return [null, null, null];
        }
        if (!question_has_capability_on($question, 'view')) {
            return [null, null, null];
        }

        $url = new \moodle_url('/question/bank/questionaudit/audit.php', [
            'id' => $question->id,
            'returnurl' => $this->qbank->returnurl,
            'sesskey' => sesskey(),
        ]);
        return [$url, 'i/report', $this->label];
    }
}
