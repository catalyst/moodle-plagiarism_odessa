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

global $CFG;
require_once($CFG->dirroot . '/local/aws/sdk/aws-autoloader.php');
use GuzzleHttp\Client;

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

/**
 * Class odessa_client_api
 * @package plagiarism_odessa
 */
class odessa_client_api {

    public $baseurl;
    public $client;
    public $hash;
    public $endpoint;

    /**
     * odessa_client_api constructor.
     * initialise the API communication. Set default auth headers.
     *
     * @param $hash unique hash of the Moodle file.
     */
    public function __construct($hash) {
        // TODO get connection settings from the plugin admin settings.
        $this->baseurl = 'https://odessa';
        $this->client = new Client([
            'base_uri' => $this->baseurl,
            'headers' => ['X-Auth-Token' => '1784d1d4-ac5b-4db1-94a5-2b1e23e1e804'],
            'verify' => false,
        ]);
        $this->hash = $hash;
    }

    /**
     * Send metadata request to ODESSA
     *
     * @param $hash unique id the represents Moodle file.
     * @return mixed string with endpoint where to send the file.
     */
    public function send_metadata_set_endpoint() {
        $options = ['json' => ['id' => $this->hash]];

        try {
            $metadataresponse = $this->client->request('POST', '/api/submit', $options);
            // Successful response should give us endpoint (Location) where to submit user's file.
            if ($metadataresponse->getStatusCode() == 200 and $metadataresponse->hasHeader('Location')) {
                $this->endpoint = $metadataresponse->getHeader('Location')[0];
                return true;
            } else {
                throw new Exception('ERROR: Response for metadata request was not 200 or endpoint location was not provided.');
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Send file API to ODESSA.
     *
     * @param $content string text to send to ODESSA checker
     * @return bool|exception true if file was accepted by ODESSA, exception otherwise.
     */
    public function send_file($content) {

        // Check the endpoint is set.
        if (empty($this->endpoint)) {
            return false;
        }

        $options = ['body' => $content];

        try {
            // Make a file PUT request.
            $fileputresponse = $this->client->request('PUT', $this->endpoint . $this->hash, $options);
            if ($fileputresponse->getStatusCode() == 200) {
                return true;
            } else {
                throw new Exception('ERROR: Response for file PUT request was not 200.');
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Retrieve file API from ODESSA
     */
    public function retrieve_file() {
        // Test retrieval the file data from ODESSA
        $options = ['json' => ['ids' => [$this->hash]]];

        try {
            // Make a file PUT request.
            $retrievalresponse = $this->client->request('GET', 'https://odessa/api/retrieve', $options);
            if ($retrievalresponse->getStatusCode() == 200) {
                // $submission->record_check_file_submittion();
                $body = $retrievalresponse->getBody()->getContents();
                return $body;
            } else {
                throw new Exception('ERROR: Response for file PUT request was not 200.');
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}

