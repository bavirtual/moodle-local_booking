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
 * Add ATO specific fields and course groups
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafahajjar@gmail.com)
 * @category   Install
 * @copyright  BAVirtual.co.uk © 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 defined('MOODLE_INTERNAL') || die();

 define('LOCAL_BOOKING_VATSIMCID', 'vatsimcid');
 define('LOCAL_BOOKING_VATSIMCIDLABEL', 'VATSIM CID');
 define('LOCAL_BOOKING_PRIMARYSIMULATOR', 'simulator');
 define('LOCAL_BOOKING_PRIMARYSIMULATORLABEL', 'Primary Flight Simulator');
 define('LOCAL_BOOKING_SECONDARYSIMULATOR', 'simulator2');
 define('LOCAL_BOOKING_SECONDARYSIMULATORLABEL', 'Secondary Flight Simulator');
 define('LOCAL_BOOKING_SUPPORTEDSIMULATORS', 'MSFS'. PHP_EOL . 'XP11'. PHP_EOL . 'P3D5'. PHP_EOL . 'P3D4'. PHP_EOL . 'FSX');
 define('LOCAL_BOOKING_DEFAULTSIMULATOR', 'MSFS');
 define('LOCAL_BOOKING_CALLSIGN', 'callsign');
 define('LOCAL_BOOKING_CALLSIGNLABEL', 'Callsign');

require_once($CFG->dirroot . '/local/booking/lib.php');

use \core_customfield\api;
use \core_customfield\category_controller;
use \core_customfield\field_controller;
use \core_customfield\event\field_created;

/**
 * Add to user profile and courses ATO custom categories and custom fields.
 *
 * @return bool
 */
function xmldb_local_booking_install() {

    // Add ATO custom category and fields in user profile
    create_user_profile_customfields();

    // Add ATO custom category and fields in courses
    create_course_customfields();

    return true;
}

/**
 * Add user profile ATO category and custom fields.
 *
 * @return bool
 */
function create_user_profile_customfields() {
    global $DB;

    // Look for ATO category and add to the end if it doesn't exist
    $category = $DB->get_record('user_info_category', array('name'=>ucfirst(get_string('pluginname', 'local_booking'))));
    $categoryid = 0;
    $sortorder = 0;
    if (empty($category)) {
        // get next sort order
        $categories = $DB->get_records('user_info_category', null, 'sortorder DESC', '*', 0, 1);
        $lastcategory = array_shift($categories);
        $sortorder = $lastcategory->sortorder + 1;

        // insert ATO category
        $categoryobj = new \stdClass();
        $categoryobj->name       = ucfirst(get_string('pluginname', 'local_booking'));
        $categoryobj->sortorder  = $sortorder;

        $categoryid = $DB->insert_record('user_info_category', $categoryobj);
    } else { $categoryid = $category->id; }

    // get next sort order
    $customfields = $DB->get_records('user_info_field', null, 'sortorder DESC', '*', 0, 1);
    $lastcustomfield = array_shift($customfields);
    $fieldsortorder = !empty($lastcustomfield) ? $lastcustomfield->sortorder + 1 : 1;

    // Add VATSIM PID  simulator field under the ATO category if it doesn't exist
    $fieldsortorder = save_user_customfield($categoryid, $fieldsortorder, LOCAL_BOOKING_VATSIMCID,
    LOCAL_BOOKING_VATSIMCIDLABEL, 'text', 2);

    // Add primary simulator field under the ATO category if it doesn't exist
    $fieldsortorder = save_user_customfield($categoryid, $fieldsortorder, LOCAL_BOOKING_PRIMARYSIMULATOR,
        LOCAL_BOOKING_PRIMARYSIMULATORLABEL, 'menu', 2, LOCAL_BOOKING_DEFAULTSIMULATOR, LOCAL_BOOKING_SUPPORTEDSIMULATORS);

    // Add secondary simulator field under the ATO category if it doesn't exist
    $fieldsortorder = save_user_customfield($categoryid, $fieldsortorder, LOCAL_BOOKING_SECONDARYSIMULATOR,
        LOCAL_BOOKING_SECONDARYSIMULATORLABEL, 'menu', 2, ' ', ' ' . PHP_EOL . LOCAL_BOOKING_SUPPORTEDSIMULATORS);

    // Add callsign field if it doesn't exist
    $fieldsortorder = save_user_customfield($categoryid, $fieldsortorder, LOCAL_BOOKING_CALLSIGN,
        LOCAL_BOOKING_CALLSIGNLABEL, 'text', 2);
}

/**
 * Add custom ATO category and custom fields for all courses
 */
