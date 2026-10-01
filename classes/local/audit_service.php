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

use invalid_parameter_exception;
use moodle_exception;
use question_bank;
use required_capability_exception;
use Throwable;

/**
 * Orchestrates deterministic and AI checks without persisting audit content.
 *
 * @package    qbank_questionaudit
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class audit_service {
    /**
     * Audit one question.
     *
     * @param int $questionid Question id.
     * @return array
     */
    public function audit_question(int $questionid): array {
        access_manager::require_view_question($questionid);
        $questiondata = question_bank::load_question_data($questionid);
        $question = question_normalizer::normalize($questiondata);

        $analyzer = new deterministic_analyzer();
        $localfindings = $analyzer->analyze($question);
        $aifindings = [];
        $aisummary = '';
        $aierror = '';

        if (question_normalizer::is_supported((string)$question['qtype']) &&
            trim((string)$question['questiontext']) !== '' &&
            question_normalizer::evidence_corpus($question) !== '') {
            try {
                $review = (new ai_reviewer())->review($question);
                $aisummary = (string)$review['summary'];
                $aifindings = $review['findings'];
            } catch (Throwable $exception) {
                $aierror = $this->safe_ai_error($exception);
            }
        }

        return [
            'question' => $question,
            'summary' => $aisummary,
            'findings' => array_merge($localfindings, $aifindings),
            'aierror' => $aierror,
        ];
    }

    /**
     * Convert bridge/parser failures into safe user-facing messages without exposing provider details.
     *
     * @param Throwable $exception Failure.
     * @return string
     */
    private function safe_ai_error(Throwable $exception): string {
        if ($exception instanceof invalid_parameter_exception) {
            return get_string('invalidairesponse', 'qbank_questionaudit');
        }
        if ($exception instanceof required_capability_exception) {
            return get_string('bridgepermission', 'qbank_questionaudit');
        }
        if ($exception instanceof moodle_exception) {
            return match ($exception->errorcode) {
                'bridgeunavailable' => get_string('bridgeunavailable', 'qbank_questionaudit'),
                'error:notenant' => get_string('bridgetenant', 'qbank_questionaudit'),
                'error:userdisabled' => get_string('bridgeuserdisabled', 'qbank_questionaudit'),
                'error:purposeunavailable' => get_string('bridgepurpose', 'qbank_questionaudit'),
                'error:noroute' => get_string('bridgeroute', 'qbank_questionaudit'),
                default => get_string('bridgefailed', 'qbank_questionaudit'),
            };
        }
        return get_string('bridgefailed', 'qbank_questionaudit');
    }

}
