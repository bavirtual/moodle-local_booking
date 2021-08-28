<?php
// This file is part of Moodle - https://moodle.org/
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
 * Adds admin settings for the plugin.
 *
 * @package     local_booking
 * @category    admin
 * @copyright   2021 Mustafa Hajjar
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $ADMIN->add('localplugins', new admin_category('local_booking_settings', new lang_string('pluginname', 'local_booking')));
    $settingspage = new admin_settingpage('managelocalbooking', new lang_string('pluginname', 'local_booking'));

    if ($ADMIN->fulltree) {
        // exercise titles
        $settingspage->add(new admin_setting_configtext('local_booking/exercisetitles',
            new lang_string('exercisetitles', 'local_booking'), new lang_string('exercisetitlesdesc', 'local_booking'),
            '', PARAM_RAW)
        );
    }

    if ($ADMIN->fulltree) {
        // last session recency days weight multiplier
        $settingspage->add(new admin_setting_configtext('local_booking/recencydaysweight',
            new lang_string('recencydaysweight', 'local_booking'), new lang_string('recencydaysweightdesc', 'local_booking'),
            10, PARAM_INT)
        );
    }

    if ($ADMIN->fulltree) {
        // slot count weight multiplier
        $settingspage->add(new admin_setting_configtext('local_booking/slotcountweight',
            new lang_string('slotcountweight', 'local_booking'), new lang_string('slotcountweightdesc', 'local_booking'),
            10, PARAM_INT)
        );
    }

    if ($ADMIN->fulltree) {
        // activity count weight multiplier
        $settingspage->add(new admin_setting_configtext('local_booking/activitycountweight',
            new lang_string('activitycountweight', 'local_booking'), new lang_string('activitycountweightdesc', 'local_booking'),
            1, PARAM_INT)
        );
    }

    if ($ADMIN->fulltree) {
        // lesson completion weight multiplier
        $settingspage->add(new admin_setting_configtext('local_booking/completionweight',
            new lang_string('completionweight', 'local_booking'), new lang_string('completionweightdesc', 'local_booking'),
            10, PARAM_INT)
        );
    }

    $ADMIN->add('localplugins', $settingspage);
}