function create_course_customfields() {
    // get handler for course custom fields
    $handler = \core_customfield\handler::get_handler('core_course', 'course');

    // check if course category exists for ATO
    $categories = api::get_categories_with_fields('core_course', 'course', 0);
    $category = null;
    $categoryexists = false;
    $categoryid = -1;
    $categorysortorder = -1;
    foreach ($categories as $coursecategory) {
        $categoryid = $coursecategory->get('id');
        $categorysortorder = $coursecategory->get('sortorder');
        if ($coursecategory->get('name') == ucfirst(get_string('pluginname', 'local_booking'))) {
            $categoryexists = true;
            $category = $coursecategory;
            continue;
        }
    }

    // create course category for ATO if it doesn't exist
    if (!$categoryexists) {
        $category = category_controller::create(0, new \stdClass(), $handler);
        $category->set('name', ucfirst(get_string('pluginname', 'local_booking')));
        $category->set('descriptionformat', 0);
        $category->set('sortorder', $categorysortorder + 1);
        $category->set('component', 'core_course');
        $category->set('area', 'course');
        $category->set('contextid', 1);
        api::save_category($category);
    }

    // create course Session Booking custom fields for ATO
    save_course_customfield($category, 'checkbox', 'subscribed', get_string('useplugin', 'local_booking'),',"visibility":"0","checkbydefault":"0"');
    save_course_customfield($category, 'select', 'trainingtype', get_string('trainingtype', 'local_booking'),',"visibility":"0","options":"' .
            get_string('customfielddual', 'local_booking') . '\r\n' . get_string('customfieldmulticrew', 'local_booking') . '","defaultvalue":"' .
            get_string('customfielddual', 'local_booking') . '"');
    save_course_customfield($category, 'text', 'outcomerating',  get_string('outcomerating', 'local_booking'),
        ',"visibility":"0","defaultvalue":"","displaysize":15,"maxlength":50,"ispassword":"0","link":""', get_string('outcomeratingdesc', 'local_booking'));
    save_course_customfield($category, 'text', 'postingwait',  get_string('postingwait', 'local_booking'),
        ',"visibility":"0","defaultvalue":"","displaysize":5,"maxlength":2,"ispassword":"0","link":""', get_string('postingwaitdesc', 'local_booking'));
    save_course_customfield($category, 'text', 'onholdperiod',  get_string('onholdperiod', 'local_booking'),
        ',"visibility":"0","defaultvalue":"","displaysize":5,"maxlength":2,"ispassword":"0","link":""', get_string('onholdperioddesc', 'local_booking'));
    save_course_customfield($category, 'text', 'suspensionperiod',  get_string('suspensionperiod', 'local_booking'),
        ',"visibility":"0","defaultvalue":"","displaysize":5,"maxlength":2,"ispassword":"0","link":""', get_string('suspensionperioddesc', 'local_booking'));
    save_course_customfield($category, 'text', 'overdueperiod',  get_string('overdueperiod', 'local_booking'),
        ',"visibility":"0","defaultvalue":"","displaysize":5,"maxlength":2,"ispassword":"0","link":""', get_string('overdueperioddesc', 'local_booking'));
    save_course_customfield($category, 'text', 'homeicao',  get_string('homeicao', 'local_booking'),
        ',"visibility":"0","defaultvalue":"","displaysize":5,"maxlength":4,"ispassword":"0","link":""');
    save_course_customfield($category, 'textarea', 'aircrafticao', get_string('trainingaircraft', 'local_booking'),
        ',"visibility":"0","defaultvalue":"","defaultvalueformat":"1"', get_string('trainingaircraftdesc', 'local_booking'));
    save_course_customfield($category, 'textarea', 'exercisetitles', get_string('exercisetitles', 'local_booking'),
        ',"visibility":"0","defaultvalue":"","defaultvalueformat":"1"', get_string('exercisetitlesdesc', 'local_booking'));
    save_course_customfield($category, 'checkbox', 'requiresskillseval', get_string('requiresskillseval', 'local_booking'),
        ',"visibility":"0","checkbydefault":"0"', get_string('requiresskillsevaldesc', 'local_booking'));
    save_course_customfield($category, 'text', 'gradmsgsubject', get_string('gradmsgsubject', 'local_booking'),
        ',"visibility":"0","defaultvalue":"","displaysize":75,"maxlength":100,"ispassword":"0","link":""');
    save_course_customfield($category, 'textarea', 'gradmsgbody', get_string('gradmsgbody', 'local_booking'),
        ',"visibility":"0","defaultvalue":"","defaultvalueformat":"1"', get_string('gradmsgbodydesc', 'local_booking'));
}

/**
 * Persist course custom field.
 */
function save_course_customfield($category, $type, $shortname, $name, $configdata = '', $description = '') {
    $fieldexists = false;
    $field = null;
    $fieldsortorder = -1;
    $fields = $category->get_fields();
    // check if course custom field exists for ATO category
    foreach ($fields as $coursefield) {
        $fieldsortorder = $coursefield->get('sortorder');
        if ($coursefield->get('shortname') == $shortname) {
            $fieldexists = true;
            $field = $coursefield;
            continue;
        }
    }

    // create a field
    if (!$fieldexists) {
        $fieldrec = new \stdClass();
        $fieldrec->type = $type;
        $field = field_controller::create(0, $fieldrec, $category);
        $field->set('shortname', $shortname);
        $field->set('name', $name);
        $field->set('description', !empty($description) ? '<p dir="ltr" style="text-align:left;">' . $description . '</p>' : '');
        $field->set('descriptionformat', 1);
        $field->set('sortorder', $fieldsortorder + 1);
        $field->set('configdata', '{"required":"0","uniquevalues":"0","locked":"0"' . $configdata . '}');
        $field->save();
        field_created::create_from_object($field)->trigger();
    }
}

/**
 * Persist user custom field.
 */
function save_user_customfield($categoryid, $fieldsortorder, $shortname, $name, $type, $visibility, $default = '', $param1 = '') {
    global $DB;

    // Add primary simulator field under the ATO category if it doesn't exist
    $userfield = $DB->get_record('user_info_field', array('shortname'=>$shortname));
    if (empty($userfield)) {

        // build field object
        $field = new \stdClass();
        $field->name        = $name;
        $field->shortname   = $shortname;
        $field->description = '';
        $field->descriptionformat = 1;
        $field->visible     = $visibility;
        $field->datatype    = $type;
        $field->categoryid  = $categoryid;
        $field->sortorder   = $fieldsortorder;
        $field->defaultdata = $default;
        $field->param1      = $param1;

        $DB->insert_record('user_info_field', $field);
        $fieldsortorder = $fieldsortorder++;

    } else { $fieldsortorder = $userfield->sortorder + 1 ;}

    return $fieldsortorder;
}
