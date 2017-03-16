Travis integration: [![Build Status](https://travis-ci.org/catalyst/moodle-plagiarism_odessa.svg?branch=master)](https://travis-ci.org/catalyst/moodle-plagiarism_odessa)

# moodle-plagiarism_odessa

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
wget https://github.com/catalyst/moodle-local_aws/archive/master.zip
sudo -u www-data mkdir Moodle_website_DocumentRoot/plagiarism/odessa
sudo -u www-data unzip master.zip -d Moodle_website_DocumentRoot/plagiarism/odessa
```

After that login to Moodle UI as admin and finish installation there.
