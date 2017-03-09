Travis integration: [![Build Status](https://travis-ci.org/catalyst/moodle-plagiarism_odessa.svg?branch=master)](https://travis-ci.org/catalyst/moodle-plagiarism_odessa)

# moodle-plagiarism_odessa

Odessa plagiarism plugin for Moodle.
 
Enables sending student submissions to ODESSA plagiarism checker.

## Installation

To install the plugin as a git submodule:
* `cd Moodle_website_DocumentRoot` 
* `git submodule add -b master git@github.com:catalyst/moodle-plagiarism_odessa.git plagiarism/odessa`
* `cd plagiarism/odessa`
* [Install composer](https://getcomposer.org/download/)
* `php composer.phar install`
