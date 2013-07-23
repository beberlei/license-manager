Feature: Switch License
    As a project owner
    To switch the license of my project
    I want to create and import the project

    Scenario: Create Project
        When I import project "https://github.com/beberlei/assert.git"
        Then project "beberlei/assert" should exist
        And project "beberlei/assert" should have "0" confirmed code-changes
        And project "beberlei/assert" should have author "Benjamin Eberlei"
        And project "beberlei/assert" should have author "Bastian Feder"
