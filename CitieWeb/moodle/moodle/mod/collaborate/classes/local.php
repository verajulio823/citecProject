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
 * Common local functions used by the collaborate module.
 *
 * @package   mod_collaborate
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_collaborate;

defined('MOODLE_INTERNAL') || die();

use mod_collaborate\soap\generated\SetHtmlSession;
use mod_collaborate\soap\generated\ServerConfiguration;
use mod_collaborate\soap\generated\UpdateHtmlSessionDetails;
use mod_collaborate\soap\generated\HtmlAttendeeCollection;
use mod_collaborate\soap\generated\HtmlAttendee;
use mod_collaborate\soap\generated\HtmlSessionRecording;
use mod_collaborate\soap\api;

class local {
    /**
     * Get timeend from duration.
     *
     * @param int $timestart
     * @param int $duration
     * @return int
     */
    public static function timeend_from_duration($timestart, $duration) {
        if ($duration != 9999) {
            $timeend = ($timestart + intval($duration));
        } else {
            $timeend = strtotime('3000-01-01 00:00');
        }
        return $timeend;
    }

    /**
     * Get boundary time in minutes.
     *
     * @return int
     */
    public static function boundary_time() {
        // Hard coded.
        return 15;
    }

    /**
     * get_times
     *
     * @param int | object $collaborate
     * @return object
     */
    public static function get_times($collaborate) {
        global $DB;

        if (!is_object($collaborate)) {
            $collaborate = $DB->get_record('collaborate', array('id' => $collaborate));
        }
        $times = (object) array(
            'start' => intval($collaborate->timestart),
            'end' => self::timeend_from_duration($collaborate->timestart, $collaborate->duration),
            'duration' => $collaborate->duration
        );
        return ($times);
    }

    /**
     * Update the calendar entries for this assignment.
     *
     * @param \stdClass $collaborate- collaborate record
     *
     * @return bool
     */
    public static function update_calendar($collaborate) {
        global $DB, $CFG;
        require_once($CFG->dirroot.'/calendar/lib.php');

        $event = new \stdClass();

        $params = array('modulename' => 'collaborate', 'instance' => $collaborate->id);
        $event->id = $DB->get_field('event', 'id', $params);
        $event->name = $collaborate->name;
        $event->timestart = $collaborate->timestart;

        // Convert the links to pluginfile. It is a bit hacky but at this stage the files
        // might not have been saved in the module area yet.
        $intro = $collaborate->intro;
        if ($draftid = file_get_submitted_draft_itemid('introeditor')) {
            $intro = file_rewrite_urls_to_pluginfile($intro, $draftid);
        }

        // We need to remove the links to files as the calendar is not ready
        // to support module events with file areas.
        $intro = strip_pluginfile_content($intro);

        $event->description = array(
            'text' => $intro,
            'format' => $collaborate->introformat
        );

        if ($event->id) {
            $calendarevent = \calendar_event::load($event->id);
            $calendarevent->update($event);
        } else {
            unset($event->id);
            $event->courseid    = $collaborate->course;
            $event->groupid     = 0;
            $event->userid      = 0;
            $event->modulename  = 'collaborate';
            $event->instance    = $collaborate->id;
            $event->eventtype   = 'due';
            $event->timeend = self::timeend_from_duration($collaborate->timestart, $collaborate->duration);
            if (!empty($event->timeend)) {
                $event->timeduration = ($event->timeend - $event->timestart);
            } else {
                $event->timeduration = 0;
            }
            \calendar_event::create($event);
        }
    }

    /**
     * Convert servertime to utc time or vise-a-versa
     *
     * @param int|\DateTime $time
     * @param bool $fromutc
     * @return bool|int
     */
    private static function process_time($time, $fromutc = false) {

        if ($time instanceof \DateTime) {
            $time = clone ($time); // Clone to break reference.
        }

        // Is this a string that should be an integer? This is stricter than is_numeric.
        if (is_string($time) && strval(intval($time)) === $time) {
            // This is a string that should be an integer - e.g. UTS that has come from a database.
            $time = intval($time);
        }
        if (is_string($time)) {
            if (substr(trim($time), -1, 1) == 'Z') {
                // The date has been specified as a UTC date (see ISO 8601) so strtotime will automatically convert it
                // to local server time.
                return strtotime($time);
            }
            $time = strtotime($time);
        } else if ($time instanceof \DateTime) {
            $time = $time->getTimestamp();
        }
        $servertzoneoffset = date('Z');
        if ($fromutc) {
            $time += $servertzoneoffset;
        } else {
            $time -= $servertzoneoffset;
        }
        return $time;
    }

    /**
     * Convert a time on the server - e.g. in db - to a UTC time.
     * @param int|\DateTime $time
     *
     * @return bool|int
     */
    public static function servertime_to_utc($time) {
        return self::process_time($time, false);
    }

