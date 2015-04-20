General information
====================
moodle-analyst is a tool for support and administration tasks for 
moodle (https://moodle.org/), implemented as a report plugin.


Features - summary
===================
- extreme fast and accurate course and user search
- get fast summary of users and courses with all relevant information
- reach frequently used moodle administration functions without any reloads

Did I mention that everything is pretty fast? ;)


Status: beta
=============
moodle-analyst has proved itself very useful on our productive system
(Apache on Linux with MSSQL backend) with about 25,000 users. We also tested it 
successfully on smaller systems with MySQL backend. 
We would like to hear all about your experiences, bugs you have found and 
all kind of suggestions! :)


Server requirements
===================
Minimum PHP version: PHP 5.4.x


Installation
=============
- Put all files into /report/moodleanalyst in your moodle directory
- open the moodle administration page to install the plugin
- you find the plugin in the site reports: 
    Administration -> Site administration -> Reports -> Moodle AnalyST
    (user needs to be at least 'course creator')


Idea
=====
Moodle makes (for good reasons) only very conservatively use of JavaScript. 
For the reasons see [1], [2] and [3].
In consequence many full page reloads are necessary to perform even simple tasks.

For this project we dropped the moodle principles of accessibility to get 
a high speed support/administration tool, realised under heavy JavaScript usage.

Libraries we used:
- AngularJS, [4]
- Google Charts, [5] used for the tables, and filters (dashboards)
- Slim, [6] "a micro framework for PHP" to realise a simple API to get
    the needed data as JSON


Links
======
[1] https://docs.moodle.org/dev/JavaScript_guidelines
[2] https://docs.moodle.org/dev/Unobtrusive_Javascript
[3] https://docs.moodle.org/dev/Progressive_enhancement
[4] https://angularjs.org/
[5] https://developers.google.com/chart/
[6] http://www.slimframework.com/