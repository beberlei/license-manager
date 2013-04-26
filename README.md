# Doctrine License Manager

A Symfony2 application that helps you switch the license for a Github project.

[![Build Status](https://travis-ci.org/beberlei/license-manager.png?branch=master)](https://travis-ci.org/beberlei/license-manager)

Description
-----------

This application allows importing multiple github repositories, extracting each author
and their respective commits. The project overview page tracks the approval ratio per
imported project.

Administrators can add missing emails for authors as well as mark specific commits as
trivial (ie. not requiring specific approval). Administrators can then trigger a mass
email to all authors with no yet approved commits which contain a link with a per
author hash.

Using this link authors can review each commit and decide to approve/deny the proposed
license change.

Prerequisites
-------------

* PHP 5.3.3 or better
* A database supported by doctrine orm, i.e. mysql, postgresql or sqlite
* A [mailgun](https://mailgun.net) account (free for up to 200 emails per
  day - alternatively send a pull request to support the symfony mailer)

Installation
------------

Run "composer create-project doctrine/license-manager --stability=dev" and
adjust the suggested parameter values as needed when prompted.

Using ruby-fpm and ant you can generate a debian package of this application.

Usage
-----

To import a repository run

    app/console license:import [url to repository]

The URL must be the https git repository URL, for example
https://github.com/jackalope/orm.git

Then log in with `admin` and the value you set in the *password* parameters.yml
field. Now visit the "Authors" tab to check if the emails make sense and
optionally mark commits as trivial.

Finally, go to the projects page, hit the "Send Emails" button and wait
for your contributors to confirm the license change.