    /**
     * Convert a UTC time to a server time.
     * @param int|\DateTime $time
     * @return bool|int
     */
    public static function utc_to_servertime($time) {
        return self::process_time($time, true);
    }

    /**
     * Is this module configured?
     * @return bool
     */
    public static function configured() {
        $config = get_config('collaborate');

        if (!empty ($config)
            && !empty($config->server)
            && !empty($config->username)
            && !empty($config->password)
        ) {
            return true;
        }

        return false;
    }

    /**
     * Make sure module is configured or throw error.
     * @throws \moodle_exception
     */
    public static function require_configured() {
        if (!static::configured()) {
            throw new \moodle_exception('error:noconfiguration', 'mod_collaborate');
        }
    }

    /**
     * Verify that the api works.
     *
     * @param bool $silent
     * @param bool|stdClass $config
     * @return bool
     */
    public static function api_verified($silent = false, $config = false) {
        static $apiverified = null;
        // Only do this once! settings.php was calling this 3 times, hence the static to stop this!
        if ($apiverified !== null) {
            return $apiverified;
        }

        $config = $config ? $config : get_config('collaborate');

        if (static::configured()) {
            $param = new ServerConfiguration();
            try {
                $api = api::get_api(true, [], null, $config);
            } catch (\Exception $e) {
                $api = false;
            }
            if ($api && $api->is_usable()) {
                // If silent, will stop error output for now.
                $api->set_silent($silent);
                try {
                    $result = @$api->GetServerConfiguration($param);
                } catch (\Exception $e) {
                    $result = false;
                }
                // Renable error output.
                $api->set_silent(false);
            } else {
                $result = false;
            }
        } else {
            $result = false;
        }
        $apiverified = false;
        if (!empty($result)) {
            $configresp = $result->getServerConfigurationResponse();
            if (!empty($configresp[0])) {
                $tzone = $configresp[0]->getTimeZone();
                if (!empty($tzone)) {
                    $apiverified = true;
                }
             }
         }
        return ($apiverified);
    }

    /**
     * Get API times from start unix timestamps and duration.
     *
     * @param $starttime
     * @param $duration
     * @return array
     */
    public static function get_apitimes($starttime, $duration) {
        // Note it would be great if we could use date('c', $data->timestart) which would include the server timezone
        // offset in the date - e.g. 2015-04-02T17:00:00+01:00.
        // However, the apollo api does not accept 2015-04-02T17:00:00+01:00
        // So we are converting starttime to a UTC date by subtracting the server time zone offset.
        $starttime = self::servertime_to_utc($starttime);
        $endtime = self::timeend_from_duration($starttime, $duration);
        $timestart = self::api_datetime($starttime);
        $timeend = self::api_datetime($endtime);
        return [$timestart, $timeend];
    }

    /**
     * Take a utctime (adjusted by server timezone offset) and return a date suitable for the API.
     *
     * NOTE: date('c', $data->timestart) doesn't work with the API as it treates any time date with a + symbol in it as
     * invalid. Therefore, this function expects the date passed in to already be a UTC date WITHOUT an offset.
     * @param $utctime
     *
     * @return string
     */
    public static function api_datetime($utctime) {
        $dt = new \DateTime(date('Y-m-d H:i:s', $utctime), new \DateTimeZone('UTC'));
        $dt->format('Y-m-d\TH:i:s\Z');
        return $dt;
    }

    /**
     * Create a session based on $data for specific $course
     *
     * @param $data
     * @param $course
     * @return mixed
     * @throws \moodle_exception
     */
    public static function api_create_session($data, $course) {
        $config = get_config('collaborate');

        $data->timeend = self::timeend_from_duration($data->timestart, $data->duration);
        $htmlsession = self::el_set_html_session($data, $course);
        $api = api::get_api();

        $result = $api->SetHtmlSession($htmlsession);
        if (!$result) {
            $msg = 'SetHtmlSession';
            if (!empty($config->wsdebug)) {
                $msg .= ' - returned: '.var_export($result, true);
            }
            $api->process_error('error:apicallfailed', logging\constants::SEV_CRITICAL, $msg);
        }
        $respobjs = $result->getHtmlSession();
        if (!is_array($respobjs) || empty($respobjs)) {
            $api->process_error(
                'error:apicallfailed', logging\constants::SEV_CRITICAL,
                'SetHtmlSession - failed on $result->getApolloSessionDto()'
            );
        }
        $respobj = $respobjs[0];
        $sessionid = $respobj->getSessionId();
        return ($sessionid);
    }

