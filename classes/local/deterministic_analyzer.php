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
 * Fast local checks that do not use AI.
 *
 * @package    qbank_questionaudit
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class deterministic_analyzer {
    /**
     * Audit normalized question data.
     *
     * @param array $question Normalized question.
     * @return array[] Findings.
     */
    public function analyze(array $question): array {
        $findings = [];
        $qtype = (string)($question['qtype'] ?? '');

        if (!question_normalizer::is_supported($qtype)) {
            $findings[] = $this->finding(
                'informational', 'unsupported_qtype', 'high',
                $qtype,
                get_string('local_unsupported', 'qbank_questionaudit'),
                get_string('unsupportedqtype', 'qbank_questionaudit', $qtype)
            );
            return $findings;
        }

        if (trim((string)($question['questiontext'] ?? '')) === '') {
            $findings[] = $this->finding(
                'error', 'empty_questiontext', 'high', '',
                get_string('local_empty_questiontext', 'qbank_questionaudit'),
                get_string('suggest_add_questiontext', 'qbank_questionaudit')
            );
        }

        if (!$this->meaningful_name((string)($question['name'] ?? ''), $qtype)) {
            $findings[] = $this->finding(
                'suggestion', 'question_name', 'high', (string)($question['name'] ?? ''),
                get_string('local_empty_name', 'qbank_questionaudit'),
                get_string('suggest_meaningful_name', 'qbank_questionaudit')
            );
        }

        $this->check_answers($question, $findings);
        $this->check_structure($question, $findings);
        $this->check_feedback($question, $findings);
        $this->check_html($question, $findings);

        return $findings;
    }

    /**
     * Check answer presence, duplicates and fractions.
     *
     * @param array $question Question.
     * @param array $findings Findings target.
     */
    private function check_answers(array $question, array &$findings): void {
        $qtype = (string)$question['qtype'];
        $answers = $question['answers'] ?? [];
        if (!in_array($qtype, ['multichoice', 'truefalse', 'shortanswer', 'numerical'], true)) {
            return;
        }

        $seen = [];
        $fractions = [];
        foreach ($answers as $answer) {
            $text = trim((string)($answer['text'] ?? ''));
            if ($text === '') {
                $findings[] = $this->finding(
                    'error', 'empty_answer', 'high', '',
                    get_string('local_empty_answer', 'qbank_questionaudit'),
                    get_string('suggest_empty_answer', 'qbank_questionaudit')
                );
            } else {
                $key = \core_text::strtolower($text);
                if (isset($seen[$key])) {
                    $findings[] = $this->finding(
                        'warning', 'duplicate_answer', 'high', $text,
                        get_string('local_duplicate_answer', 'qbank_questionaudit'),
                        get_string('suggest_duplicate_answer', 'qbank_questionaudit')
                    );
                }
                $seen[$key] = true;
            }
            if ($answer['fraction'] !== null) {
                $fraction = (float)$answer['fraction'];
                $fractions[] = $fraction;
                if ($fraction < -1.00001 || $fraction > 1.00001) {
                    $findings[] = $this->finding(
                        'error', 'fraction_range', 'high', $text,
                        get_string('local_fraction_inconsistent', 'qbank_questionaudit'),
                        get_string('suggest_fraction_range', 'qbank_questionaudit')
                    );
                }
            }
        }

        $maxfraction = $fractions ? max($fractions) : 0.0;
        if (!$answers || $maxfraction <= 0.0) {
            $findings[] = $this->finding(
                'error', 'missing_correct_answer', 'high', (string)($question['questiontext'] ?? ''),
                get_string('local_no_correct_answer', 'qbank_questionaudit'),
                get_string('suggest_correct_answer', 'qbank_questionaudit')
            );
            return;
        }

        if ($qtype === 'multichoice') {
            $single = !empty($question['settings']['single']);
            $positive = array_values(array_filter($fractions, static fn(float $fraction): bool => $fraction > 0));
            $positivesum = array_sum($positive);
            if ($single) {
                $fullcorrect = count(array_filter($fractions, static fn(float $fraction): bool => abs($fraction - 1.0) < 0.00001));
                if ($fullcorrect !== 1) {
                    $findings[] = $this->finding(
                        'error', 'fraction_sum', 'high', (string)($question['questiontext'] ?? ''),
                        get_string('local_fraction_inconsistent', 'qbank_questionaudit'),
                        get_string('suggest_single_fraction', 'qbank_questionaudit')
                    );
                }
            } else if (abs($positivesum - 1.0) > 0.01) {
                $findings[] = $this->finding(
                    'warning', 'fraction_sum', 'high', (string)($question['questiontext'] ?? ''),
                    get_string('local_fraction_inconsistent', 'qbank_questionaudit'),
                    get_string('suggest_multi_fraction', 'qbank_questionaudit')
                );
            }
        } else if ($maxfraction < 0.99999) {
            $findings[] = $this->finding(
                'warning', 'fraction_sum', 'high', (string)($question['questiontext'] ?? ''),
                get_string('local_fraction_inconsistent', 'qbank_questionaudit'),
                get_string('suggest_full_credit', 'qbank_questionaudit')
            );
        }
    }

    /**
     * Check known structural requirements for supported qtypes.
     *
     * @param array $question Question.
     * @param array $findings Findings target.
     */
    private function check_structure(array $question, array &$findings): void {
        $qtype = (string)$question['qtype'];
        $answers = $question['answers'] ?? [];
        $pairs = $question['matchingpairs'] ?? [];
        $problem = false;
        $evidence = (string)($question['questiontext'] ?? '');
        $suggestion = '';

        if ($qtype === 'multichoice' && count($answers) < 2) {
            $problem = true;
            $suggestion = get_string('suggest_multichoice_structure', 'qbank_questionaudit');
        } else if ($qtype === 'truefalse' && count($answers) !== 2) {
            $problem = true;
            $suggestion = get_string('suggest_truefalse_structure', 'qbank_questionaudit');
        } else if (in_array($qtype, ['shortanswer', 'numerical'], true) && count($answers) < 1) {
            $problem = true;
            $suggestion = get_string('suggest_accepted_answer', 'qbank_questionaudit');
        } else if ($qtype === 'match') {
            $validpairs = 0;
            foreach ($pairs as $pair) {
                $questionpart = trim((string)($pair['question'] ?? ''));
                $answerpart = trim((string)($pair['answer'] ?? ''));
                if ($questionpart === '' || $answerpart === '') {
                    $findings[] = $this->finding(
                        'error', 'empty_answer', 'high', $questionpart !== '' ? $questionpart : $answerpart,
                        get_string('local_empty_answer', 'qbank_questionaudit'),
                        get_string('suggest_matching_pair', 'qbank_questionaudit')
                    );
                } else {
                    $validpairs++;
                }
            }
            if ($validpairs < 2) {
                $problem = true;
                $suggestion = get_string('suggest_matching_structure', 'qbank_questionaudit');
            }
        }

        if ($problem) {
            $findings[] = $this->finding(
                'error', 'qtype_structure', 'high', $evidence,
                get_string('local_structure_problem', 'qbank_questionaudit'),
                $suggestion
            );
        }
    }

    /**
     * Detect lack of feedback.
     *
     * @param array $question Question.
     * @param array $findings Findings target.
     */
    private function check_feedback(array $question, array &$findings): void {
        if (trim((string)($question['generalfeedback'] ?? '')) !== '') {
            return;
        }
        foreach ($question['answers'] ?? [] as $answer) {
            if (trim((string)($answer['feedback'] ?? '')) !== '') {
                return;
            }
        }
        $findings[] = $this->finding(
            'suggestion', 'feedback_missing', 'high', (string)($question['questiontext'] ?? ''),
            get_string('local_feedback_missing', 'qbank_questionaudit'),
            get_string('suggest_feedback', 'qbank_questionaudit')
        );
    }

    /**
     * Run a deliberately conservative simple HTML balance check.
     *
     * @param array $question Question.
     * @param array $findings Findings target.
     */
    private function check_html(array $question, array &$findings): void {
        $htmlvalues = [
            (string)($question['questiontext_html'] ?? ''),
            (string)($question['generalfeedback_html'] ?? ''),
        ];
        foreach ($question['answers'] ?? [] as $answer) {
            $htmlvalues[] = (string)($answer['html'] ?? '');
            $htmlvalues[] = (string)($answer['feedback_html'] ?? '');
        }
        foreach ($question['matchingpairs'] ?? [] as $pair) {
            $htmlvalues[] = (string)($pair['question_html'] ?? '');
            $htmlvalues[] = (string)($pair['answer_html'] ?? '');
        }

        foreach ($htmlvalues as $html) {
            if ($html !== '' && !$this->simple_html_is_balanced($html)) {
                $findings[] = $this->finding(
                    'warning', 'html_structure', 'high', question_normalizer::text($html),
                    get_string('local_html_invalid', 'qbank_questionaudit'),
                    get_string('suggest_html', 'qbank_questionaudit')
                );
                return;
            }
        }
    }

    /**
     * Simple tag-balance check. This is not an HTML validator by design.
     *
     * @param string $html HTML.
     * @return bool
     */
    private function simple_html_is_balanced(string $html): bool {
        if (!str_contains($html, '<')) {
            return true;
        }
        preg_match_all('/<\/?([a-zA-Z][a-zA-Z0-9]*)\b[^>]*>/', $html, $matches, PREG_SET_ORDER);
        $void = ['area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr'];
        $stack = [];
        foreach ($matches as $match) {
            $tag = \core_text::strtolower($match[1]);
            $token = $match[0];
            if (in_array($tag, $void, true) || str_ends_with(trim($token), '/>')) {
                continue;
            }
            if (str_starts_with($token, '</')) {
                if (!$stack || array_pop($stack) !== $tag) {
                    return false;
                }
            } else {
                $stack[] = $tag;
            }
        }
        return $stack === [];
    }

    /**
     * Determine whether the bank name is useful to a teacher.
     *
     * @param string $name Question name.
     * @param string $qtype Question type.
     * @return bool
     */
    private function meaningful_name(string $name, string $qtype): bool {
        $name = trim($name);
        if (\core_text::strlen($name) < 4) {
            return false;
        }
        $lower = \core_text::strtolower($name);
        $generic = ['question', 'new question', 'questão', 'nova questão', $qtype];
        return !in_array($lower, $generic, true);
    }

    /**
     * Create a normalized finding.
     *
     * @param string $severity Severity.
     * @param string $category Category.
     * @param string $confidence Confidence.
     * @param string $evidence Evidence.
     * @param string $explanation Explanation.
     * @param string $suggestion Suggestion.
     * @return array
     */
    private function finding(
        string $severity,
        string $category,
        string $confidence,
        string $evidence,
        string $explanation,
        string $suggestion
    ): array {
        return [
            'severity' => $severity,
            'category' => $category,
            'confidence' => $confidence,
            'evidence' => $evidence,
            'explanation' => $explanation,
            'suggestion' => $suggestion,
            'source' => 'local',
        ];
    }
}
