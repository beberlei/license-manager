# Doctrine License Manager

A Symfony2 application that helps you switch the license for a Github project.

1. Checkout
2. Change app/config/parameters.yml-dist to app/config/parameters.yml
3. Enter parameters
4. Update app/config/security.yml in memory provider details
5. Run "composer install"

Using ruby-fpm and ant you can generate a debian package of this application.

You have to update all the views in src/Doctrine/Bundle/LicenseManagerBundle/Resources/views to match your own open source project. Currently texts are all for Doctrine Project and there is no CMS to help you change the content.

