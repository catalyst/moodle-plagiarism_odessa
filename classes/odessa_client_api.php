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
 * odessa_client_api.php - Contains methods to communicate with ODESSA plagiarism checking provider.
 *
 * @since 3.1
 * @package    plagiarism_odessa
 * @author     Suan Kan <suankan@catalyst-au.net>
 * @copyright  2017 Catalyst IT https://www.catalyst-au.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_odessa;
require_once(__DIR__ . '/../vendor/autoload.php');
use GuzzleHttp\Client;

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

/**
 * Class odessa_client_api
 * @package plagiarism_odessa
 */
class odessa_client_api {
    
    public $client;
    public $baseurl = 'https://odessa';
    public $fileid = '12121212121212';

    /**
     * odessa_client_api constructor.
     * initialise the API communication. Set default auth headers.
     */
    public function __construct() {
        // TODO get connection settings from the plugin admin settings.
        $this->client = new Client([
            'base_uri' => $this->baseurl,
            'headers' => ['X-Auth-Token' => '1784d1d4-ac5b-4db1-94a5-2b1e23e1e804'],
            'verify' => false,
        ]);
    }

    /**
     * Send metadata request API to ODESSA.
     */
    public function send_metadata() {
        // TODO
        // Phase 1.
        // Make a Metadata request.
        // JSON params of the call should be retrieved from the $event. Make a stub for just now.
        $options = [
            'json' => [
                'id' => $this->fileid,
                'course' => 'Maths 101',
                'user' => '4',
                'submission' => '42731',
            ]
        ];

        try {
            $metadataresponse = $this->client->request('POST', '/api/submit', $options);
            // Successful response should give us endpoint (Location) where to submit user's file.
            if ($metadataresponse->getStatusCode() == 200 and $metadataresponse->hasHeader('Location')) {
                //$submission->record_file_metadata_submittion();
                $endpoint = $metadataresponse->getHeader('Location')[0];
            } else {
                throw new Exception('ERROR: Response for metadata request was not 200 or endpoint location was not provided.');
            }
        } catch(\Exception $e) {
            echo $e->getMessage();
            return;
        }
    }

    /**
     * Send file API to ODESSA.
     * 
     * @param $file
     */
    public function send_file($file) {
        // Phase 2.
        // Submit user file to ODESSA checker.
        $body = \GuzzleHttp\Psr7\stream_for('hello!');
        $options = [
            'body' => $body,
        ];

        try {
            // Make a file PUT request.
            $fileputresponse = $this->client->request('PUT', $endpoint . $this->fileid . '.txt', $options);
            if ($fileputresponse->getStatusCode() == 200) {
                // $submission->record_file_submittion();
            } else {
                throw new Exception('ERROR: Response for file PUT request was not 200.');
            }
        } catch(\Exception $e) {
            echo $e->getMessage();
            return;
        }
    }

    /**
     * Retrieve file API from ODESSA
     */
    public function retrieve_file() {
        // Phase 3.
        // Test retrieval the file data from ODESSA
        $options = [
            'json' => [
                'ids' => [$this->fileid,],
            ],
        ];

        try {
            // Make a file PUT request.
            $retrievalresponse = $this->client->request('GET', 'https://odessa/api/retrieve', $options);
            if ($fileputresponse->getStatusCode() == 200) {
                // $submission->record_check_file_submittion();
                $body = $retrievalresponse->getBody()->getContents();
            } else {
                throw new Exception('ERROR: Response for file PUT request was not 200.');
            }
        } catch(\Exception $e) {
            echo $e->getMessage();
            return;
        }
    }
}

