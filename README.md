Travis integration: [![Build Status](https://travis-ci.org/catalyst/moodle-plagiarism_odessa.svg?branch=master)](https://travis-ci.org/catalyst/moodle-plagiarism_odessa)

# moodle-plagiarism_odessa

## Description

Odessa plagiarism plugin for Moodle.
 
Enables sending student submissions to ODESSA plagiarism checker.

## Installation

Plugin [local_aws](https://github.com/catalyst/moodle-local_aws) needs to be installed first. Please install it according to its installation instructions.

Two ways to install plagiarism_odessa plugin:
### As a git submodule:
```
cd Moodle_website_DocumentRoot 
git submodule add -b master git@github.com:catalyst/moodle-plagiarism_odessa.git plagiarism/odessa
```

### By zip archive:
```
wget https://github.com/catalyst/moodle-plagiarism_odessa/archive/master.zip
sudo -u www-data mkdir Moodle_website_DocumentRoot/plagiarism/odessa
sudo -u www-data unzip master.zip -d Moodle_website_DocumentRoot/plagiarism/odessa
```

After that login to Moodle UI as admin and finish installation there.

## Technical implementation details

### Integration
Currently the plugin is integrated to process submissions from:
* mod_assign
* mod_forum
* mod_workshop

### Features
Currently the plugin supports just a few essential features:

#### Odessa submission manager queue (class submissions_manager)

Table mdl_odessa_submissions keeps the following info of submission records:

|Column          |Comment                                     |
|----------------|--------------------------------------------|
|id              |record ID                                   |
|sourcecomponent |name of plugin where submission was created |
|type            |either 'onlinetext' or 'file'               |
|userid          |user the submission belongs to              |
|courseid        |course the submission belongs to            | 
|contextid       |Course module context ID                    | 
|pathnamehash    |File API identifier                         | 
|contenthash     |File API identifier                         | 
|status          |For future use.                             | 
|laststatus      |For future use.                             | 
|result          |Result from plagiarism checking provider    | 
|timecreated     |                                            | 
|timeupdated     |                                            | 

#### Capturing students' submissions at realtime. 

The idea is to queue users' submissions realtime for further sending to the plagiarism provider asynchroneously. 

Capturing is done by creating observers for native Moodle event `assessable_uploaded`. 
This event is issued by mod_assign, mod_forum and mod_workshop when students create/update/save their submissions in UI.

#### Gathering existing submissions 

The idea is to have a CLI script that will gather all existing users' submissions from mod_assign, mod_forum and mod_workshop 
and to line them up in Odessa submission manager queue.

Sample CLI usage:
```
cd plagiarism/odessa
sudo -u www-data php cli/existing_submissions.php --plugin=mod_workshop
```

#### REST API-client for ODESSA plagiarism checking engine

See `class plagiarism_odessa\odessa_client_api` .

As of yet ODESSA plagiarism checking engine accepts 3 methods:
* Sending submission metadata
method send_metadata_set_endpoint()
* Send file
method send_file()
* Retrieve file
method retrieve_file()

Yet this is a functional draft which makes REST calls by means of GuzzleHttp\Client.

### A note of types of submissions in Moodle 3.1

Natively modules mod_assign, mod_forum and mod_workshop allow users to make two types of submissions.

#### File submission

In Moodle UI users are offered to attach their files via File picker which employs File API.

For each file submission we keep pathnamehash and contenthash in the Odessa submission manager queue which identifies these files uniquely.

#### Onlinetext submission

In Moodle UI users are offered to enter some text via usual textarea input field. The difference comparing to the above file submissions is that such input is saved as a plain text in corresponding db tables of mod_assign, mod_forum and mod_workshop.

##### Problem

This makes a problem because we aim to keep only pathnamehash and contenthash as unique submissions' identifiers in Odessa submission manager queue. That is for a sake of universal interface for working with items of Odessa submission manager queue later.

##### Solution
To workaround this in the event observer we extract onlinetext from users' submissions and save it via Moodle File API. And then we save the submission record by pathnamehash and contenthash.

Since Moodle event `assessable_uploaded` is fired each time user creates/updates/saves their submissions (confirmed behaviour in all three modules mod_assign, mod_forum and mod_workshop) - we are guaranteed that our Moodle File always corresponds to the onlinetext from user's submission.
