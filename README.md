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

Installation
------------

Run "composer create-project doctrine/license-manager --stability=dev" and
adjust the suggested parameter values as needed when prompted.

Using ruby-fpm and ant you can generate a debian package of this application.

Usage
-----

To import a repository run "app/console license:import [url to repository]"

You can simply re-run the license:import command to update an existing
repository, in case the relicensing process took too long and you got new
contributors. The approval is per author, so only new authors that did not
approve yet will be listed as not approved.