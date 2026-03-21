<?php
// This file is part of Moodle - http://moodle.org/

namespace local_booking;

defined('MOODLE_INTERNAL') || die();

use local_booking\output\redirect_handler;

/**
 * Assignment grading redirect hooks.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @category   event
 * @copyright  BAVirtual.co.uk © 2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assign_grading_callbacks {

    /**
     * Callback for before_standard_top_of_body_html_generation hook
     *
     * @param \core\hook\output\before_standard_top_of_body_html_generation $hook
     */
    public static function before_standard_top_of_body_html(\core\hook\output\before_standard_top_of_body_html_generation $hook): void {
        global $SESSION, $PAGE;

        // Check on any page (will redirect if page is assignment submission view page is set)
        $pagetype = !empty($SESSION->frompage) ? $SESSION->frompage : $PAGE->pagetype;
        if ($pagetype === 'mod-assign-view') {

            // Get the redirect URL from the session variable set in the observer
            $redirect = $SESSION->booking_redirect;

            // Clear the redirect URL from the session variable set in the observer to prevent unintended redirects on subsequent page loads
            unset($SESSION->booking_redirect);
            unset($SESSION->frompage);

            // Render the redirect handler
            if (!empty($redirect)) {
                redirect(new \moodle_url($redirect), get_string('redirectingtostudentreport', 'local_booking'), 2000);
            }
        }
        return;
    }
}
