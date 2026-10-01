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

/**
 * Run question audits without modifying question data.
 *
 * @package    qbank_questionaudit
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/questionlib.php');

use core_question\local\bank\helper;
use qbank_questionaudit\local\access_manager;
use qbank_questionaudit\local\audit_service;
use qbank_questionaudit\local\question_repository;
use qbank_questionaudit\output\report;

require_login();
helper::require_plugin_enabled('qbank_questionaudit');
require_sesskey();

$questionid = optional_param('id', 0, PARAM_INT);
$categoryid = optional_param('categoryid', 0, PARAM_INT);
$returnurlparam = optional_param('returnurl', '', PARAM_LOCALURL);
$returnurl = $returnurlparam !== '' ? new moodle_url($returnurlparam) : new moodle_url('/');

$repository = new question_repository();
$questionids = [];
$pagecontext = context_system::instance();

if ($questionid > 0) {
    access_manager::require_view_question($questionid);
    $questionids = [$questionid];
    $category = $repository->category_for_question($questionid);
    $pagecontext = context::instance_by_id((int)$category->contextid);
} else if ($categoryid > 0) {
    $category = $DB->get_record('question_categories', ['id' => $categoryid], '*', MUST_EXIST);
    $pagecontext = context::instance_by_id((int)$category->contextid);
    require_any_capability(['moodle/question:viewmine', 'moodle/question:viewall'], $pagecontext);
    $questionids = $repository->accessible_latest_ids_in_category($categoryid);
} else {
    foreach ($_REQUEST as $key => $value) {
        if (preg_match('/^q([0-9]+)$/', (string)$key, $matches)) {
            $selectedid = clean_param($matches[1], PARAM_INT);
            if ($selectedid > 0) {
                access_manager::require_view_question($selectedid);
                $questionids[] = $selectedid;
            }
        }
    }
    $questionids = array_values(array_unique($questionids));
    sort($questionids, SORT_NUMERIC);
    if ($questionids) {
        $category = $repository->category_for_question($questionids[0]);
        $pagecontext = context::instance_by_id((int)$category->contextid);
    }
}

$PAGE->set_context($pagecontext);
$PAGE->set_url(new moodle_url('/question/bank/questionaudit/audit.php'));
$PAGE->set_title(get_string('auditresults', 'qbank_questionaudit'));
$PAGE->set_heading(get_string('auditresults', 'qbank_questionaudit'));
$PAGE->activityheader->disable();

if (!$questionids) {
    echo $OUTPUT->header();
    $message = $categoryid > 0
        ? get_string('emptycategory', 'qbank_questionaudit')
        : get_string('emptyselection', 'qbank_questionaudit');
    echo $OUTPUT->notification($message, 'info');
    echo html_writer::link($returnurl, get_string('backtoquestionbank', 'qbank_questionaudit'), ['class' => 'btn btn-primary']);
    echo $OUTPUT->footer();
    exit;
}

$service = new audit_service();
$results = [];
foreach ($questionids as $id) {
    $results[] = $service->audit_question($id);
}

$report = new report($results, $returnurl);

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('qbank_questionaudit/report', $report->export_for_template($OUTPUT));
echo $OUTPUT->footer();
