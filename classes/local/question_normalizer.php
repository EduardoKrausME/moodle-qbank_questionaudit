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
 * Convert Moodle question data into the small, provider-neutral structure used by the auditor.
 *
 * @package    qbank_questionaudit
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question_normalizer {
    /** @var string[] Initially supported question types. */
    public const SUPPORTED_TYPES = [
        'multichoice',
        'truefalse',
        'shortanswer',
        'numerical',
        'essay',
        'match',
    ];

    /**
     * Whether a question type has semantic audit support.
     *
     * @param string $qtype Question type.
     * @return bool
     */
    public static function is_supported(string $qtype): bool {
        return in_array($qtype, self::SUPPORTED_TYPES, true);
    }

    /**
     * Normalize a question loaded using question_bank::load_question_data().
     *
     * @param \stdClass $question Moodle question data.
     * @return array
     */
    public static function normalize(\stdClass $question): array {
        $normalized = [
            'id' => (int)($question->id ?? 0),
            'qtype' => (string)($question->qtype ?? ''),
            'name' => self::text((string)($question->name ?? '')),
            'questiontext' => self::text((string)($question->questiontext ?? '')),
            'questiontext_html' => (string)($question->questiontext ?? ''),
            'generalfeedback' => self::text((string)($question->generalfeedback ?? '')),
            'generalfeedback_html' => (string)($question->generalfeedback ?? ''),
            'defaultmark' => isset($question->defaultmark) ? (float)$question->defaultmark : null,
            'penalty' => isset($question->penalty) ? (float)$question->penalty : null,
            'answers' => [],
            'matchingpairs' => [],
            'settings' => [],
        ];

        $options = $question->options ?? null;
        if (is_object($options)) {
            self::extract_answers($options, $normalized);
            self::extract_matching_pairs($options, $normalized);
            self::extract_settings($options, $normalized);
        }

        return $normalized;
    }

    /**
     * Convert rich text to stable plain text for comparison and AI input.
     *
     * @param string $value Value.
     * @return string
     */
    public static function text(string $value): string {
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = strip_tags($value);
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
        return trim($value);
    }

    /**
     * Build a single searchable evidence corpus from normalized question content.
     *
     * @param array $question Normalized question.
     * @return string
     */
    public static function evidence_corpus(array $question): string {
        $parts = [
            (string)($question['name'] ?? ''),
            (string)($question['questiontext'] ?? ''),
            (string)($question['generalfeedback'] ?? ''),
        ];
        foreach ($question['answers'] ?? [] as $answer) {
            $parts[] = (string)($answer['text'] ?? '');
            $parts[] = (string)($answer['feedback'] ?? '');
        }
        foreach ($question['matchingpairs'] ?? [] as $pair) {
            $parts[] = (string)($pair['question'] ?? '');
            $parts[] = (string)($pair['answer'] ?? '');
        }
        return self::text(implode("\n", $parts));
    }

    /**
     * Extract answer records shared by several qtypes.
     *
     * @param object $options Question options.
     * @param array $normalized Target structure.
     */
    private static function extract_answers(object $options, array &$normalized): void {
        if (!isset($options->answers) || !is_iterable($options->answers)) {
            return;
        }
        foreach ($options->answers as $answer) {
            if (!is_object($answer)) {
                continue;
            }
            $normalized['answers'][] = [
                'id' => (int)($answer->id ?? 0),
                'text' => self::text((string)($answer->answer ?? '')),
                'html' => (string)($answer->answer ?? ''),
                'fraction' => isset($answer->fraction) ? (float)$answer->fraction : null,
                'feedback' => self::text((string)($answer->feedback ?? '')),
                'feedback_html' => (string)($answer->feedback ?? ''),
            ];
        }
    }

    /**
     * Extract matching pairs.
     *
     * @param object $options Question options.
     * @param array $normalized Target structure.
     */
    private static function extract_matching_pairs(object $options, array &$normalized): void {
        if (!isset($options->subquestions) || !is_iterable($options->subquestions)) {
            return;
        }
        foreach ($options->subquestions as $subquestion) {
            if (!is_object($subquestion)) {
                continue;
            }
            $normalized['matchingpairs'][] = [
                'id' => (int)($subquestion->id ?? 0),
                'question' => self::text((string)($subquestion->questiontext ?? '')),
                'question_html' => (string)($subquestion->questiontext ?? ''),
                'answer' => self::text((string)($subquestion->answertext ?? '')),
                'answer_html' => (string)($subquestion->answertext ?? ''),
            ];
        }
    }

    /**
     * Extract qtype settings that are useful to deterministic or semantic checks.
     *
     * @param object $options Question options.
     * @param array $normalized Target structure.
     */
    private static function extract_settings(object $options, array &$normalized): void {
        $fields = [
            'single', 'shuffleanswers', 'answernumbering', 'showstandardinstruction',
            'usecase', 'responseformat', 'responserequired', 'responsefieldlines',
            'attachments', 'attachmentsrequired', 'unitgradingtype', 'unitpenalty',
        ];
        foreach ($fields as $field) {
            if (property_exists($options, $field)) {
                $normalized['settings'][$field] = $options->{$field};
            }
        }
    }
}
