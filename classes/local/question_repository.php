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

namespace qbank_questionaudit\local;

/**
 * Read-only question bank queries used by category audits.
 *
 * @package    qbank_questionaudit
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question_repository {
    /**
     * Resolve the category owning a specific question version.
     *
     * @param int $questionid Question id.
     * @return \stdClass
     */
    public function category_for_question(int $questionid): \stdClass {
        global $DB;

        $sql = "SELECT qc.*
                  FROM {question_versions} qv
                  JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid
                  JOIN {question_categories} qc ON qc.id = qbe.questioncategoryid
                 WHERE qv.questionid = :questionid";
        return $DB->get_record_sql($sql, ['questionid' => $questionid], MUST_EXIST);
    }

    /**
     * Return latest question version ids in one category that the current user may view.
     *
     * @param int $categoryid Question category id.
     * @return int[]
     */
    public function accessible_latest_ids_in_category(int $categoryid): array {
        global $DB;

        $sql = "SELECT qv.questionid
                  FROM {question_bank_entries} qbe
                  JOIN {question_versions} qv ON qv.questionbankentryid = qbe.id
                 WHERE qbe.questioncategoryid = :categoryid
                   AND qv.version = (
                       SELECT MAX(qv2.version)
                         FROM {question_versions} qv2
                        WHERE qv2.questionbankentryid = qbe.id
                   )
              ORDER BY qbe.id";
        $records = $DB->get_records_sql($sql, ['categoryid' => $categoryid]);
        $ids = [];
        foreach ($records as $record) {
            $questionid = (int)$record->questionid;
            if (access_manager::can_view_question($questionid)) {
                $ids[] = $questionid;
            }
        }
        return $ids;
    }
}
