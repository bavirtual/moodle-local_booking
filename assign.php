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
 * Assigment feedback redirect
 * Clears preset filters and redirects to correct exercise
 * for intructor provided feedback submission
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use \local_booking\local\participant\entities\student;

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->dirroot . '/mod/assign/locallib.php');

// Get URL parameters.
$courseid     = optional_param('courseid', 0, PARAM_INT);
$exerciseid   = optional_param('exeid', 0, PARAM_INT);
$studentid    = optional_param('userid', 0, PARAM_INT);
$sessionpassed= optional_param('passed', 1, PARAM_INT);
$addattmept   = !$sessionpassed;

list ($course, $cm) = get_course_and_cm_from_cmid($exerciseid, 'assign');

// set context for the module and other requirements by the assignment
$context = context_module::instance($cm->id);

require_login($course, true, $cm);
require_capability('local/booking:view', $context);

$urlparams = array('id' => $exerciseid,
                  'action' => 'grading',
                  'tifirst' => '',
                  'tilast' => '');

$url = new moodle_url('/mod/assign/view.php', $urlparams);
$PAGE->set_url($url);

// create an assignment object to reset filters
$assign = new assign($context, $cm, $course);
$uniqueid = 'mod_assign_grading-' . $assign->get_context()->id;
$prefs = array(
    'collapse' => array(),
    'sortby' => array(),
    'i_first' => '',
    'i_last' => '',
    'textsort' => array());

// clear any set filters
set_user_preference('flextable_' . $uniqueid, json_encode($prefs));

if ($addattmept) {

    $params = [
        'id'=>$exerciseid,
        'userid'=>$studentid,
        'action'=>'addattempt',
        'sesskey'=>sesskey()
    ];
    $url = new moodle_url('/mod/assign/view.php',$params);

    $curl = new \curl();
    $curl->get($url->out_omit_querystring(), $url->params());
    $info = $curl->get_info();

    // The base cURL seems fine, let's press on.
    if ($curl->get_errno() || $curl->error) {
        throw new exception(get_string('errornewattemptunable', 'local_booking'));
    }
}


// redirect to the assignment feedback page, check for progressing/objective not met feedback
$redirecturl = new moodle_url('/mod/assign/view.php', [
    'id' => $exerciseid,
    'rownum' => 0,
    'userid' => $studentid,
    'sesskey'=> sesskey(),
    'action' => 'grader',
]);
redirect($redirecturl);