    /**
     * Get enrolee ids for course.
     *
     * @param \stdClass|string $course
     * @param string $withcapability
     * @param string $withoutcapability
     * @param int $groupid
     * @return array
     */
    public static function enrolees_array(
                                        $course,
                                        $withcapability = '',
                                        $withoutcapability = '',
                                        $groupid = 0,
                                        $incuser = false,
                                        $excuser = false) {

        $courseid = is_string($course) ? $course : $course->id;
        $excludeuserids = [];
        if ($excuser) {
            $excludeuserids[] = $excuser;
        }
        $ids = [];
        if ($incuser) {
            $ids[] = $incuser;
        }
        $users = get_enrolled_users(\context_course::instance($courseid), $withcapability, $groupid);
        if (!empty($withoutcapability)) {
            $excludeusers = get_enrolled_users(\context_course::instance($courseid), $withoutcapability, $groupid);
            foreach ($excludeusers as $user) {
                $excludeuserids[] = $user->id;
            }
        }

        foreach ($users as $user) {
            if (!in_array($user->id, $excludeuserids)) {
                $ids[] = $user->id;
            }
        }

        return array_unique($ids);
    }

    /**
     * Get chair enrolees for course.
     *
     * @param \stdClass|string $course
     * @param int $groupid
     * @return array
     */
    public static function moderator_enrolees($course, $groupid = 0) {
        global $USER;
        return self::enrolees_array($course, 'moodle/grade:viewall', '', $groupid, $USER->id);
    }

    /**
     * Get non-chair enrolees for course.
     *
     * @param \stdClass|string $course
     * @param int $groupid
     * @return array
     */
    public static function participant_enrolees($course, $groupid = 0) {
        global $USER;
        return self::enrolees_array($course, '', 'moodle/grade:viewall', $groupid, false, $USER->id);
    }

    /**
     * Create appropriate session param element for new session or existing session.
     *
     * @param $data
     * @param $course
     * @param null $sessionid
     * @return SetHtmlSession|UpdateHtmlSessionDetails
     */
    protected static function el_html_session($data, $course, $sessionid = null) {
        global $USER;

        // Main variables for session.
        list ($timestart, $timeend) = self::get_apitimes($data->timestart, $data->duration);
        $description = isset($data->introeditor['text']) ? $data->introeditor['text'] : $data->intro;

        // Setup appropriate session - set or update.
        if (empty($sessionid)) {
            // New session.
            $htmlsession = new SetHtmlSession($data->name, $timestart, $timeend, $USER->id);
        } else {
            // Update existing session.
            $htmlsession = new UpdateHtmlSessionDetails($sessionid);
            $htmlsession->setName($data->name);
            $htmlsession->setStartTime($timestart);
            $htmlsession->setEndTime($timeend);
        }
        $htmlsession->setDescription(strip_tags($description));
        $htmlsession->setBoundaryTime(self::boundary_time());
        $htmlsession->setMustBeSupervised(true);

        // Add attendees to html session.
        $attendees = new HtmlAttendeeCollection();
        $moderators = self::moderator_enrolees($course);
        $participants = self::participant_enrolees($course);
        $attarr = [];
        foreach ($moderators as $moderatorid) {
            $attarr[] = new HtmlAttendee($moderatorid, 'moderator');
        }
        foreach ($participants as $participantid) {
            $attarr[] = new HtmlAttendee($participantid, 'participant');
        }
        $attendees->setHtmlAttendee($attarr);
        $htmlsession->setHtmlAttendees([$attendees]);

        return $htmlsession;
    }

    /**
     * Build SetHtmlSession element
     *
     * @param $data
     * @param \stdClass|string $course
     * @return soap\generated\SetHtmlSession
     */
    public static function el_set_html_session($data, $course) {
        return self::el_html_session($data, $course);
    }

    /**
     * Build UpdateHtmlSession element
     *
     * @param $data
     * @param \stdClass|string $course
     * @return soap\generated\SetHtmlSession
     */
    public static function el_update_html_session($data, $course) {
        return self::el_html_session($data, $course, $data->sessionid);
    }

    /**
     * Is the current request via ajax?
     *
     * @return bool
     */
    public static function via_ajax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }

    /**
     * get recordings
     *
     * @param int | object $collaborate
     * @return array
     */
    public static function get_recordings($collaborate) {
        global $DB;

        if (!is_object($collaborate)) {
            $collaborate = $DB->get_record('collaborate', array('id' => $collaborate));
        }

        if ($collaborate->sessionid === null) {
            // Session has not been initialised - possibly a duplicated session.
            return [];
        }

        $config = get_config('collaborate');

        $api = api::get_api();
        $session = new HtmlSessionRecording();
        $session->setSessionId($collaborate->sessionid);
        $result = $api->ListHtmlSessionRecording($session);
        if (!$result) {
            return [];
        }
        $respobjs = $result->getHtmlSessionRecordingResponse();
        if (!is_array($respobjs) || empty($respobjs)) {
            return [];
        }
        return $respobjs;
    }

}
