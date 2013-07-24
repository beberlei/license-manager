Feature: Switch License Project
    As a project owner
    To switch the license of my project
    I want to create and import the project

    Scenario: Create Project
        When I import project "https://github.com/beberlei/assert.git"
        Then project "beberlei/assert" should exist
        And project "beberlei/assert" should have "0" confirmed code-changes
        And project "beberlei/assert" should have author "Benjamin Eberlei"
        And project "beberlei/assert" should have author "Bastian Feder"

    Scenario: E-Mail not visible for non-admin
        When I am on "/licenses/authors"
        Then I should not see a field with "kontakt@beberlei.de"

    Scenario: E-Mail visible for admin
        When I am logged in as admin
        And I am on "/licenses/authors"
        Then I should see a field with "kontakt@beberlei.de"
