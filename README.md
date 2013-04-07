# Doctrine License Manager

A Symfony2 application that helps you switch the license for a Github project.

[![Build Status](https://travis-ci.org/beberlei/license-manager.png?branch=master)](https://travis-ci.org/beberlei/license-manager)

Run "composer create-project https://github.com/beberlei/license-manager.git"

Using ruby-fpm and ant you can generate a debian package of this application.

You have to update all the views in src/Doctrine/Bundle/LicenseManagerBundle/Resources/views
to match your own open source project. Currently texts are all for Doctrine Project and
there is no CMS to help you change the content.

The best way to do this is by overriding them via ``app/Resources/DoctrineBundleLicenseManagerBundle/views``

To import a repository run "app/console license:import [url to repository]"