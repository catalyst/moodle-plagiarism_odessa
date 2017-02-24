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
 * observer.php - Contains methods which are called by observers from db/events.php
 *
 * @since 3.1
 * @package    plagiarism_odessa
 * @author     Suan Kan <suankan@catalyst-au.net>
 * @copyright  2017 Catalyst IT https://www.catalyst-au.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once(__DIR__ . '/../vendor/autoload.php');
use GuzzleHttp\Client;

/**
 * Class observers
 * Contains callback methods which are executed upon moodle events.
 * E.g. students upload or update files that need to be passed to plagiarism checker.
 * $observers defined in db/events.php execute methods from this class.
 *
 * @package plariarism_odessa
 */
class plagiarism_odessa_observer {
    public static function callback_submission_created($event) {
        $result = true;
        // TODO make use of ODESSA API call that e.g. uploads a file to ODESSA.

        return $result;
    }

    /**
     * Draft for send updated file to ODESSA to check the API
     * https://docs.google.com/document/d/1-AUwdfauYsE2YtSgZcTblanZHCu00olzdApBNP3wtZA/edit#
     *
     * @param $event
     * @return bool
     * @throws \Exception
     */
    public static function callback_submission_updated($event) {

        $baseurl = 'https://odessa';

        $fileid = '12121212121212';

        $client = new Client([
            'base_uri' => $baseurl,
            'headers' => ['X-Auth-Token' => '1784d1d4-ac5b-4db1-94a5-2b1e23e1e804'],
            'verify' => false,
        ]);

        // Phase 1.
        // Make a Metadata request.
        // JSON params of the call should be retrieved from the $event. Make a stub for just now.
        $options = [
            'json' => [
                'id' => $fileid,
                'course' => 'Maths 101',
                'user' => '4',
                'submission' => '42731',
            ]
        ];

        try {
            $metadataresponse = $client->request('POST', '/api/submit', $options);
            // Successful response should give us endpoint (Location) where to submit user's file.
            if ($metadataresponse->getStatusCode() == 200 and $metadataresponse->hasHeader('Location')) {
                plagiarism_odessa\submissions_manager::record_file_metadata_submittion();
                $endpoint = $metadataresponse->getHeader('Location')[0];
            } else {
                throw new Exception('ERROR: Response for metadata request was not 200 or endpoint location was not provided.');
            }
        } catch(\Exception $e) {
            echo $e->getMessage();
            return;
        }

        // Phase 2.
        // Submit user file to ODESSA checker.
        $body = \GuzzleHttp\Psr7\stream_for('hello!');
        $options = [
            'body' => $body,
        ];

        try {
            // Make a file PUT request.
            $fileputresponse = $client->request('PUT', $endpoint . $fileid . '.txt', $options);
            if ($fileputresponse->getStatusCode() == 200) {
                plagiarism_odessa\submissions_manager::record_file_submittion();
            } else {
                throw new Exception('ERROR: Response for file PUT request was not 200.');
            }
        } catch(\Exception $e) {
            echo $e->getMessage();
            return;
        }

        // Phase 3.
        // Test retrieval the file data from ODESSA
        $options = [
            'json' => [
                'ids' => [$fileid,],
            ],
        ];

        try {
            // Make a file PUT request.
            $retrievalresponse = $client->request('GET', 'https://odessa/api/retrieve', $options);
            if ($fileputresponse->getStatusCode() == 200) {
                plagiarism_odessa\submissions_manager::record_check_file_submittion();
                $body = $retrievalresponse->getBody()->getContents();
            } else {
                throw new Exception('ERROR: Response for file PUT request was not 200.');
            }
        } catch(\Exception $e) {
            echo $e->getMessage();
            return;
        }

        return true;
    }
}
