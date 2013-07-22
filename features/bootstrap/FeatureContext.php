<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

/**
 * Features context.
 */
class FeatureContext extends BehatContext
{
    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
    }

    /**
     * @When /^I import project "([^"]*)"$/
     */
    public function iImportProject($repositoryUrl)
    {
        exec("php app/console license:import " . escapeshellarg($repositoryUrl));
    }

    /**
     * @Then /^project "([^"]*)" should exist$/
     */
    public function projectShouldExist($arg1)
    {
        throw new PendingException();
    }

    /**
     * @Given /^project "([^"]*)" should have "([^"]*)" confirmed code-changes$/
     */
    public function projectShouldHaveConfirmedCodeChanges($arg1, $arg2)
    {
        throw new PendingException();
    }
}
