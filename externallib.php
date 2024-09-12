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
 * Oulearn webservices
 *
 * @package    local_oulearn
 * @category   external
 * @copyright  2023 Luiz Guilherme Dall' Acqua
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use enrol_lti\helper;

defined('MOODLE_INTERNAL') || die;


require_once("$CFG->libdir/externallib.php");
require_once("lib.php");


class local_oulearn_external extends external_api
{
    /**
     * @return external_multiple_structure
     */
    public static function add_course_enrolment_method_returns()
    {
        return new external_multiple_structure(
            new external_single_structure([
                'id' => new external_value(PARAM_INT, 'Instance ID'),
                'url' => new external_value(PARAM_TEXT, 'URL'),
                'launchurl' => new external_value(PARAM_TEXT, 'Launch URL'),
                'secret' => new external_value(PARAM_TEXT, 'Secret'),
            ], 'object')
        );
    }

    /**
     * @return external_function_parameters
     */
    public static function add_course_enrolment_method_parameters()
    {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID', VALUE_REQUIRED),
            'type' => new external_value(PARAM_ALPHANUM, 'Method type', VALUE_REQUIRED),
            'name' => new external_value(PARAM_TEXT, 'Instance name', VALUE_REQUIRED),
            'enrolstartdate' => new external_value(PARAM_INT, 'Start date ', VALUE_REQUIRED),
            'enrolenddate' => new external_value(PARAM_INT, 'End date ', VALUE_REQUIRED),
        ]);
    }

    /**
     * @param int $courseid
     * @param string $type
     * @param string $name
     * @param int $enrolstartdate
     * @param int $enrolenddate
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     */
    public static function add_course_enrolment_method(int $courseid, string $type, string $name, int $enrolstartdate, int $enrolenddate)
    {
        global $USER, $DB;

        [
            'courseid' => $courseid,
            'type' => $type,
            'name' => $name,
            'enrolstartdate' => $enrolstartdate,
            'enrolenddate' => $enrolenddate
        ] = self::validate_parameters(self::add_course_enrolment_method_parameters(), [
            'courseid' => $courseid,
            'type' => $type,
            'name' => $name,
            'enrolstartdate' => $enrolstartdate,
            'enrolenddate' => $enrolenddate
        ]);

        $plugin = enrol_get_plugin($type);
        if (!$plugin) {
            throw new moodle_exception('invaliddata', 'error');
        }

        $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
        $context = context_course::instance($course->id, MUST_EXIST);

        $fields = [
            'contextid' => $context->id,
            'name' => $name,
            'enrolenddate' => $enrolenddate,
            'enrolstartdate' => $enrolstartdate,
            'secret' => random_string(32),
            'rolelearner' => 5,
            'roleinstructor' => 3,
            'gradesync' => 1,
            'gradesynccompletion' => 0,
            'membersync' => 1,
            'maildisplay' => 0,
            'membersyncmode' => \enrol_lti\helper::MEMBER_SYNC_ENROL_AND_UNENROL,

        ];

        $instanceid = $plugin->add_instance($course, $fields);

        $sql = "SELECT elt.*, e.name, e.courseid, e.status, e.enrolstartdate, e.enrolenddate, e.enrolperiod
                  FROM {enrol_lti_tools} elt
                  JOIN {enrol} e
                    ON elt.enrolid = e.id
                 WHERE elt.enrolid = :tid";

        $tool = $DB->get_record_sql($sql, array('tid' => $instanceid), MUST_EXIST);
        $obj = new stdClass();
        $obj->id = $instanceid;
        $obj->url = helper::get_cartridge_url($tool)->out();;
        $obj->launchurl = helper::get_launch_url($tool->id)->out();
        $obj->secret = $tool->secret;

        return [$obj];
    }

    /**
     * @return external_multiple_structure
     */
    public static function update_course_enrolment_method_returns()
    {
        return new external_multiple_structure(
            new external_single_structure([
                'id' => new external_value(PARAM_INT, 'Instance ID'),
                'url' => new external_value(PARAM_TEXT, 'URL'),
                'launchurl' => new external_value(PARAM_TEXT, 'Launch URL'),
                'secret' => new external_value(PARAM_TEXT, 'Secret'),
            ], 'object')
        );
    }

    /**
     * @return external_function_parameters
     */
    public static function update_course_enrolment_method_parameters()
    {
        return new external_function_parameters([
            'instanceid' => new external_value(PARAM_INT, 'Instance ID', VALUE_REQUIRED),
            'type' => new external_value(PARAM_ALPHANUM, 'Method type', VALUE_REQUIRED),
            'name' => new external_value(PARAM_TEXT, 'Instance name', VALUE_REQUIRED),
            'enrolstartdate' => new external_value(PARAM_INT, 'Start date ', VALUE_REQUIRED),
            'enrolenddate' => new external_value(PARAM_INT, 'End date ', VALUE_REQUIRED),
        ]);
    }

    /**
     * @param int $instanceid
     * @param string $type
     * @param string $name
     * @param int $enrolstartdate
     * @param int $enrolenddate
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     */
    public static function update_course_enrolment_method(int $instanceid, string $type, string $name, int $enrolstartdate, int $enrolenddate)
    {
        global $USER, $DB;

        [
            'instanceid' => $instanceid,
            'type' => $type,
            'name' => $name,
            'enrolstartdate' => $enrolstartdate,
            'enrolenddate' => $enrolenddate
        ] = self::validate_parameters(self::update_course_enrolment_method_parameters(), [
            'instanceid' => $instanceid,
            'type' => $type,
            'name' => $name,
            'enrolstartdate' => $enrolstartdate,
            'enrolenddate' => $enrolenddate
        ]);

        $plugin = enrol_get_plugin($type);
        if (!$plugin) {
            throw new moodle_exception('invaliddata', 'error');
        }
        $instance = $DB->get_record('enrol', ['id' => $instanceid], '*', MUST_EXIST);
        $instance->timemodified = time();
        $instance->name = $name;
        $instance->enrolenddate = $enrolenddate;
        $instance->enrolstartdate = $enrolstartdate;
        $update = $DB->update_record('enrol', $instance);
        if ($update) {
            \core\event\enrol_instance_updated::create_from_record($instance)->trigger();
        }

        $sql = "SELECT elt.*, e.name, e.courseid, e.status, e.enrolstartdate, e.enrolenddate, e.enrolperiod
                  FROM {enrol_lti_tools} elt
                  JOIN {enrol} e
                    ON elt.enrolid = e.id
                 WHERE elt.enrolid = :tid";

        $tool = $DB->get_record_sql($sql, array('tid' => $instanceid), MUST_EXIST);
        $obj = new stdClass();
        $obj->id = $instanceid;
        $obj->url = helper::get_cartridge_url($tool)->out();;
        $obj->launchurl = helper::get_launch_url($tool->id)->out();
        $obj->secret = $tool->secret;

        return [$obj];
    }

    /**
     * @return external_multiple_structure
     */
    public static function remove_course_enrolment_method_returns()
    {
        return null;
    }

    /**
     * @return external_function_parameters
     */
    public static function remove_course_enrolment_method_parameters()
    {
        return new external_function_parameters([
            'instanceid' => new external_value(PARAM_INT, 'Instance ID', VALUE_REQUIRED),
        ]);
    }

    /**
     * @param int $instanceid
     * @return null
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     */
    public static function remove_course_enrolment_method(int $instanceid)
    {
        global $USER, $DB;

        ['instanceid' => $instanceid] = self::validate_parameters(self::remove_course_enrolment_method_parameters(), ['instanceid' => $instanceid]);
        $instance = $DB->get_record('enrol', ['id' => $instanceid], '*', MUST_EXIST);
        $plugin = enrol_get_plugin($instance->enrol);
        $plugin->delete_instance($instance);
        return null;
    }
}