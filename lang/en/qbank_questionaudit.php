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

/**
 * English strings for qbank_questionaudit.
 *
 * @package    qbank_questionaudit
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Question audit';
$string['auditquestion'] = 'Audit question';
$string['auditselected'] = 'Audit selected questions';
$string['auditcategory'] = 'Audit this category';
$string['auditresults'] = 'Question audit results';
$string['auditresultsfor'] = 'Audit: {$a}';
$string['backtoquestionbank'] = 'Back to question bank';
$string['openquestion'] = 'Open question editor';
$string['summary'] = 'Summary';
$string['findings'] = 'Findings';
$string['nofindings'] = 'No issue was detected by the available checks.';
$string['severity'] = 'Severity';
$string['category'] = 'Category';
$string['confidence'] = 'Confidence';
$string['evidence'] = 'Affected text';
$string['explanation'] = 'Why it matters';
$string['suggestion'] = 'Suggestion';
$string['source'] = 'Source';
$string['source_local'] = 'Deterministic check';
$string['source_ai'] = 'AI review';
$string['severity_error'] = 'Error';
$string['severity_warning'] = 'Warning';
$string['severity_suggestion'] = 'Suggestion';
$string['severity_informational'] = 'Informational';
$string['confidence_low'] = 'Low';
$string['confidence_medium'] = 'Medium';
$string['confidence_high'] = 'High';
$string['ailimitation'] = 'AI findings are hypotheses based on the question text. They must be reviewed by a ' .
    'person and are never applied automatically.';
$string['bridgeerror'] = 'The deterministic audit was completed, but the AI review could not be completed: {$a}';
$string['bridgeunavailable'] = 'The required local_ai_bridge API is not available.';
$string['unsupportedqtype'] = 'Question type {$a} is not supported by the semantic audit yet.';
$string['emptyselection'] = 'No questions were selected.';
$string['emptycategory'] = 'There are no accessible questions in this category.';
$string['invalidairesponse'] = 'The AI returned an invalid response and its findings were ignored.';
$string['privacy:metadata'] = 'The Question audit plugin does not store personal data. Question content is sent to ' .
    'local_ai_bridge only while an authorized user runs an audit.';
$string['local_empty_questiontext'] = 'The question statement is empty.';
$string['local_empty_name'] = 'The question name is empty or not meaningful.';
$string['local_no_correct_answer'] = 'No correct answer was found for a question type that requires one.';
$string['local_fraction_inconsistent'] = 'The answer fractions appear to be inconsistent.';
$string['local_duplicate_answer'] = 'Two or more answers are literally duplicated.';
$string['local_empty_answer'] = 'At least one answer or matching pair is empty.';
$string['local_feedback_missing'] = 'No feedback was found in the question or its answers.';
$string['local_html_invalid'] = 'The question contains simple HTML structure problems.';
$string['local_structure_problem'] = 'The question has a structural problem for its question type.';
$string['local_unsupported'] = 'This question type is not supported by the first version of Question audit.';
$string['suggest_add_questiontext'] = 'Add a complete question statement before using the question.';
$string['suggest_meaningful_name'] = 'Use a short descriptive name that helps teachers identify the question in the bank.';
$string['suggest_empty_answer'] = 'Remove the empty alternative or complete its content.';
$string['suggest_duplicate_answer'] = 'Remove the duplicate or rewrite it so that each answer represents a distinct option.';
$string['suggest_fraction_range'] = 'Review the answer fraction; Moodle question fractions should normally stay between -1 and 1.';
$string['suggest_correct_answer'] = 'Define at least one answer with a positive correct fraction.';
$string['suggest_single_fraction'] = 'A single-answer multiple-choice question should normally have exactly one 100% ' .
    'correct answer.';
$string['suggest_multi_fraction'] = 'Review positive fractions; for a multiple-answer question they normally add up to 100%.';
$string['suggest_full_credit'] = 'No answer awards full credit. Confirm that partial-credit-only grading is intentional.';
$string['suggest_multichoice_structure'] = 'Multiple-choice questions should contain at least two alternatives.';
$string['suggest_truefalse_structure'] = 'True/false questions should contain exactly the two expected answer records.';
$string['suggest_accepted_answer'] = 'Add at least one accepted answer.';
$string['suggest_matching_pair'] = 'Complete both sides of every matching pair or remove the incomplete pair.';
$string['suggest_matching_structure'] = 'Matching questions should contain at least two complete pairs.';
$string['suggest_feedback'] = 'Consider adding feedback when it can help the learner understand the result.';
$string['suggest_html'] = 'Review opening and closing HTML tags in the affected content.';
$string['bridgepermission'] = 'You do not have permission to use local_ai_bridge.';
$string['bridgetenant'] = 'No enabled AI tenant is configured for your user.';
$string['bridgeuserdisabled'] = 'Your AI bridge access is disabled.';
$string['bridgepurpose'] = 'The questionaudit-review purpose is not enabled for your tenant.';
$string['bridgeroute'] = 'No AI route is available for the questionaudit-review purpose.';
$string['bridgefailed'] = 'The AI bridge could not complete the review.';
$string['deterministicsummary'] = 'Deterministic checks produced {$a} finding(s).';
$string['emptyevidence'] = 'No text is available because the affected field is empty.';
