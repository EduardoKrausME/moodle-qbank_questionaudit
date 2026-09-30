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

use invalid_parameter_exception;

/**
 * Strict validator for the JSON returned by the AI bridge.
 *
 * @package    qbank_questionaudit
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ai_response_parser {
    /** @var string[] Allowed finding severities. */
    private const SEVERITIES = ['error', 'warning', 'suggestion', 'informational'];

    /** @var string[] Allowed confidence levels. */
    private const CONFIDENCES = ['low', 'medium', 'high'];

    /**
     * Parse and validate an AI response.
     *
     * Invalid findings are rejected with the entire response rather than silently trusted.
     *
     * @param string $json Raw response text.
     * @param array $question Normalized question used to validate textual evidence.
     * @return array
     */
    public function parse(string $json, array $question): array {
        $decoded = json_decode(trim($json), true);
        if (!is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
            throw new invalid_parameter_exception('AI response is not strict JSON.');
        }

        $topkeys = ['findings', 'summary'];
        $actualtopkeys = array_keys($decoded);
        sort($actualtopkeys);
        if ($actualtopkeys !== $topkeys || !is_string($decoded['summary']) || !is_array($decoded['findings'])) {
            throw new invalid_parameter_exception('AI response does not match the required schema.');
        }

        $summary = trim($decoded['summary']);
        if ($summary === '' || \core_text::strlen($summary) > 4000) {
            throw new invalid_parameter_exception('AI response summary is empty or too long.');
        }

        $corpus = question_normalizer::text(question_normalizer::evidence_corpus($question));
        $normalizedcorpus = \core_text::strtolower($corpus);
        $findings = [];
        if (count($decoded['findings']) > 50) {
            throw new invalid_parameter_exception('AI response contains too many findings.');
        }

        $required = ['category', 'confidence', 'evidence', 'explanation', 'severity', 'suggestion'];
        foreach ($decoded['findings'] as $finding) {
            if (!is_array($finding)) {
                throw new invalid_parameter_exception('AI finding must be an object.');
            }
            $actualkeys = array_keys($finding);
            sort($actualkeys);
            if ($actualkeys !== $required) {
                throw new invalid_parameter_exception('AI finding does not match the required schema.');
            }
            foreach ($required as $key) {
                if (!is_string($finding[$key])) {
                    throw new invalid_parameter_exception('AI finding fields must be strings.');
                }
            }
            if (!in_array($finding['severity'], self::SEVERITIES, true)) {
                throw new invalid_parameter_exception('Invalid AI finding severity.');
            }
            if (!in_array($finding['confidence'], self::CONFIDENCES, true)) {
                throw new invalid_parameter_exception('Invalid AI finding confidence.');
            }
            if (!preg_match('/^[a-z0-9_-]{1,64}$/', $finding['category'])) {
                throw new invalid_parameter_exception('Invalid AI finding category.');
            }

            $evidence = question_normalizer::text($finding['evidence']);
            $explanation = trim($finding['explanation']);
            $suggestion = trim($finding['suggestion']);
            if ($evidence === '' || $explanation === '' || $suggestion === '') {
                throw new invalid_parameter_exception('AI finding contains empty required text.');
            }
            if (\core_text::strlen($evidence) > 4000 || \core_text::strlen($explanation) > 8000 ||
                    \core_text::strlen($suggestion) > 8000) {
                throw new invalid_parameter_exception('AI finding contains oversized text.');
            }

            $normalizedevidence = \core_text::strtolower($evidence);
            if ($normalizedcorpus === '' || !str_contains($normalizedcorpus, $normalizedevidence)) {
                throw new invalid_parameter_exception('AI finding evidence is not present in the question.');
            }

            $findings[] = [
                'severity' => $finding['severity'],
                'category' => $finding['category'],
                'confidence' => $finding['confidence'],
                'evidence' => $evidence,
                'explanation' => $explanation,
                'suggestion' => $suggestion,
                'source' => 'ai',
            ];
        }

        return [
            'summary' => $summary,
            'findings' => $findings,
        ];
    }
}

