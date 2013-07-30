<?php

use Behat\Behat\Context\ClosuredContextInterface;
use Behat\Behat\Context\TranslatedContextInterface;
use Behat\Behat\Context\BehatContext;
use Behat\Behat\Exception\PendingException;
use Behat\Behat\Event\SuiteEvent;
use Behat\Behat\Event\FeatureEvent;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Context\Step;
use Behat\MinkExtension\Context\MinkContext;

use Doctrine\DBAL\DriverManager;

require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

/**
 * Features context.
 */
class FeatureContext extends MinkContext
{
    private $secret;
    private $adminPassword;

    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        $this->secret = $parameters['secret'];
        $this->adminPassword = $parameters['admin_password'];
    }

    /**
     * @BeforeFeature
     */
    static public function beforeFeature(FeatureEvent $event)
    {
        $parameters = $event->getContextParameters();
        $conn = DriverManager::getConnection($parameters['db']);

        $tables = array('commit_audit', 'commit', 'author_audit', 'author', 'project', 'revisions');

        $conn->exec('SET foreign_key_checks = 0;');
        foreach ($tables as $table) {
            $conn->exec('TRUNCATE ' . $table);
        }
        $conn->exec('SET foreign_key_checks = 1;');
    }

    /**
     * @When /^I import project "([^"]*)"$/
     */
    public function iImportProject($repositoryUrl)
    {
        return $this->projectWantsToSwitchLicense($repositoryUrl);
    }

    /**
     * @Then /^project "([^"]*)" should exist$/
     */
    public function projectShouldExist($project)
    {
        return array(
            new Step\When('I go to "/licenses/projects"'),
            new Step\Then('I should see "' . $project . '"'),
        );
    }

    /**
     * @Given /^project "([^"]*)" should have "([^"]*)" confirmed code-changes$/
     */
    public function projectShouldHaveConfirmedCodeChanges($project, $approveRatio)
    {
        return array(
            new Step\Given('I am on "/licenses/projects"'),
            new Step\When('I follow "' . $project . '"'),
            new Step\Then('I should see "approve ratio: ' . $approveRatio . '"')
        );
    }

    /**
     * @Given /^project "([^"]*)" should have author "([^"]*)"$/
     */
    public function projectShouldHaveAuthor($project, $author)
    {
        return array(
            new Step\Given('I am on "/licenses/projects"'),
            new Step\When('I follow "' . $project . '"'),
            new Step\When('I follow "' . $author . '"'),
            new Step\Then('I should see "' . $author . '"'),
        );
    }

    /**
     * @When /^project "([^"]*)" wants to switch license$/
     */
    public function projectWantsToSwitchLicense($repositoryUrl)
    {
        $name = substr(str_replace("https://github.com/", "", $repositoryUrl), 0, -4);

        return array(
            new Step\Given('I am on "/licenses/projects/create"'),
            new Step\When('I fill in "create_project[name]" with "' . $name . '"'),
            new Step\When('I fill in "create_project[githubUrl]" with "' . $repositoryUrl . '"'),
            new Step\When('I fill in "create_project[pageMessage]" with "Page Message"'),
            new Step\When('I fill in "create_project[emailMessage]" with "Mail Message"'),
            new Step\When('I press "Create Project"'),
            new Step\Given('I am logged in as admin'),
            new Step\Given('I am on "/licenses/projects"'),
            new Step\When('I press "Approve Project"'),
        );
    }

    /**
     * @Then /^the following users approve licenses:$/
     */
    public function theFollowingUsersApproveLicenses(TableNode $table)
    {
        $hash = $table->getHash();
        $steps = array();

        foreach ($hash as $row) {
            $hash = hash_hmac('sha512', $row['ID'] . $row['Email'], $this->secret);

            $steps[] = new Step\When('I am on "/licenses/authors/' . $row['ID'] . '/approve?hash=' . $hash . '"');
            $steps[] = new Step\When('I select "I approve" from "licenses_approve[approved]"');
            $steps[] = new Step\When('I press "Save Answer"');
        }

        return $steps;
    }

    /**
     * @Then /^the project "([^"]*)" should have an author approve ratio bigger than "(\d+)"%$/
     */
    public function theProjectShouldHaveAnAuthorApproveRatioBiggerThan($project, $ratio)
    {
        return array(
            new Step\Given('I am on "/licenses/projects"'),
            new Step\When('I follow "' . $project . '"'),
            new Step\Then('I should see the value in ".app-approve" is bigger than "' . $ratio . '"')
        );
    }

    /**
     * @Then /^I should see the value in "([^"]*)" is bigger than "([^"]*)"$/
     */
    public function theValueShouldBeBiggerThan($selector, $value)
    {
        $page = $this->getSession()->getPage();
        $found = (int)$page->find('css', $selector)->getText();

        if ($found < $value) {
            throw new \RuntimeException("The value '" . $found . "' is not bigger than '" . $value . "'");
        }
    }

    /**
     * @Then /^the project "([^"]*)" should have a code approval ratio bigger than "(\d+)"%$/
     */
    public function theProjectShouldHaveACodeApprovalRatioBiggerThan($project, $ratio)
    {
        return array(
            new Step\Given('I am on "/licenses/projects"'),
            new Step\Then('I should see the value in "*[data-name=\'' . $project . '\'].app-approve" is bigger than "' . $ratio . '"')
        );
    }

    /**
     * @When /^I am logged in as admin$/
     */
    public function iAmLoggedInAsAdmin()
    {
        return array(
            new Step\Given('I am on "/login"'),
            new Step\When('I fill in "_username" with "admin"'),
            new Step\When('I fill in "_password" with "' . $this->adminPassword . '"'),
            new Step\When('I press "login"'),
            new Step\Then('I should be on "/"'),
        );
    }

    /**
     * @Then /^I should not see a field with "([^"]*)"$/
     */
    public function iShouldNotSeeAFieldWith($text)
    {
        $page = $this->getSession()->getPage();
        $found = $page->find('css', 'input[value="' . $text . '"]');

        if ($found !== null) {
            throw new \RuntimeException("Element was found!");
        }
    }

    /**
     * @Then /^I should see a field with "([^"]*)"$/
     */
    public function iShouldSeeAFieldWith($text)
    {
        $page = $this->getSession()->getPage();
        $found = $page->find('css', 'input[value="' . $text . '"]');

        if ($found === null) {
            throw new \RuntimeException("Element was not found!");
        }
    }

    /**
     * @When /^I am not an admin$/
     */
    public function iAmNotAnAdmin()
    {
        return array(new Step\When('I am on "/logout"'));
    }

    /**
     * @Given /^I should not see a button with "([^"]*)"$/
     */
    public function iShouldNotSeeAButtonWith($text)
    {
        $page = $this->getSession()->getPage();
        $found = $page->find('css', 'input[value="' . $text . '"]');

        if ($found !== null) {
            throw new \RuntimeException("Element was not found!");
        }
    }

    /**
     * @Given /^I should see a button with "([^"]*)"$/
     */
    public function iShouldSeeAButtonWith($text)
    {
        $page = $this->getSession()->getPage();
        $found = $page->find('css', 'input[value="' . $text . '"]');

        if ($found === null) {
            throw new \RuntimeException("Element was not found!");
        }
    }
}
